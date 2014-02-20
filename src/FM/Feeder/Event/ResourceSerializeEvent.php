<?php

namespace FM\Feeder\Event;

use FM\Feeder\Resource\Resource;
use Symfony\Component\EventDispatcher\Event;

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
     * @param \FM\Feeder\Resource\Resource $resource
     * @param mixed                        $item
     */
    public function __construct(Resource $resource, &$item)
    {
        $this->resource = $resource;
        $this->item     = &$item;
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function &getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}
