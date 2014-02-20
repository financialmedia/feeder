<?php

namespace FM\Feeder\Modifier\Data\Transformer;

class InverseBooleanTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_null($value) || ($value === '')) {
            return null;
        }

        return ! (bool) $value;
    }
}
