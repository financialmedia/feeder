<?php

namespace FM\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FileTransport extends AbstractTransport
{
    public function __construct(Connection $conn, $destination = null, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($conn, $destination, $dispatcher);

        if (!isset($this->connection['file'])) {
            throw new \InvalidArgumentException('The "file" key is required in the Connection object');
        }

        if (!is_readable($this->connection['file'])) {
            throw new \InvalidArgumentException(sprintf('Not readable: %s', $this->connection['file']));
        }
    }

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
        // the destination may be the same as the source
        if (realpath($this->connection['file']) === realpath($destination)) {
            return;
        }

        copy($this->connection['file'], $destination);
    }
}
