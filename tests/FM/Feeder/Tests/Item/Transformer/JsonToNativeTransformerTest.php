<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\Transformer\JsonToNativeTransformer;

class JsonToNativeTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonToNativeTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new JsonToNativeTransformer();
    }

    /**
     * @dataProvider getTestData
     */
    public function testData($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test, 'foo', new ParameterBag()));
    }

    public static function getTestData()
    {
        return [
            ['true', true],
            ['0', 0],
            ['1234.56', 1234.56],
            ['foo', null],
            ['"foo"', 'foo'],
            ['["foo", "bar"]', ['foo', 'bar']],
            ['{"foo": "bar"}', ['foo' => 'bar']]
        ];
    }
}
