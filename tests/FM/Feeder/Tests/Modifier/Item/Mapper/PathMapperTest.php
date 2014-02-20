<?php

namespace FM\Feeder\Tests\Modifier\Item\Mapper;

use FM\Feeder\Modifier\Item\Mapper\PathMapper;
use Symfony\Component\HttpFoundation\ParameterBag;

class PathMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapping()
    {
        $mapping = [
            'test-foo'    => 'foo',
            'test-bar'    => 'bar',
            'test-baz'    => 'baz',
            'not-in-item' => 'should not appear in mapped'
        ];

        $item = [
            'test-foo' => 'foo value',
            'test-bar' => 'bar value',
            'test-baz' => null,
            'unmapped' => 'unmapped value',
        ];

        $expected = [
            'foo'      => 'foo value',
            'bar'      => 'bar value',
            'baz'      => null,
            'unmapped' => 'unmapped value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }

    public function testMappingWithOverride()
    {
        $mapping = [
            'test-foo'  => 'foo',
            'other-foo' => 'foo',
            'test-bar'  => 'bar',
        ];

        $item = [
            'test-foo'  => 'foo value',
            'other-foo' => 'overriden foo value',
            'test-bar'  => 'bar value',
        ];

        $expected = [
            'foo'      => 'overriden foo value',
            'bar'      => 'bar value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }

    public function testMappingWithNoOverride()
    {
        $mapping = [
            'test-foo'  => 'foo',
            'other-foo' => 'foo',
            'test-bar'  => 'bar',
        ];

        $item = [
            'test-foo'  => 'foo value',
            'other-foo' => '',
            'test-bar'  => 'bar value',
        ];

        $expected = [
            'foo'      => 'foo value',
            'bar'      => 'bar value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }
}
