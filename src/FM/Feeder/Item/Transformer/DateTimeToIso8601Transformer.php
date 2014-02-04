<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\TransformationFailedException;

class DateTimeToIso8601Transformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value, $key, ParameterBag $item)
    {
        if (is_null($value)) {
            return null;
        }

        if (!$value instanceof \DateTime) {
            throw new TransformationFailedException(
                sprintf('Expected a DateTime to transform, got "%s" instead.', json_encode($value))
            );
        }

        return $value->format('c');
    }
}
