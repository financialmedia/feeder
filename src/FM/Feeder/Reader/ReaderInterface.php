<?php

namespace FM\Feeder\Reader;

use Symfony\Component\HttpFoundation\ParameterBag;

interface ReaderInterface extends \Iterator
{
    /**
     * Reads the next item in the feed
     *
     * @return ParameterBag
     */
    public function read();
}
