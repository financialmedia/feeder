<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\Transformer\NormalizedArrayTransformer;

class NormalizedArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer();
        $this->assertEquals($expected, $transformer->transform($test, 'foo', new ParameterBag([])));
    }

    public static function getTestData()
    {
        return array(
            array(null, null),
            array('Foo', array('Foo')),
        );
    }

    /**
     * @dataProvider getNestedTestData
     */
    public function testNestedArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer(true);
        $this->assertEquals($expected, $transformer->transform($test, 'foo', new ParameterBag([])));
    }

    public static function getNestedTestData()
    {
        return array(
            array(null, null),
            array('Foo', array('Foo')),
            array(array('Foo'), array('Foo')),
            array(array('Foo' => 'Bar'), array(array('Foo' => 'Bar'))),
        );
    }
}
