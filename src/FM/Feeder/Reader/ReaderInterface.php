<?php

namespace FM\Feeder\Reader;

interface ReaderInterface extends \Iterator
{
    public function read();
}
