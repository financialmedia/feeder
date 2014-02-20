<?php

namespace FM\Feeder\Tests\Modifier\Data\Transformer;

use FM\Feeder\Modifier\Data\Transformer\NormalizedArrayTransformer;

class NormalizedArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer();
        $this->assertEquals($expected, $transformer->transform($test));
    }

    public static function getTestData()
    {
        return [
            [null, null],
            ['Foo', ['Foo']],
        ];
    }

    /**
     * @dataProvider getNestedTestData
     */
    public function testNestedArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer(true);
        $this->assertEquals($expected, $transformer->transform($test));
    }

    public static function getNestedTestData()
    {
        return [
            [null, null],
            ['Foo', ['Foo']],
            [['Foo'], ['Foo']],
            [['Foo' => 'Bar'], [['Foo' => 'Bar']]],
        ];
    }
}
