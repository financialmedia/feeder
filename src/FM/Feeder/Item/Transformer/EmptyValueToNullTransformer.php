<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class EmptyValueToNullTransformer implements DataTransformer
{
    public function transform($value, $key, ParameterBag $item)
    {
        if (is_null($value) || empty($value)) {
            return null;
        }

        return $value;
    }
}
