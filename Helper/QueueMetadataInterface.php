<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

interface QueueMetadataInterface
{
    /**
     * Defines the queue topic name in one place, so it's easier to reference across in observers and publishers
     */
    public const EVENT_QUEUE = 'event.trigger';

    public const FAILOVER_EXCHANGE = 'event.failover';

    public const RETRY_INIT_ROUTING_KEY = 'event.retry.init';

    public const DEAD_LETTER_ROUTING_KEY = 'event.retry';

    public const DEAD_LETTER_KILL_KEY = 'event.retry.kill';
}
