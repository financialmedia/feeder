<?php

namespace FM\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface Transport
{
    /**
     * @return string
     */
    public function getDestination();

    /**
     * @return string
     */
    public function getDestinationDir();

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate();

    /**
     * @return \SplFileObject
     */
    public function getFile();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * Purges a previously transported file, removing the destination and
     * whatever cache the transport uses internally
     *
     * @return void
     */
    public function purge();
}
