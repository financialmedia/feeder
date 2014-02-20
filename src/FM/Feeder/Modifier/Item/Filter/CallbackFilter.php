<?php

namespace FM\Feeder\Modifier\Item\Filter;

use FM\Feeder\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\ParameterBag;

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
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callback');
        }

        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function filter(ParameterBag $item)
    {
        return call_user_func($this->callback, $item);
    }
}
