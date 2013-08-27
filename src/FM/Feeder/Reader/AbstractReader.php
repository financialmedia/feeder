<?php

namespace FM\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Serializer;
use FM\Feeder\FeedEvents;
use FM\Feeder\Event\ResourceEvent;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

abstract class AbstractReader implements ReaderInterface
{
    /**
     * @var ResourceCollection
     */
    protected $resources;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var Closure
     */
    protected $nextNode;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var boolean
     */
    protected $initialized;

    public function __construct($nextNode, ResourceCollection $resources = null)
    {
        if ($resources === null) {
            $resources = new ResourceCollection();
        }

        $this->resources = $resources;
        $this->nextNode  = $this->getNextNodeCallback($nextNode);
        $this->eventDispatcher = new EventDispatcher();
    }

    public function current()
    {
        $this->initialize();

        return $this->doCurrent();
    }

    public function key()
    {
        $this->initialize();

        return $this->doKey();
    }

    public function next()
    {
        $this->initialize();

        $this->doNext();

        // if the current reader is not valid, create a reader for the next resource
        if (!$this->valid()) {
            $this->createNextReader();
        }
    }

    public function rewind()
    {
        $this->initialize();

        $this->doRewind();
    }

    public function valid()
    {
        $this->initialize();

        return $this->doValid();
    }

    /**
     * Wrapper that implements various calls, so you can use the iterator in a
     * simple while loop.
     *
     * @return ParameterBag
     */
    public function read()
    {
        if (!$this->valid()) {
            return null;
        }

        $res = $this->current();

        $this->next();

        return $this->serialize($res);
    }

    public function setResources(ResourceCollection $resources)
    {
        $this->resources = $resources;

        // must reinitialize, because we basically start over at this point
        $this->initialized = false;
    }

    public function addResource(Resource $resource)
    {
        $this->resources->append($resource);
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    protected function createNextReader()
    {
        if ($this->resource) {
            $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_END, new ResourceEvent($this->resource, $this->resources));
        }

        if ($this->resource = $this->resources->getNextResource()) {
            $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_START, new ResourceEvent($this->resource, $this->resources));
            $this->createReader($this->resource);
        }
    }

    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->createNextreader();

        $this->initialized = true;
    }

    abstract protected function doKey();
    abstract protected function doCurrent();
    abstract protected function doNext();
    abstract protected function doValid();
    abstract protected function doRewind();
    abstract protected function createReader(Resource $resource);
}
