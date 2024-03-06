<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

use Magento\Framework\DataObject;
use MageOS\AsyncEvents\Api\Data\ResultInterface;

class NotifierResult extends DataObject implements ResultInterface
{
    private const SUCCESS = 'success';
    private const SUBSCRIPTION_ID = 'subscription_id';
    private const RESPONSE_DATA = 'response_data';
    private const UUID = 'uuid';
    private const DATA = 'data';
    private const IS_RETRYABLE = 'is_retryable';
    private const RETRY_AFTER = 'retry_after';

    /**
     * Getter for success
     *
     * @deprecated use getIsSuccessful instead
     * @see NotifierResult::getIsSuccessful
     * @return bool
     */
    public function getSuccess(): bool
    {
        return (bool) $this->getData(self::SUCCESS);
    }

    /**
     * Setter for success
     *
     * @deprecated use setIsSuccessful instead
     * @see NotifierResult::setIsSuccessful
     * @param bool $success
     * @return void
     */
    public function setSuccess(bool $success): void
    {
        $this->setData(self::SUCCESS, $success);
    }

    /**
     * Getter for subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Setter for subscription id
     *
     * @param int $subscriptionId
     * @return void
     */
    public function setSubscriptionId(int $subscriptionId): void
    {
        $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Getter for response data
     *
     * @return string
     */
    public function getResponseData(): string
    {
        return (string) $this->getData(self::RESPONSE_DATA);
    }

    /**
     * Setter for response data
     *
     * @param string $responseData
     * @return void
     */
    public function setResponseData(string $responseData): void
    {
        $this->setData(self::RESPONSE_DATA, $responseData);
    }

    /**
     * Getter for UUID
     *
     * @return string
     */
    public function getUuid(): string
    {
        return (string) $this->getData(self::UUID);
    }

    /**
     * Setter for UUID
     *
     * @param string $uuid
     * @return void
     */
    public function setUuid(string $uuid): void
    {
        $this->setData(self::UUID, $uuid);
    }

    /**
     * Getter for async event data
     *
     * @return array
     */
    public function getAsyncEventData(): array
    {
        return $this->getData(self::DATA);
    }

    /**
     * Setter for async event data
     *
     * @param array $eventData
     * @return void
     */
    public function setAsyncEventData(array $eventData): void
    {
        $this->setData(self::DATA, $eventData);
    }

    /**
     * @inheritDoc
     */
    public function getIsSuccessful(): bool
    {
        return $this->getSuccess();
    }

    /**
     * @inheritDoc
     */
    public function setIsSuccessful(bool $isSuccessful): void
    {
        $this->setSuccess($isSuccessful);
    }

    /**
     * @inheritDoc
     */
    public function getIsRetryable(): bool
    {
        return (bool) $this->getData(self::IS_RETRYABLE);
    }

    /**
     * @inheritDoc
     */
    public function setIsRetryable(bool $isRetryable): void
    {
        $this->setData(self::IS_RETRYABLE, $isRetryable);
    }

    /**
     * @inheritDoc
     */
    public function getRetryAfter(): ?int
    {
        return $this->getData(self::RETRY_AFTER);
    }

    /**
     * @inheritDoc
     */
    public function setRetryAfter(int $retryAfter): void
    {
        $this->setData(self::RETRY_AFTER, $retryAfter);
    }
}
