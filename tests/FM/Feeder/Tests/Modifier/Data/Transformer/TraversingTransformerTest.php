<?php

namespace FM\Feeder\Tests\Modifier\Data\Transformer;

use FM\Feeder\Modifier\Data\Transformer\TransformerInterface;
use FM\Feeder\Modifier\Data\Transformer\TraversingTransformer;

class TraversingTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TraversingTransformer
     */
    protected $transformer;

    /**
     * @var TransformerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $innerTransformer;

    protected function setup()
    {
        $this->innerTransformer = $this
            ->getMockBuilder('FM\Feeder\Modifier\Data\Transformer\TransformerInterface')
            ->setMethods(['transform'])
            ->getMockForAbstractClass()
        ;

        $this->innerTransformer
            ->expects($this->any())
            ->method('transform')
            ->will($this->returnCallback(function ($value) {
                return $value + 5;
            }))
        ;
    }

    public function testArrayTransformation()
    {
        $transformer = new TraversingTransformer($this->innerTransformer);

        $value = [
            10,
            20,
            30,
        ];

        $newValue = $transformer->transform($value);

        $this->assertSame(15, $newValue[0]);
        $this->assertSame(25, $newValue[1]);
        $this->assertSame(35, $newValue[2]);
    }

    public function testTraversableTransformation()
    {
        $transformer = new TraversingTransformer($this->innerTransformer);

        $value = new \ArrayIterator([
            10,
            20,
            30,
        ]);

        /** @var \ArrayIterator $newValue */
        $newValue = $transformer->transform($value);

        $this->assertSame(15, $newValue->offsetGet(0));
        $this->assertSame(25, $newValue->offsetGet(1));
        $this->assertSame(35, $newValue->offsetGet(2));
    }
}
