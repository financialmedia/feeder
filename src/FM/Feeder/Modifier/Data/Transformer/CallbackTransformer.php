<?php

namespace FM\Feeder\Modifier\Data\Transformer;

use FM\Feeder\Exception\UnexpectedTypeException;

class CallbackTransformer implements TransformerInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
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
    public function transform($value)
    {
        return call_user_func($this->callback, $value);
    }
}
