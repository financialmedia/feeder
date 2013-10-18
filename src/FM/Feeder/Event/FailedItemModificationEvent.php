<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\ModifierInterface;

class FailedItemModificationEvent extends Event
{
    protected $item;
    protected $modifier;
    protected $exception;
    protected $continue = false;

    public function __construct(ParameterBag $item, ModifierInterface $modifier, $exception)
    {
        $this->item = $item;
        $this->modifier = $modifier;
        $this->exception = $exception;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function setContinue($bool)
    {
        $this->continue = (boolean) $bool;
    }

    public function getContinue()
    {
        return $this->continue;
    }
}
