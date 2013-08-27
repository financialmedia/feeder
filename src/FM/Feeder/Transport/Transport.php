<?php

namespace FM\Feeder\Transport;

interface Transport
{
    /**
     * @return string
     */
    public function getDestination();
}
