<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Makes sure obsolete fields are removed
 */
class ObsoleteFieldsNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * Constructor
     *
     * @param array $fields The fields to keep. Any other field will be removed.
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function normalize(ParameterBag $item)
    {
        foreach ($item->keys() as $key) {
            if (!in_array($key, $this->fields)) {
                $item->remove($key);
            }
        }
    }
}
