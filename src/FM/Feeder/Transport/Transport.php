<?php

namespace FM\Feeder\Transport;

interface Transport
{
    /**
     * @return string
     */
    public function getDestination();

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate();

    /**
     * @return \SplFileObject
     */
    public function getFile();
}
