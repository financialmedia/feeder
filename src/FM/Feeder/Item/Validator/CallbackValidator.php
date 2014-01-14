<?php

namespace FM\Feeder\Item\Validator;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class CallbackValidator implements ValidatorInterface
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

    public function validate(ParameterBag $item)
    {
        return call_user_func($this->callback, $item);
    }
}
