<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

/**
 * Transforms a string to an array, using one or more delimiters.
 */
class EnumeratedStringToArrayTransformer implements DataTransformer
{
    /**
     * @var array
     */
    protected $delimiters;

    public function __construct(array $delimiters = array())
    {
        $this->delimiters = !empty($delimiters) ? $delimiters : array(',');
        $this->regex = sprintf('/[%s]+/', implode('|', array_map(function($delimiter) {
            return preg_quote($delimiter, '/');
        }, $this->delimiters)));
    }

    public function transform($value, $key, ParameterBag $item)
    {
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return preg_split($this->regex, $value, PREG_SPLIT_NO_EMPTY);
    }
}
