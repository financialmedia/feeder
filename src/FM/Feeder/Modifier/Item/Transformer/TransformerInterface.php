<?php

namespace FM\Feeder\Modifier\Item\Transformer;

use FM\Feeder\Modifier\Item\ModifierInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface TransformerInterface extends ModifierInterface
{
    /**
     * @param ParameterBag $item
     *
     * @return void
     */
    public function transform(ParameterBag $item);
}
