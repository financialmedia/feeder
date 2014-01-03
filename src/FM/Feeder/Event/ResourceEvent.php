<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

class ResourceEvent extends Event
{
    /**
     * @var \FM\Feeder\Resource\Resource
     */
    protected $resource;

    /**
     * @var \FM\Feeder\Resource\ResourceCollection
     */
    protected $resources;

    /**
     * @param \FM\Feeder\Resource\Resource           $resource
     * @param \FM\Feeder\Resource\ResourceCollection $resources
     */
    public function __construct(Resource $resource, ResourceCollection $resources)
    {
        $this->resource = $resource;
        $this->resources = $resources;
    }

    /**
     * @return\FM\Feeder\Resource\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return ResourceCollection
     */
    public function getResources()
    {
        return $this->resources;
    }
}
