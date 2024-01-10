<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

interface AsyncEventDisplayInterface
{
    /**
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * @return string
     */
    public function getEventName(): string;

    /**
     * @return string
     */
    public function getRecipientUrl(): string;

    /**
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * @return string
     */
    public function getSubscribedAt(): string;
}
