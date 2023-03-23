<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

interface AsyncEventInterface
{
    /**
     * Getter for subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int;

    /**
     * Setter for subscription id
     *
     * @param int $id
     * @return void
     */
    public function setSubscriptionId(int $id): void;

    /**
     * Getter for asynchronous event name
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Setter for asynchronous event name
     *
     * @param string $eventName
     * @return void
     */
    public function setEventName(string $eventName): void;

    /**
     * Getter for recipient/destination
     *
     * Initial version was intended to work with HTTP but in time it was realised that it can be protocol independent.
     * For Amazon EventBridge this can be an ARN, for TCP this can be a socket, for AMQP this can be the host. So even
     * though it is named as recipient url it can be anything that is a destination depending on the transport protocol
     *
     * @return string
     */
    public function getRecipientUrl(): string;

    /**
     * Setter for recipient url
     *
     * @param string $recipientURL
     * @return void
     */
    public function setRecipientUrl(string $recipientURL): void;

    /**
     * Getter for verification token
     *
     * @return string
     */
    public function getVerificationToken(): string;

    /**
     * Setter for verification token
     *
     * @param string $verificationToken
     * @return void
     */
    public function setVerificationToken(string $verificationToken): void;

    /**
     * Getter for status
     *
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * Setter for status
     *
     * @param bool $status
     * @return void
     */
    public function setStatus(bool $status): void;

    /**
     * Getter for subscribed at
     *
     * @return string
     */
    public function getSubscribedAt(): string;

    /**
     * Setter for subscribed at
     *
     * @param string $subscribedAt
     * @return void
     */
    public function setSubscribedAt(string $subscribedAt): void;

    /**
     * Getter for metadata
     *
     * @return string
     */
    public function getMetadata(): string;

    /**
     * Setter for metadata
     *
     * @param string $metadata
     * @return void
     */
    public function setMetadata(string $metadata): void;

    /**
     * Getter for store id
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Setter for store id
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;
}
