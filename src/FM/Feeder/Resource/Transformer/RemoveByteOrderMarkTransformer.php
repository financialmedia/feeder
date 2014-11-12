<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

/**
 * Strips the BOM from the beginning of the resource
 */
class RemoveByteOrderMarkTransformer implements ResourceTransformer
{
    /**
     * @var array
     */
    protected $boms;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->boms = [
            '\x00\x00\xFE\xFF', // UTF-32 (BE)
            '\xFF\xFE\x00\x00', // UTF-32 (LE)
            '\xFE\xFF',         // UTF-16 (BE)
            '\xFF\xFE',         // UTF-16 (LE)
            '\xEF\xBB\xBF',     // UTF-8
        ];
    }

    /**
     * @inheritdoc
     */
    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        // the file could be big, so just read the
        $tmpFile = tempnam(sys_get_temp_dir(), $file);
        $old     = fopen($file, 'r');
        $new     = fopen($tmpFile, 'w');

        // write the beginning with the BOM stripped
        fwrite($new, preg_replace($this->getBomRegex(), '', fread($old, 16)));

        // now copy the rest of the file
        while (!feof($old)) {
            fwrite($new, fread($old, 8192));
        }

        fclose($old);
        fclose($new);

        // atomic write
        $this->rename($tmpFile, $file);

        $transport = FileTransport::create($file);

        if ($resource->getTransport()) {
            $transport->setDestinationDir($resource->getTransport()->getDestinationDir());
        }

        return new FileResource($transport);
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(Resource $resource)
    {
        $file = $resource->getFile()->getPathname();
        $handle = fopen($file, 'r');

        $result = (bool) preg_match($this->getBomRegex(), fread($handle, 16));

        fclose($handle);

        return $result;
    }

    /**
     * @param string $old
     * @param string $new
     *
     * @throws \RuntimeException
     */
    protected function rename($old, $new)
    {
        if (!rename($old, $new)) {
            throw new \RuntimeException(sprintf('Could not rename %s to %s', $old, $new));
        }
    }

    /**
     * @return string
     */
    protected function getBomRegex()
    {
        return sprintf('/^(%s)/', implode('|', $this->boms));
    }
}
