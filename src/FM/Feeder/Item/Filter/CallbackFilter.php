<?php

namespace FM\Feeder\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class CallbackFilter implements FilterInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor
     *
     * @param callable $callback
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callback');
        }

        $this->callback = $callback;
    }

    public function filter(ParameterBag $item)
    {
        return call_user_func($this->callback, $item);
    }
}
