<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface TransformerInterface extends ModifierInterface
{
    public function transform(ParameterBag $item);
}
