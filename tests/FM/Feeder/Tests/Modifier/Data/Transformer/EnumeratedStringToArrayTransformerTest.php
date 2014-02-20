<?php

namespace FM\Feeder\Tests\Modifier\Data\Transformer;

use FM\Feeder\Modifier\Data\Transformer\EnumeratedStringToArrayTransformer;

class EnumeratedStringToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnumeratedStringToArrayTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new EnumeratedStringToArrayTransformer([',', '/', '+', 'and']);
    }

    /**
     * @dataProvider getTestData
     */
    public function testEnumeratedStrings($string, array $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($string));
    }

    public static function getTestData()
    {
        return [
            ['foo, bar', ['foo', 'bar']],
            ['foo/bar', ['foo', 'bar']],
            ['foo / bar', ['foo', 'bar']],
            ['foo, bar and baz', ['foo', 'bar', 'baz']],
            ['foo,bar +baz', ['foo', 'bar', 'baz']],
        ];
    }
}
