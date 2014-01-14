<?php

namespace FM\Feeder\Item\Validator;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

interface ValidatorInterface extends ModifierInterface
{
    /**
     * @throws \FM\Feeder\Exception\ValidationException If item is invalid
     */
    public function validate(ParameterBag $item);
}
