<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface NormalizerInterface extends ModifierInterface
{
    /**
     * @param  ParameterBag $item
     * @return void
     */
    public function normalize(ParameterBag $item);
}
