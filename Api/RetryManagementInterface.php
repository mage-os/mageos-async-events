<?php

namespace MageOS\AsyncEvents\Api;

use CloudEvents\V1\CloudEventImmutable as Event;

interface RetryManagementInterface
{
    /**
     * Start the chain for retrying an asynchronous event that has failed
     *
     * @param int $subscriptionId
     * @param Event $event
     * @param string $uuid
     * @return void
     */
    public function init(int $subscriptionId, Event $event, string $uuid): void;

    /**
     * Place an asynchronous event to be retried for the nth time
     *
     * @param int $deathCount
     * @param int $subscriptionId
     * @param Event $event
     * @param string $uuid
     * @param int|null $backoff
     * @return void
     */
    public function place(int $deathCount, int $subscriptionId, Event $event, string $uuid, ?int $backoff): void;

    /**
     * Kill the asynchronous event and send it to the DEAD LETTERS department
     *
     * @param int $subscriptionId
     * @param Event $event
     * @return void
     */
    public function kill(int $subscriptionId, Event $event): void;
}
