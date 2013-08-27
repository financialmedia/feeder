<?php

namespace FM\Feeder\Resource;

interface Resource
{
    /**
     * @return \SplFileObject
     */
    public function getFile();

    /**
     * @param $file \SplFileObject
     */
    public function setFile(\SplFileObject $file);
}
