<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

interface AsyncEventInterface
{
    /**
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * @param int $id
     * @return void
     */
    public function setSubscriptionId(int $id);

    /**
     * @return string
     */
    public function getEventName(): string;

    /**
     * @param string $eventName
     * @return void
     */
    public function setEventName(string $eventName);

    /**
     * @return string
     */
    public function getRecipientUrl(): string;

    /**
     * @param string $recipientURL
     * @return void
     */
    public function setRecipientUrl(string $recipientURL);

    /**
     * @return string
     */
    public function getVerificationToken(): string;

    /**
     * @param string $verificationToken
     * @return void
     */
    public function setVerificationToken(string $verificationToken);

    /**
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * @param bool $status
     * @return void
     */
    public function setStatus(bool $status);

    /**
     * @return string
     */
    public function getSubscribedAt(): string;

    /**
     * @param string $subscribedAt
     * @return void
     */
    public function setSubscribedAt(string $subscribedAt);

    /**
     * @return string
     */
    public function getMetadata(): string;

    /**
     * @param string $metadata
     * @return void
     */
    public function setMetadata(string $metadata);
}
