<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

interface QueueMetadataInterface
{
    /**
     * Defines the queue topic name in one place so it's easier to reference across in observers and publishers
     */
    const EVENT_QUEUE = 'event.trigger';

    const FAILOVER_EXCHANGE = 'event.failover';

    const RETRY_INIT_QUEUE = 'event.delay.1';

    const RETRY_INIT_ROUTING_KEY = 'event.retry.init';

    const DEAD_LETTER_ROUTING_KEY = 'event.retry';

    const DEAD_LETTER_KILL_KEY = 'event.retry.kill';
}
