<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\Transformer\NodeToStringTransformer;

class NodeToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new NodeToStringTransformer();
    }

    /**
     * @dataProvider getTestData
     */
    public function testNodes($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test, 'foo', new ParameterBag([])));
    }

    public static function getTestData()
    {
        return array(
            array('Foo', 'Foo'),
            array(array('Foo'), array('Foo')),
            array(array('#' => 'Foo'), 'Foo'),
        );
    }
}
