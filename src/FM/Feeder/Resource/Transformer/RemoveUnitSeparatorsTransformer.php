<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

class RemoveUnitSeparatorsTransformer implements ResourceTransformer
{
    protected $length;

    public function __construct($length = 8192)
    {
        $this->length = intval($length);
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        // first, rename the original file
        $oldFile = $this->rename($file);

        // remove unit separators
        $char = chr(31);
        $old = fopen($oldFile, 'r');
        $new = fopen($file, 'w');

        while (!feof($old)) {
          fwrite($new, str_replace($char, '', fread($old, $this->length)));
        }

        fclose($old);
        fclose($new);

        unlink($oldFile);

        $transport = FileTransport::create($file);
        $transport->setDestinationDir($resource->getTransport()->getDestinationDir());

        return $transport;
    }

    public function needsTransforming(Resource $resource)
    {
        return false;
    }

    protected function rename($file)
    {
        $tmpFile = $file . '.tmp';

        if (rename($file, $tmpFile)) {
            return $tmpFile;
        }

        throw new \RuntimeException(sprintf('Could not rename %s to %s', $file, $tmpFile));
    }
}
