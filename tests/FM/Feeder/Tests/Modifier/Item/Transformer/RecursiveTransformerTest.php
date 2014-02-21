<?php

namespace FM\Feeder\Tests\Modifier\Item\Transformer;

use FM\Feeder\Modifier\Data\Transformer\EmptyValueToNullTransformer;
use FM\Feeder\Modifier\Item\Transformer\RecursiveTransformer;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Test recursive an array, EmptyValueToNullTransformer is used to test.
 *
 * Class RecursiveTransformerTest
 * @package FM\Feeder\Tests\Modifier\Item\Transformer
 */
class RecursiveTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformer()
    {
        $item = new ParameterBag();

        $item->set('key', 'value');
        $item->set('array', ['value1', 'value2', '', ['value3', 'value4', '']]);

        $original = $item->all();

        $transformer = new RecursiveTransformer(new EmptyValueToNullTransformer());

        $transformer->transform($item);

        $array = $item->all();

        $this->iterate($array, $original);

    }

    /**
     * @param $resultValue
     * @param $originalValue
     */
    protected function iterate($resultValue, $originalValue)
    {
        if (is_array($resultValue)) {
            foreach($resultValue as $key=>$value){
                $this->iterate($resultValue[$key], $originalValue[$key]);
            }
        } else {
            $this->unittest($resultValue, $originalValue);
        }
    }

    /**
     * @param $resultValue
     * @param $originalValue
     */
    protected function unittest($resultValue, $originalValue)
    {
        //An empty string should be transformed into an NULL value. So when the $originalValue is an empty string, I expect a NULL.
        if ($originalValue === '') {
            $originalValue = null;
        }

        $this->assertSame($resultValue, $originalValue);
    }
}
