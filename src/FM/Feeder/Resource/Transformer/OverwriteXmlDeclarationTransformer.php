<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

/**
 * Overwrite xml declaration
 */
class OverwriteXmlDeclarationTransformer implements ResourceTransformer
{
    /**
     * @var string
     */
    protected $xmlDeclaration;

    /**
     * @var string regexp
     */
    protected $xmlDeclarationRegEx = '/^\<\?xml.*\?\>/i';

    /**
     * Constructor
     */
    public function __construct($xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>')
    {
        $this->xmlDeclaration = $xmlDeclaration;
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

        // write the beginning with the xml declaration replaced
        fwrite($new, preg_replace($this->xmlDeclarationRegEx, $this->xmlDeclaration, fread($old, 96)));

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

        return preg_match($this->xmlDeclarationRegEx, fread($handle, 96));
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
