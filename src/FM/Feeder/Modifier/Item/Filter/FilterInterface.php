<?php

namespace FM\Feeder\Modifier\Item\Filter;

use FM\Feeder\Modifier\Item\ModifierInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface FilterInterface extends ModifierInterface
{
    /**
     * @throws \FM\Feeder\Exception\FilterException If item needs to be filtered
     */
    public function filter(ParameterBag $item);
}
