<?php

namespace FM\Feeder;

final class FeedEvents
{
    const CACHED                   = 'feeder.transport.cached';
    const PRE_DOWNLOAD             = 'feeder.transport.pre_download';
    const POST_DOWNLOAD            = 'feeder.transport.post_download';
    const DOWNLOAD_PROGRESS        = 'feeder.transport.download_progress';

    const BREAK_UP                 = 'feeder.feed.break_up';
    const RESOURCE_START           = 'feeder.feed.resource_start';
    const RESOURCE_END             = 'feeder.feed.resource_end';
    const RESOURCE_PRE_SERIALIZE   = 'feeder.feed.resource_pre_serialize';
    const RESOURCE_POST_SERIALIZE  = 'feeder.feed.resource_post_serialize';

    const PRE_MODIFICATION         = 'feeder.item.pre_modification';
    const POST_MODIFICATION        = 'feeder.item.post_modification';
    const ITEM_FILTERED            = 'feeder.item.filtered';
    const ITEM_MODIFICATION_FAIL   = 'feeder.item.modification_fail';
    const ITEM_MODIFICATION_FAILED = 'feeder.item.modification_failed';
}
