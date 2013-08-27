<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

interface ResourceTransformer
{
    /**
     * @param  Resource $resource
     * @return Resource
     */
    public function transform(Resource $resource, ResourceCollection $collection);

    public function needsTransforming(Resource $resource);
}
