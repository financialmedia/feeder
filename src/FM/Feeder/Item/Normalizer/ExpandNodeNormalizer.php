<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class ExpandNodeNormalizer implements NormalizerInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var boolean
     */
    protected $removeCompound;

    /**
     * Constructor
     *
     * @param string  $field
     * @param boolean $removeCompound
     */
    public function __construct($field, $removeCompound = false)
    {
        $this->field = $field;
        $this->removeCompound = $removeCompound;
    }

    public function normalize(ParameterBag $item)
    {
        if ($item->has($this->field)) {
            $value = $item->get($this->field);

            // check if the field is an array
            if (is_array($value)) {
                $this->expand($value, $item);
            }

            // remove the compound field if requested
            if ($this->removeCompound) {
                $item->remove($this->field);
            }
        }
    }

    protected function expand(array $value, ParameterBag $item)
    {
        foreach ($value as $name => $val) {
            $item->set($name, $val);
        }
    }
}
