<?php

namespace FM\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\FeedEvents;
use FM\Feeder\Event\ResourceEvent;
use FM\Feeder\Event\ResourceSerializeEvent;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

abstract class AbstractReader implements ReaderInterface
{
    /**
     * @var \FM\Feeder\Resource\ResourceCollection
     */
    protected $resources;

    /**
     * @var \FM\Feeder\Resource\Resource
     */
    protected $resource;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var boolean
     */
    protected $initialized;

    /**
     * Constructor
     *
     * @param mixed $resources Optional resource collection. Can be a Resource, an array of
     *                                             Resource's, or a ResourceCollection. When empty, a new collection
     *                                             will be created.
     * @param  EventDispatcherInterface  $dispatcher Optional event dispatcher.
     * @throws \InvalidArgumentException
     */
    public function __construct($resources = null, EventDispatcherInterface $dispatcher = null)
    {
        if ($resources instanceof Resource) {
            $resources = [$resources];
        }

        if (is_array($resources)) {
            $resources = new ResourceCollection($resources);
        }

        if ($resources === null) {
            $resources = new ResourceCollection();
        }

        if (!$resources instanceof ResourceCollection) {
            throw new \InvalidArgumentException(
                'Second argument must be a Resource object, an array of Resource objects, or null'
            );
        }

        $this->resources = $resources;
        $this->eventDispatcher = $dispatcher ?: new EventDispatcher();
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

        // keep a local copy of the resource; the next() call could change the cached one
        $resource = $this->resource;

        $item = $this->current();
        $this->next();

        // serialize the item
        $event = new ResourceSerializeEvent($resource, $item);
        $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_PRE_SERIALIZE, $event);
        $item = $this->serialize($item);
        $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_POST_SERIALIZE, $event);

        return $item;
    }

    /**
     * @param ResourceCollection $resources
     */
    public function setResources(ResourceCollection $resources)
    {
        $this->resources = $resources;

        // must reinitialize, because we basically start over at this point
        $this->initialized = false;
    }

    /**
     * @return ResourceCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function getCurrentResource()
    {
        return $this->resource;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    protected function createNextReader()
    {
        if ($this->resource) {
            // end existing resource first
            $this->eventDispatcher->dispatch(
                FeedEvents::RESOURCE_END,
                new ResourceEvent($this->resource, $this->resources)
            );
        }

        if ($this->resources->isEmpty()) {
            return;
        }

        // get the next resource
        $this->resource = $this->resources->shift();

        // dispatch start event
        $this->eventDispatcher->dispatch(
            FeedEvents::RESOURCE_START,
            new ResourceEvent($this->resource, $this->resources)
        );

        // create a reader for this new resource
        $this->createReader($this->resource);
    }

    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        // mark initialized first, to prevent recursive calls
        $this->initialized = true;

        $this->resources->rewind();
        $this->createNextReader();
    }

    /**
     * Serializes a read item into a ParameterBag
     *
     * @param  string       $data
     * @return ParameterBag
     */
    abstract protected function serialize($data);

    /**
     * Creates a reader for a resource
     *
     * @param  \FM\Feeder\Resource\Resource $resource
     * @return void
     */
    abstract protected function createReader(Resource $resource);

    /**
     * @see \Iterator::key()
     */
    abstract protected function doKey();

    /**
     * @see \Iterator::current()
     */
    abstract protected function doCurrent();

    /**
     * @see \Iterator::next()
     */
    abstract protected function doNext();

    /**
     * @see \Iterator::valid()
     */
    abstract protected function doValid();

    /**
     * @see \Iterator::rewind()
     */
    abstract protected function doRewind();
}
