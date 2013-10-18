<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Transforms empty values to null. This is not the exact equivalent of the
 * empty() function. The difference is that this transformer will leave 0 and
 * false alone, as they could be valid feed values.
 */
class EmptyValueToNullTransformer implements DataTransformerInterface
{
    public function transform($value, $key, ParameterBag $item)
    {
        // let booleans, integers and floats pass
        if (is_null($value) || is_bool($value) || is_integer($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            if (trim($value) === '') {
                return  null;
            }
        } elseif (empty($value)) {
            return null;
        }

        return $value;
    }
}
