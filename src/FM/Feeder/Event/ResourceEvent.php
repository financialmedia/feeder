<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

class ResourceEvent extends Event
{
    protected $resource;
    protected $resources;

    public function __construct(Resource $resource, ResourceCollection $resources)
    {
        $this->resource = $resource;
        $this->resources = $resources;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getResources()
    {
        return $this->resources;
    }
}
