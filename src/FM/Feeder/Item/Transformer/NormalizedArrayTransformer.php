<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\TransformationFailedException;

class NormalizedArrayTransformer implements DataTransformer
{
    protected $nestedArrays;

    public function __construct($nestedArrays = false)
    {
        $this->nestedArrays = $nestedArrays;
    }

    public function transform($value, $key, ParameterBag $item)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_scalar($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('Expected a scalar value or array to transform, got "%s" instead.', json_encode($value)));
        }

        if ($this->nestedArrays && !is_numeric(key($value))) {
            $value = [$value];
        }

        return $value;
    }
}
