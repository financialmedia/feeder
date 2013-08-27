<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class ItemNotModifiedEvent extends Event
{
    protected $item;
    protected $reason;

    public function __construct(ParameterBag $item, $reason)
    {
        $this->item = $item;
        $this->reason = $reason;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getReason()
    {
        return $this->reason;
    }
}
