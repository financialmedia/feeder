<?php

namespace FM\Feeder\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface FilterInterface extends ModifierInterface
{
    /**
     * @throws \FM\Feeder\Exception\FilterException If item needs to be filtered
     */
    public function filter(ParameterBag $item);
}
