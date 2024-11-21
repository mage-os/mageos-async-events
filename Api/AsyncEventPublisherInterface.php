<?php

namespace MageOS\AsyncEvents\Api;

interface AsyncEventPublisherInterface
{
    /**
     * Publish an asynchronous event
     *
     * @param string $eventName
     * @param array $data
     * @param int $storeId
     * @return void
     */
    public function publish(string $eventName, array $data, int $storeId): void;
}
