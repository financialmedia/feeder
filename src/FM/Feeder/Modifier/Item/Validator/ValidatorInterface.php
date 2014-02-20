<?php

namespace FM\Feeder\Modifier\Item\Validator;

use FM\Feeder\Modifier\Item\ModifierInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ValidatorInterface extends ModifierInterface
{
    /**
     * @throws \FM\Feeder\Exception\ValidationException If item is invalid
     */
    public function validate(ParameterBag $item);
}
