<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Exception\UnexpectedTypeException;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

class CallbackTransformer implements ResourceTransformer
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callback');
        }

        $this->callback = $callback;
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        return call_user_func($this->callback, $resource, $collection);
    }

    public function needsTransforming(Resource $resource)
    {
        return true;
    }
}
