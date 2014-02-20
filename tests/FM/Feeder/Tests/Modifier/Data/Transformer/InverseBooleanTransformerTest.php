<?php

namespace FM\Feeder\Tests\Modifier\Data\Transformer;

use FM\Feeder\Modifier\Data\Transformer\InverseBooleanTransformer;

class InverseBooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InverseBooleanTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new InverseBooleanTransformer([',', '/', '+', 'and']);
    }

    /**
     * @dataProvider getTestData
     */
    public function testBooleans($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test));
    }

    public static function getTestData()
    {
        return [
            [true, false],
            [false, true],
            [null, null],
            ['', null],
            [0, true],
            ['0', true],
            [1, false],
            ['1', false],
            ['foo', false],
        ];
    }
}
