<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

/**
 * Strips all control characters from the resource, except space characters.
 */
class RemoveControlCharactersTransformer implements ResourceTransformer
{
    /**
     * @var integer
     */
    protected $length;

    /**
     * @param integer $length The number of bytes to read/write while processing the resource
     */
    public function __construct($length = 8192)
    {
        $this->length = intval($length);
    }

    /**
     * @inheritdoc
     */
    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        $tmpFile = tempnam(sys_get_temp_dir(), $file);

        // remove control characters
        $old = fopen($file, 'r');
        $new = fopen($tmpFile, 'w');

        while (!feof($old)) {
            fwrite($new, preg_replace('/[^\P{Cc}\t\r\n]/u', '', fread($old, $this->length)));
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
        return true;
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
}
