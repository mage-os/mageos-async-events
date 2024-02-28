<?php

namespace MageOS\AsyncEvents\Api;

use CloudEvents\V1\CloudEventImmutable;

interface RetryManagementInterface
{
    public function init(int $subscriptionId, CloudEventImmutable $event, string $uuid): void;

    public function place(int $deathCount, int $subscriptionId, CloudEventImmutable $event, string $uuid, ?int $backoff): void;

    public function kill(int $subscriptionId, CloudEventImmutable $event): void;
}
