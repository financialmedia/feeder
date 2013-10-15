<?php

namespace FM\Feeder\Resource;

interface Resource
{
    /**
     * @return \FM\Feeder\Transport\Transport
     */
    public function getTransport();

    /**
     * @return \SplFileObject
     */
    public function getFile();

    /**
     * @param $file \SplFileObject
     */
    public function setFile(\SplFileObject $file);
}
