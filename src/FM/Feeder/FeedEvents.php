<?php

namespace FM\Feeder;

final class FeedEvents
{
    /**
     * When cached version is used
     */
    const CACHED                   = 'feeder.transport.cached';

    /**
     * When starting download
     */
    const PRE_DOWNLOAD             = 'feeder.transport.pre_download';

    /**
     * After download is complete
     */
    const POST_DOWNLOAD            = 'feeder.transport.post_download';

    /**
     * When download is in progress
     */
    const DOWNLOAD_PROGRESS        = 'feeder.transport.download_progress';

    /**
     * When starting to import a resource
     */
    const RESOURCE_START           = 'feeder.feed.resource_start';

    /**
     * When ending a resource
     */
    const RESOURCE_END             = 'feeder.feed.resource_end';

    /**
     * Before serializing an item
     */
    const RESOURCE_PRE_SERIALIZE   = 'feeder.feed.resource_pre_serialize';

    /**
     * After serializing an item
     */
    const RESOURCE_POST_SERIALIZE  = 'feeder.feed.resource_post_serialize';

    /**
     * Before modifying an item
     */
    const PRE_MODIFICATION         = 'feeder.item.pre_modification';

    /**
     * After modifying an item
     */
    const POST_MODIFICATION        = 'feeder.item.post_modification';

    /**
     * When an item is filtered during modification
     */
    const ITEM_FILTERED            = 'feeder.item.filtered';

    /**
     * When an item is found invalid during modification
     */
    const ITEM_INVALID             = 'feeder.item.invalid';

    /**
     * When an item is failed during modification
     */
    const ITEM_FAILED              = 'feeder.item.failed';

    /**
     * When a modifier fails. Note that this does not have to mean the entire item fails.
     * This is determined by the continue property of the modifier and/or this event.
     */
    const ITEM_MODIFICATION_FAILED = 'feeder.item.modification_failed';
}
