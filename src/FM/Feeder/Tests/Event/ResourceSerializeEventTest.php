<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use FM\Feeder\Event\ResourceSerializeEvent;

class ResourceSerializeEventTest extends \PHPUnit_Framework_TestCase
{
    protected static $resource = '<?xml version="1.0"?><foo><bar></bar></foo>';
    protected static $serialized = ['foo' => 'bar'];

    public function testReferences()
    {
        // changing original input modifies resource
        $resource = static::$resource;
        $event = new ResourceSerializeEvent($resource);
        $resource = static::$serialized;
        $this->assertEquals(static::$serialized, $event->getResource());

        // changing getter return value modifies resource
        $resource = static::$resource;
        $event = new ResourceSerializeEvent($resource);
        $er = &$event->getResource();
        $er = static::$serialized;
        $this->assertEquals(static::$serialized, $event->getResource());
        $this->assertEquals(static::$serialized, $resource);

        // set a new resource value modifies resource
        $resource = static::$resource;
        $serialized = static::$serialized;
        $event = new ResourceSerializeEvent($resource);
        $event->setResource($serialized);
        $this->assertEquals(static::$serialized, $event->getResource());
        $this->assertEquals(static::$serialized, $resource);
    }
}
