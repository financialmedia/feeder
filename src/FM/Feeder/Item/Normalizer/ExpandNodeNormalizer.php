<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;

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
     * @var array
     */
    protected $overwriteKeys;

    /**
     * Constructor
     *
     * @param string  $field
     * @param boolean $removeCompound
     * @param array   $overwriteKeys
     */
    public function __construct($field, $removeCompound = false, array $overwriteKeys = array())
    {
        $this->field = $field;
        $this->removeCompound = $removeCompound;
        $this->overwriteKeys = $overwriteKeys;
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
        foreach ($value as $key => $val) {
            // if key already exists, check if we may overwrite it
            if ($item->has($key)) {
                if (!in_array($key, $this->overwriteKeys)) {
                    continue;
                }
            }

            $item->set($key, $val);
        }
    }
}
