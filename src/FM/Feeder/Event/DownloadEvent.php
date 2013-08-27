<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use FM\Feeder\Transport\Transport;

class DownloadEvent extends Event
{
    protected $transport;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function getSource()
    {
        return $this->transport->__toString();
    }

    public function getSize()
    {
        return $this->transport->getSize();
    }

    public function getDestination()
    {
        return $this->transport->getDestination();
    }
}
