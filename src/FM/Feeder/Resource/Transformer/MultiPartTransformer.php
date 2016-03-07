<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Reader\ReaderInterface;
use FM\Feeder\Resource\FileResource;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Transport\FileTransport;
use FM\Feeder\Writer\WriterInterface;

class MultiPartTransformer implements ResourceTransformer
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var integer
     */
    protected $size;

    /**
     * @var integer|null
     */
    protected $maxParts;

    /**
     * @param ReaderInterface $reader
     * @param WriterInterface $writer
     * @param integer         $size
     * @param integer         $maxParts
     */
    public function __construct(ReaderInterface $reader, WriterInterface $writer, $size = 1000, $maxParts = null)
    {
        $this->reader   = $reader;
        $this->writer   = $writer;
        $this->size     = $size;
        $this->maxParts = $maxParts;
    }

    /**
     * @inheritdoc
     */
    public function transform(Resource $resource, ResourceCollection $collection)
    {
        // break up again
        $files = $this->breakup($resource);

        $resources = [];
        foreach ($files as $file) {
            $transport = FileTransport::create($file);
            $transport->setDestination($file);
            $resources[] = new FileResource($transport);

            if (($this->maxParts > 0) && (sizeof($resources) >= $this->maxParts)) {
                break;
            }
        }

        $collection->unshiftAll($resources);

        return $collection->shift();
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(Resource $resource)
    {
        return !$this->isPartFile($resource);
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return integer
     */
    protected function isPartFile(Resource $resource)
    {
        return preg_match('/\.part(\d+)$/', $resource->getFile()->getBasename());
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return array
     */
    protected function getPartFiles(Resource $resource)
    {
        $files = [];

        $originalFile = $resource->getFile();
        $regex = sprintf('/^%s\.part(\d+)$/', preg_quote($originalFile->getBasename(), '/'));
        $finder = new \DirectoryIterator($originalFile->getPath());

        /** @var $file \SplFileInfo */
        foreach ($finder as $file) {
            if ($file->isFile() && preg_match($regex, $file->getBaseName(), $matches)) {
                $files[(int) $matches[1]] = $file->getPathname();
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return array
     */
    protected function breakup(Resource $resource)
    {
        $originalFile = $resource->getFile();
        $baseFile = $originalFile->getPathname();

        $this->reader->setResources(new ResourceCollection([$resource]));

        $partCount = 0;
        $started = false;
        while ($this->reader->valid()) {
            if ($this->reader->key() % $this->size === 0) {
                if ($this->reader->key() > 0) {
                    $this->endPart();
                }

                $file = sprintf('%s.part%s', $baseFile, ++$partCount);
                $this->startPart($file);
                $started = true;
            }

            $this->writer->write($this->reader->current());
            $this->writer->flush();

            $this->reader->next();
        }

        if ($started) {
            $this->endPart();
        }

        return $this->getPartFiles($resource);
    }

    /**
     * @param string $file
     */
    protected function startPart($file)
    {
        $this->writer = clone($this->writer);
        $this->writer->setFile(new \SplFileObject($file, 'w'));
        $this->writer->start();
    }

    /**
     * @return void
     */
    protected function endPart()
    {
        $this->writer->end();
    }
}
