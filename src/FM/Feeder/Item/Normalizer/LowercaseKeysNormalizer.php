<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

class LowercaseKeysNormalizer implements NormalizerInterface
{
    public function normalize(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->lowercaseKeys($parameters);
        $item->replace($parameters);
    }

    protected function lowercaseKeys(array &$arr)
    {
        $arr = array_change_key_case($arr, CASE_LOWER);

        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                $this->lowercaseKeys($value);
            }
        }
    }
}
