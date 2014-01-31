<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Resource\Transformer\ResourceTransformer;

class ResourceCollection extends \SplQueue
{
    /**
     * @var ResourceTransformer[]
     */
    protected $transformers = [];

    /**
     * @var array
     */
    protected $transformed = [];

    /**
     * @param \FM\Feeder\Resource\Resource[] $resources
     */
    public function __construct(array $resources = array())
    {
        $this->enqueueAll($resources);
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function current()
    {
        $resource = parent::current();

        return $resource ? $this->transform($resource) : $resource;
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function shift()
    {
        return $this->transform(parent::shift());
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function pop()
    {
        return $this->transform(parent::pop());
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function dequeue()
    {
        return $this->transform(parent::dequeue());
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function bottom()
    {
        return $this->transform(parent::bottom());
    }

    /**
     * @return \FM\Feeder\Resource\Resource
     */
    public function top()
    {
        return $this->transform(parent::top());
    }

    /**
     * @param integer $index
     *
     * @return \FM\Feeder\Resource\Resource
     */
    public function offsetGet($index)
    {
        return $this->transform(parent::offsetGet($index));
    }

    /**
     * @param \FM\Feeder\Resource\Resource[] $resources
     */
    public function enqueueAll(array $resources)
    {
        foreach ($resources as $resource) {
            $this->enqueue($resource);
        }

        $this->rewind();
    }

    /**
     * @param \FM\Feeder\Resource\Resource[] $resources
     */
    public function unshiftAll(array $resources)
    {
        foreach (array_reverse($resources) as $resource) {
            $this->unshift($resource);
        }

        $this->rewind();
    }

    /**
     * @param ResourceTransformer $transformer
     */
    public function addTransformer(ResourceTransformer $transformer)
    {
        $this->transformers[] = $transformer;
    }

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return \FM\Feeder\Resource\Resource
     */
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
