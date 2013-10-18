<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class InverseBooleanTransformer implements DataTransformerInterface
{
    public function transform($value, $key, ParameterBag $item)
    {
        if (is_null($value) || ($value === '')) {
            return null;
        }

        return ! (bool) $value;
    }
}
