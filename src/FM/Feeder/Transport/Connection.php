<?php

namespace FM\Feeder\Transport;

class Connection implements \ArrayAccess
{
    protected $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->options[] = $value;
        } else {
            $this->options[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->options[$offset]) ? $this->options[$offset] : null;
    }

    public function toArray()
    {
        return $this->options;
    }

    public function getHash()
    {
        return md5(json_encode($this->options));
    }

    public function __toString()
    {
        foreach (array('name', 'url', 'file') as $attempt) {
            if (array_key_exists($attempt, $this->options)) {
                return $this->options[$attempt];
            }
        }

        return json_encode($this);
    }
}
