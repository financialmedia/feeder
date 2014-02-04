<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

interface ResourceTransformer
{
    /**
     * @param \FM\Feeder\Resource\Resource           $resource
     * @param \FM\Feeder\Resource\ResourceCollection $collection
     *
     * @return \FM\Feeder\Resource\Resource
     */
    public function transform(Resource $resource, ResourceCollection $collection);

    /**
     * @param \FM\Feeder\Resource\Resource $resource
     *
     * @return boolean
     */
    public function needsTransforming(Resource $resource);
}
