<?php

namespace FM\Feeder\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface MapperInterface extends ModifierInterface
{
    /**
     * @param  ParameterBag $item
     * @return ParameterBag
     */
    public function map(ParameterBag $item);
}
