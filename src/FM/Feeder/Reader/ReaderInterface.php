<?php

namespace FM\Feeder\Reader;

use FM\Feeder\Resource\ResourceCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ReaderInterface extends \Iterator
{
    /**
     * Reads the next item in the feed
     *
     * @return ParameterBag
     */
    public function read();

    /**
     * @param ResourceCollection $resources
     */
    public function setResources(ResourceCollection $resources);

    /**
     * @return ResourceCollection
     */
    public function getResources();
}
