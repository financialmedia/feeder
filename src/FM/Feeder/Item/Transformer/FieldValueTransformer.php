<?php

namespace FM\Feeder\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\TransformationFailedException;

class FieldValueTransformer implements TransformerInterface
{
    /**
     * @var DataTransformerInterface
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param DataTransformerInterface $transformer
     * @param string                   $field
     */
    public function __construct(DataTransformerInterface $transformer, $field)
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

        try {
            $newValue = $this->transformer->transform($value, $this->field, $item);
        } catch (TransformationFailedException $e) {
            // set the value to null as we couldn't transform it
            $item->set($this->field, null);

            throw new TransformationFailedException(
                sprintf(
                    'Transforming "%s" using "%s" failed with message: %s.',
                    $this->field,
                    get_class($this->transformer),
                    $e->getMessage()
                ),
                null,
                $e
            );
        }

        $item->set($this->field, $newValue);
    }
}
