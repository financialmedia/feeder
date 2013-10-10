<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

class TrimNormalizer implements NormalizerInterface
{
    public function normalize(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->trimValues($parameters);
        $item->replace($parameters);
    }

    protected function trimValues(array &$arr)
    {
        foreach ($arr as $key => &$value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if (is_array($value)) {
                $this->trimValues($value);
            }
        }
    }
}
