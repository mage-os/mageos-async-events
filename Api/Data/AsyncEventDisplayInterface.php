<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

interface AsyncEventDisplayInterface
{
    /**
     * Getter for subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * Getter for asynchronous event name
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Getter for recipient/destination
     *
     * @return string
     */
    public function getRecipientUrl(): string;

    /**
     * Getter for status
     *
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * Getter for subscribed at
     *
     * @return string
     */
    public function getSubscribedAt(): string;
}
