<?php

namespace FM\Feeder\Resource;

use FM\Feeder\Resource\Transformer\ResourceTransformer;

class ResourceCollection extends \ArrayIterator
{
    protected $transformers = [];

    public function addTransformer(ResourceTransformer $transformer)
    {
        $this->transformers[] = $transformer;
    }

    public function append($value)
    {
        if (!$value instanceof Resource) {
            throw new \InvalidArgumentException('You can only append a Resource instance');
        }

        return parent::append($value);
    }

    public function getNextResource()
    {
        if (!$this->valid()) {
            return;
        }

        $resource = $this->current();
        $this->transform($resource);
        $this->next();

        return $resource;
    }

    protected function transform(Resource $resource)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->needsTransforming($resource)) {
                $transformer->transform($resource, $this);
            }
        }
    }
}
