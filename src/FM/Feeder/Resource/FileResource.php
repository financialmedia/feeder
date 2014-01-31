<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Transport\Transport;
use FM\Feeder\Exception\TransportException;

class FileResource implements Resource
{
    /**
     * @var Transport
     */
    protected $transport;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @param Transport $transport
     */
    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritdoc
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @inheritdoc
     */
    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function getFile()
    {
        if ($this->file === null) {
            try {
                $this->file = $this->transport->getFile();
            } catch (\RuntimeException $e) {
                throw new TransportException(
                    sprintf('Could not open file "%s": %s', $this->transport->getDestination(), $e->getMessage()),
                    null,
                    $e
                );
            }
        }

        return $this->file;
    }
}
