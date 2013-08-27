<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Reader\ReaderInterface;
use FM\Feeder\Writer\WriterInterface;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Resource\FileResource;
use FM\Feeder\Transport\FileTransport;

class MultiPartTransformer implements ResourceTransformer
{
    protected $reader;
    protected $writer;
    protected $size;
    protected $maxParts;

    public function __construct(ReaderInterface $reader, WriterInterface $writer, $size = 1000, $maxParts = null)
    {
        $this->reader   = $reader;
        $this->writer   = $writer;
        $this->size     = $size;
        $this->maxParts = $maxParts;
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile();

        // find files that match the part-regex
        $files = $this->getPartFiles($file);

        if (empty($files)) {
            // break up again
            $this->breakup($file);
            $files = $this->getPartFiles($file);
        }

        // respect maxParts
        if ($this->maxParts > 0) {
            $files = array_splice($files, 0, $this->maxParts);
        }

        // append each part as resource
        foreach ($files as $file) {
            $transport = FileTransport::create($file);
            $transport->setDestination($file);

            $collection->append(new FileResource($transport));
        }
    }

    public function needsTransforming(Resource $resource)
    {
        return true;
    }

    protected function getPartFiles(\SplFileInfo $originalFile)
    {
        $files = [];

        $regex = sprintf('/^%s\.part(\d+)$/', preg_quote($originalFile->getBasename(), '/'));
        $finder = new \DirectoryIterator($originalFile->getPath());

        foreach ($finder as $file) {
            if ($file->isFile() && preg_match($regex, $file->getBaseName(), $matches)) {
                $files[(int) $matches[1]] = $file->getPathname();
            }
        }

        ksort($files);

        return $files;
    }

    protected function breakup(\SplFileInfo $originalFile)
    {
        $partCount = 0;
        $baseFile = $originalFile->getPathname();

        while ($this->reader->valid()) {
            if ($this->reader->key() % $this->size === 0) {
                if ($this->reader->key() > 0) {
                    $this->endPart();
                }

                $file = sprintf('%s.part%s', $baseFile, ++$partCount);
                $this->startPart($file);
            }

            $this->writer->write($this->reader->current());
            $this->writer->flush();

            $this->reader->next();
        }

        $this->endPart();
    }

    protected function startPart($file)
    {
        $this->writer = clone($this->writer);
        $this->writer->setFile(new \SplFileObject($file, 'w'));
        $this->writer->start();
    }

    protected function endPart()
    {
        $this->writer->end();
    }
}
