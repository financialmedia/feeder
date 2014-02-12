<?php

namespace FM\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FileTransport extends AbstractTransport
{
    /**
     * @inheritdoc
     */
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

    /**
     * Factory method
     *
     * @param string $file
     *
     * @return FileTransport
     */
    public static function create($file)
    {
        return new self(new Connection(['file' => $file]));
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string) $this->connection['file'];
    }

    /**
     * @inheritdoc
     */
    public function getLastModifiedDate()
    {
        return new \DateTime('@' . filemtime($this->connection['file']));
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return filesize($this->connection['file']);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return basename($this->connection['file']);
    }

    /**
     * @inheritdoc
     */
    protected function doDownload($destination)
    {
        // the destination may be the same as the source
        if (realpath($this->connection['file']) === realpath($destination)) {
            return;
        }

        copy($this->connection['file'], $destination);
    }
}
