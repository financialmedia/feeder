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
            if (null !== $value = $item->get($from, null, true)) {
                $item->set($to, $value);

                if ($to !== $from) {
                    $item->remove($from);
                }
            }
        }

        return $item;
    }
}
