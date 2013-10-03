<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class NodeToStringTransformer implements DataTransformer
{
    public function transform($value, $key, ParameterBag $item)
    {
        // if value is an array with a hash, that's a serialized node's text value
        if (is_array($value) && array_key_exists('#', $value)) {
            return $value['#'];
        }

        return $value;
    }
}
