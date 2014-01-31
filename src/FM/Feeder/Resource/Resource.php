<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Exception\TransportException;
use FM\Feeder\Transport\Transport;

interface Resource
{
    /**
     * @return Transport
     */
    public function getTransport();

    /**
     * @return \SplFileObject
     *
     * @throws TransportException
     */
    public function getFile();

    /**
     * @param $file \SplFileObject
     */
    public function setFile(\SplFileObject $file);
}
