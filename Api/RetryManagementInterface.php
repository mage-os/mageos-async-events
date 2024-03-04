<?php

namespace MageOS\AsyncEvents\Api;

interface RetryManagementInterface
{
    public function init(int $subscriptionId, mixed $data, string $uuid): void;

    public function place(int $deathCount, int $subscriptionId, mixed $data, string $uuid, ?int $backoff): void;

    public function kill(int $subscriptionId, mixed $data): void;
}
