<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\Transformer\EnumeratedStringToArrayTransformer;

class EnumeratedStringToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new EnumeratedStringToArrayTransformer(array(',', '/', '+', 'and'));
    }

    /**
     * @dataProvider getTestData
     */
    public function testEnumeratedStrings($string, array $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($string, 'foo', new ParameterBag([])));
    }

    public static function getTestData()
    {
        return array(
            array('foo, bar', ['foo', 'bar']),
            array('foo/bar', ['foo', 'bar']),
            array('foo / bar', ['foo', 'bar']),
            array('foo, bar and baz', ['foo', 'bar', 'baz']),
            array('foo,bar +baz', ['foo', 'bar', 'baz']),
        );
    }
}
