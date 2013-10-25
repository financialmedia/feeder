<?php

namespace FM\Feeder\Transport;

class FileTransport extends AbstractTransport
{
    public static function create($file)
    {
        return new self(new Connection(['file' => $file]));
    }

    public function __toString()
    {
        return $this->connection['file'];
    }

    public function getLastModifiedDate()
    {
        return new \DateTime('@' . filemtime($this->connection['file']));
    }

    public function getSize()
    {
        return filesize($this->connection['file']);
    }

    public function getFilename()
    {
        return basename($this->connection['file']);
    }

    protected function doDownload($destination)
    {
        if (!isset($this->connection['file']) || !is_readable($this->connection['file'])) {
            throw new \InvalidArgumentException('No readable file given');
        }

        // the destination may be the same as the source
        if (realpath($this->connection['file']) === realpath($destination)) {
            return;
        }

        copy($this->connection['file'], $destination);
    }
}
