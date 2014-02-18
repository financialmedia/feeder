<?php

namespace FM\Feeder\Item\Normalizer;

use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Exception\UnexpectedTypeException;

class ExpandAttributesNormalizer implements NormalizerInterface
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
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($field = null, $removeCompound = false, array $overwriteKeys = array())
    {
        if (!is_string($field) && !is_null($field)) {
            throw new UnexpectedTypeException($field, 'string or null');
        }

        $this->field = $field;
        $this->removeCompound = $removeCompound;
        $this->overwriteKeys = $overwriteKeys;
    }

    public function normalize(ParameterBag $item)
    {
        if (null === $this->field) {
            $this->expand($item->all(), $item);
        } else {
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
    }

    protected function expand(array $value, ParameterBag $item)
    {
        foreach ($value as $name => $val) {
            // attributes are converted to @attribute
            if (substr($name, 0, 1) === '@') {
                $name = ltrim($name, '@');

                // if key already exists, check if we may overwrite it
                if ($item->has($name)) {
                    if (!in_array($name, $this->overwriteKeys)) {
                        continue;
                    }
                }

                $item->set($name, $val);
            }
        }
    }
}
