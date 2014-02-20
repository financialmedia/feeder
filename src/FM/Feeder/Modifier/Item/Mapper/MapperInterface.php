<?php

namespace FM\Feeder\Modifier\Item\Mapper;

use FM\Feeder\Modifier\Item\ModifierInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface MapperInterface extends ModifierInterface
{
    /**
     * @param ParameterBag $item
     *
     * @return ParameterBag
     */
    public function map(ParameterBag $item);
}
