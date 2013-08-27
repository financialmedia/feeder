<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Transport\Transport;
use FM\Feeder\Exception\TransportException;

class FileResource implements Resource
{
    protected $transport;
    protected $file;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        if ($this->file === null) {
            try {
                $this->file = $this->transport->getFile();
            } catch (\RuntimeException $e) {
                throw new TransportException(sprintf('The path "%s" is invalid', $this->transport->getDestination()), null, $e);
            }
        }

        return $this->file;
    }
}
