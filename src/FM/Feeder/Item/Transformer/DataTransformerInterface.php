<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

interface DataTransformerInterface
{
    /**
     * @param  mixed        $value
     * @param  string       $key
     * @param  ParameterBag $item
     * @return mixed
     */
    public function transform($value, $key, ParameterBag $data);
}
