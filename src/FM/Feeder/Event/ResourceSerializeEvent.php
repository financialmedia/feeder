<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use FM\Feeder\Resource\Resource;

class ResourceSerializeEvent extends Event
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $item;

    /**
     * @param Resource $resource
     * @param mixed    $item
     */
    public function __construct(Resource $resource, &$item)
    {
        $this->resource = $resource;
        $this->item     = &$item;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function &getItem()
    {
        return $this->item;
    }

    public function setItem($item)
    {
        $this->item = $item;
    }
}
