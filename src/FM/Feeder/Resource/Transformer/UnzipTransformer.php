<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Exception\TransportException;
use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class UnzipTransformer implements ResourceTransformer
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var string[]
     */
    protected $files;

    /**
     * @param string $files  The filename(s) in the zip file to return
     * @param string $target Target directory, defaults to the directory in which the zip is located
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($files, $target = null)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        if (!is_array($files)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting a file path or array of file paths as first argument, got "%s"',
                    json_encode($files)
                )
            );
        }

        $this->files = $files;
        $this->target = $target;
    }

    /**
     * @inheritdoc
     */
    public function transform(Resource $resource, ResourceCollection $collection)
    {
        if ($this->needsUnzipping($resource)) {
            $this->unzip($resource);
        }

        $resources = [];
        foreach ($this->files as $file) {
            $targetFile = $this->getTargetFile($resource, $file);
            if (!file_exists($targetFile)) {
                throw new TransportException(sprintf('File "%s" was not found in the archive', $targetFile));
            }

            $transport = FileTransport::create($targetFile);
            $transport->setDestinationDir($this->getTargetDir($resource));
            $resources[] = new FileResource($transport);
        }

        $collection->unshiftAll($resources);

        return $collection->shift();
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return boolean
     */
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

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return boolean
     */
    protected function isExtractable(Resource $resource)
    {
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($resource->getFile()->getPathname()) === 'application/zip';
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     */
    protected function unzip(Resource $resource)
    {
        $zip = new \ZipArchive();
        $zip->open($resource->getFile()->getPathname());
        $zip->extractTo($this->getTargetDir($resource));
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return string
     */
    protected function getTargetDir(Resource $resource)
    {
        return $this->target ?: $resource->getFile()->getPath();
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     * @param string                       $filename
     *
     * @return string
     */
    protected function getTargetFile(Resource $resource, $filename)
    {
        return sprintf('%s/%s', $this->getTargetDir($resource), $filename);
    }
}
