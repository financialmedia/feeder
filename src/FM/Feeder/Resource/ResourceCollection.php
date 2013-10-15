<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Resource\Transformer\ResourceTransformer;

class ResourceCollection extends \SplQueue
{
    protected $transformers = [];
    protected $transformed = [];

    public function __construct(array $resources = array())
    {
        $this->enqueueAll($resources);
    }

    public function current()
    {
        $resource = parent::current();

        return $resource ? $this->transform($resource) : $resource;
    }

    public function shift()
    {
        return $this->transform(parent::shift());
    }

    public function pop()
    {
        return $this->transform(parent::pop());
    }

    public function bottom()
    {
        return $this->transform(parent::bottom());
    }

    public function top()
    {
        return $this->transform(parent::top());
    }

    public function offsetGet($index)
    {
        return $this->transform(parent::offsetGet($index));
    }

    public function enqueueAll(array $resources)
    {
        foreach ($resources as $resource) {
            $this->enqueue($resource);
        }

        $this->rewind();
    }

    public function unshiftAll(array $resources)
    {
        foreach (array_reverse($resources) as $resource) {
            $this->unshift($resource);
        }

        $this->rewind();
    }

    public function addTransformer(ResourceTransformer $transformer)
    {
        $this->transformers[] = $transformer;
    }

    protected function transform(Resource $resource)
    {
        $hash = spl_object_hash($resource);

        // see if it needs transforming
        if (!in_array($hash, $this->transformed)) {
            foreach ($this->transformers as $transformer) {
                if ($transformer->needsTransforming($resource)) {
                    $resource = $transformer->transform($resource, $this);
                }
            }

            $this->transformed[] = $hash;
        }

        return $resource;
    }
}
