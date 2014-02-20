<?php

namespace FM\Feeder\Modifier\Data\Transformer;

use FM\Feeder\Exception\TransformationFailedException;

interface TransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws TransformationFailedException
     */
    public function transform($value);
}
