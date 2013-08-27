<?php

namespace FM\Feeder\Reader;

use FM\Feeder\Resource\Resource;

interface ReaderInterface extends \Iterator
{
    public function read();
    public function addResource(Resource $resource);
}
