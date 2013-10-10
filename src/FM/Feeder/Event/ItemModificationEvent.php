<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class ItemModificationEvent extends Event
{
    protected $item;

    public function __construct(ParameterBag $item)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $this->item;
    }
}
