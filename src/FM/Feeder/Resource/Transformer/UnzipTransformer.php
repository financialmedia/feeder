<?php

namespace FM\Feeder\Resource\Transformer;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;

class UnzipTransformer implements ResourceTransformer
{
    protected $target;
    protected $files;

    /**
     * @param string $files  The filename(s) in the zip file to return
     * @param string $target Target directory
     */
    public function __construct($files, $target = null)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        if (!is_array($files)) {
            throw new \InvalidArgumentException(sprintf('Expecting a file path or array of file paths as first argument, got "%s"', json_encode($files)));
        }

        $this->files = $files;
        $this->target = $target;
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        if ($this->needsUnzipping($resource)) {
            $this->unzip($resource);
        }

        $resources = [];
        foreach ($this->files as $file) {
            $transport = FileTransport::create($this->getTargetFile($resource, $file));
            $transport->setDestinationDir($this->getTargetDir($resource));
            $resources[] = new FileResource($transport);
        }

        $collection->unshiftAll($resources);

        return $collection->shift();
    }

    public function needsTransforming(Resource $resource)
    {
        $resourceFile = (string) $resource->getTransport();

        // don't transform unzipped files
        foreach ($this->files as $file) {
            if ($resourceFile === $this->getTargetFile($resource, $file)) {
                return false;
            }
        }

        // check if file type is actually zip
        return $this->isExtractable($resource);
    }

    protected function needsUnzipping(Resource $resource)
    {
        foreach ($this->files as $file) {
            $targetFile = $this->getTargetFile($resource, $file);
            if (!file_exists($targetFile) || ($resource->getFile()->getMTime() > filemtime($targetFile))) {
                return true;
            }
        }

        return false;
    }

    protected function isExtractable(Resource $resource)
    {
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($resource->getFile()->getPathname()) === 'application/zip';
    }

    protected function unzip(Resource $resource)
    {
        $zip = new \ZipArchive();
        $zip->open($resource->getFile()->getPathname());
        $zip->extractTo($this->getTargetDir($resource));
    }

    protected function getTargetDir(Resource $resource)
    {
        return $this->target ?: $resource->getFile()->getPath();
    }

    protected function getTargetFile(Resource $resource, $filename)
    {
        return sprintf('%s/%s', $this->getTargetDir($resource), $filename);
    }
}
