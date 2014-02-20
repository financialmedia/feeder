<?php

namespace FM\Feeder\Modifier\Data\Transformer;

class JsonToNativeTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        return json_decode($value, true);
    }
}
