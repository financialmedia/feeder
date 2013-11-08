<?php

namespace FM\CascoBundle\Tests\Import\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Item\Transformer\InverseBooleanTransformer;

class InverseBooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new InverseBooleanTransformer(array(',', '/', '+', 'and'));
    }

    /**
     * @dataProvider getTestData
     */
    public function testBooleans($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test, 'foo', new ParameterBag([])));
    }

    public static function getTestData()
    {
        return array(
            array(true, false),
            array(false, true),
            array(null, null),
            array('', null),
            array(0, true),
            array('0', true),
            array(1, false),
            array('1', false),
            array('foo', false),
        );
    }
}
