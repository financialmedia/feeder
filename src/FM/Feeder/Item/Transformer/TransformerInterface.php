<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface TransformerInterface extends ModifierInterface
{
    /**
     * @param  ParameterBag                                       $item
     * @throws \FM\Feeder\Exception\TransformationFailedException
     */
    public function transform(ParameterBag $item);
}
