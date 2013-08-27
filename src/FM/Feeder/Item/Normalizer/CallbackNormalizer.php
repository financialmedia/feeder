<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class CallbackNormalizer implements NormalizerInterface
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

    public function normalize(ParameterBag $item)
    {
        call_user_func($this->callback, $item);
    }
}
