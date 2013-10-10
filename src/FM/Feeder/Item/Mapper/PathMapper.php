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
            // get the value, but only if it's not null
            if (null === $value = $item->get($from, null, true)) {
                continue;
            }

            // if value is empty, only set it when we don't already have this value
            if (empty($value) && $item->has($to)) {
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
}
