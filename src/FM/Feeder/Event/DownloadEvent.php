<?php

namespace FM\Feeder\Event;

use FM\Feeder\Transport\Transport;
use Symfony\Component\EventDispatcher\Event;

class DownloadEvent extends Event
{
    /**
     * @var Transport
     */
    protected $transport;

    /**
     * @param Transport $transport
     */
    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->transport->__toString();
    }

    /**
     * @return integer|null
     */
    public function getSize()
    {
        return $this->transport->getSize();
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->transport->getDestination();
    }
}
