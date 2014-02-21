<?php

namespace FM\Feeder\Tests\Modifier\Item\Transformer;

use FM\Feeder\Modifier\Data\Transformer\EmptyValueToNullTransformer;
use FM\Feeder\Modifier\Item\Mapper\PathMapper;
use FM\Feeder\Modifier\Item\Transformer\CallbackTransformer;
use FM\Feeder\Modifier\Item\Transformer\RecursiveTransformer;
use Symfony\Component\HttpFoundation\ParameterBag;

class RecursiveTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformer()
    {
        $item = new ParameterBag();

        $item->add('key', 'value');
        $item->add('array', ['value1', 'value2', '', ['value3', 'value4', '']]);

        $transformer = new RecursiveTransformer(new EmptyValueToNullTransformer());

        $transformer->transform($item);


    }
}
