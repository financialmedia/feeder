<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;

class ResourceSerializeEvent extends Event
{
    protected $resource;

    public function __construct(&$resource)
    {
        $this->resource = &$resource;
    }

    public function &getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }
}
