<?php

namespace FM\Feeder\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Performs deep path search in ParameterBag.
 * This supports fields like: foo[bar][baz]
 */
class PathMapper extends BasicMapper
{
    public function map(ParameterBag $item)
    {
        foreach ($this->mapping as $from => $to) {
            // use a special kind of null value to check, because we do want a
            // `null` value if it's actually set, but we cannot use the has()
            // method on deep paths, like foo[bar]
            if ('__null__' === $value = $item->get($from, '__null__', true)) {
                continue;
            }

            // if value is null, only set it when we don't already have this value
            if ($item->has($to) && !$this->mayOverride($item->get($to), $value)) {
                $item->remove($from);
                continue;
            }

            $item->set($to, $value);

            // remove the original if the key is mapped to a different key
            if ($to !== $from) {
                $item->remove($from);
            }
        }

        return $item;
    }

    /**
     * Decides whether a value may override a previous value
     *
     * @param mixed $previous
     * @param mixed $value
     *
     * @return boolean
     *
     * @todo implement override strategy with options: keep and override
     */
    protected function mayOverride($previous, $value)
    {
        return !empty($value) || empty($previous);
    }
}
