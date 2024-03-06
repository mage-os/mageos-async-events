<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api;

interface AsyncEventPublisherInterface
{
    /**
     * Publish an asynchronous event
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function publish(string $eventName, array $data): void;
}
