<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

class ConvertEncodingTransformer implements ResourceTransformer
{
    /**
     * @var string
     */
    protected $fromEncoding;

    /**
     * @var string
     */
    protected $toEncoding;

    /**
     * @param string $fromEncoding The encoding in which the resource is initially
     * @param string $toEncoding   The encoding to convert to. Uses internal encoding when left empty
     */
    public function __construct($fromEncoding, $toEncoding = null)
    {
        $this->fromEncoding = $fromEncoding;
        $this->toEncoding   = $toEncoding ?: mb_internal_encoding();
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        // first, rename the original file
        $oldFile = $this->rename($file);

        $old = fopen($oldFile, 'r');
        $new = fopen($file, 'w');
        while (!feof($old)) {
            fwrite($new, mb_convert_encoding(fgets($old), $this->toEncoding, $this->fromEncoding));
        }

        fclose($old);
        fclose($new);

        unlink($oldFile);

        $transport = FileTransport::create($file);

        if ($resource->getTransport()) {
            $transport->setDestinationDir($resource->getTransport()->getDestinationDir());
        }

        return new FileResource($transport);
    }

    public function needsTransforming(Resource $resource)
    {
        $file = $resource->getFile();
        while (!$file->eof()) {
            $line = $file->fgets();
            if (!mb_check_encoding($line, $this->toEncoding)) {
                return true;
            }
        }

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
