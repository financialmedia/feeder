<?php

namespace FM\Feeder\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;

class BasicMapper implements MapperInterface
{
    protected $mapping = [];

    public function __construct(array $mapping = array())
    {
        $this->set($mapping);
    }

    public function add($fromField, $toField)
    {
        $this->mapping[$fromField] = $toField;
    }

    public function set(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function mapToField($fromField)
    {
        return array_key_exists($fromField, $this->mapping) ? $this->mapping[$fromField] : null;
    }

    public function mapFromField($toField)
    {
        if (false !== $key = array_search($toField, $this->mapping)) {
            return $key;
        }
    }

    public function map(ParameterBag $item)
    {
        $mapped = new ParameterBag();

        foreach ($this->mapping as $from => $to) {
            if ($item->has($from)) {
                $mapped->set($to, $item->get($from));
            }
        }

        return $mapped;
    }
}
