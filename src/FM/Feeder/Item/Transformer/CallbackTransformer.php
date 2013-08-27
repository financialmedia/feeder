<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class CallbackTransformer implements DataTransformer
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

    public function transform($value, $key, ParameterBag $item)
    {
        return call_user_func($this->callback, $value);
    }
}
