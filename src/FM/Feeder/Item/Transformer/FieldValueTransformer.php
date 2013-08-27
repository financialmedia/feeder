<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class FieldValueTransformer implements TransformerInterface
{
    protected $transformer;
    protected $field;

    public function __construct(DataTransformer $transformer, $field)
    {
        $this->transformer = $transformer;
        $this->field = $field;
    }

    /**
     * @param ParameterBag $item
     */
    public function transform(ParameterBag $item)
    {
        if (!$item->has($this->field)) {
            return;
        }

        $value = $item->get($this->field);
        $newValue = $this->transformer->transform($value, $this->field, $item);
        $item->set($this->field, $newValue);
    }
}
