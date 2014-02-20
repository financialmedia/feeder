<?php

namespace FM\Feeder\Modifier\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;

class BasicMapper implements MapperInterface
{
    /**
     * @var array<string,string>
     */
    protected $mapping = [];

    /**
     * @param array<string,string> $mapping
     */
    public function __construct(array $mapping = array())
    {
        $this->set($mapping);
    }

    /**
     * @param string $fromField
     * @param string $toField
     */
    public function add($fromField, $toField)
    {
        $this->mapping[$fromField] = $toField;
    }

    /**
     * @param array<string,string> $mapping
     */
    public function set(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param string $fromField
     *
     * @return string|null
     */
    public function mapToField($fromField)
    {
        return array_key_exists($fromField, $this->mapping) ? $this->mapping[$fromField] : null;
    }

    /**
     * @param string $toField
     *
     * @return string|null
     */
    public function mapFromField($toField)
    {
        if (false !== $key = array_search($toField, $this->mapping)) {
            return $key;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
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
