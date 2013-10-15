<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

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
        $this->regex = sprintf('/(%s)+/', implode('|', array_map(function($delimiter) {
            if (mb_strlen($delimiter) > 1) {
                // treat it as a word
                return '\b' . preg_quote($delimiter, '/') . '\b';
            }

            return preg_quote($delimiter, '/');
        }, $this->delimiters)));
    }

    public function transform($value, $key, ParameterBag $item)
    {
        if (is_string($value)) {
            return array_map('trim', preg_split($this->regex, $value, null, PREG_SPLIT_NO_EMPTY));
        }

        return $value;
    }
}
