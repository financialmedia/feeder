<?php

namespace FM\Feeder\Tests\Event;

use FM\Feeder\Event\ResourceSerializeEvent;
use FM\Feeder\Resource\StringResource;

class ResourceSerializeEventTest extends \PHPUnit_Framework_TestCase
{
    protected static $item = '<?xml version="1.0"?><foo><bar></bar></foo>';
    protected static $serialized = ['foo' => 'bar'];

    public function testChangeOriginalItemModifiesItem()
    {
        $item = static::$item;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);
        $item = static::$serialized;
        $this->assertEquals(static::$serialized, $event->getItem());
    }

    public function testChangeReturnValueModifiesItem()
    {
        $item = static::$item;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);
        $item = &$event->getItem();
        $item = static::$serialized;
        $this->assertEquals(static::$serialized, $event->getItem());
    }

    public function testSetItemModifiesItem()
    {
        $item = static::$item;
        $serialized = static::$serialized;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);
        $event->setItem($serialized);
        $this->assertEquals(static::$serialized, $event->getItem());
        $this->assertEquals(static::$serialized, $item);
    }
}
