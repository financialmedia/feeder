<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Filters out unwanted fields.
 */
class RemoveFieldsNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * Constructor
     *
     * @param array $fields The fields to remove
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function normalize(ParameterBag $item)
    {
        foreach ($item->keys() as $key) {
            if (in_array($key, $this->fields)) {
                $item->remove($key);
            }
        }
    }
}
