<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class JsonToNativeTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value, $key, ParameterBag $data)
    {
        return json_decode($value, true);
    }
}
