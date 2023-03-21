<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

use Magento\Framework\DataObject;

class NotifierResult extends DataObject
{
    private const SUCCESS = 'success';
    private const SUBSCRIPTION_ID = 'subscription_id';
    private const RESPONSE_DATA = 'response_data';
    private const UUID = 'uuid';
    private const DATA = 'data';

    /**
     * Getter for success
     *
     * @return bool
     */
    public function getSuccess(): bool
    {
        return (bool) $this->getData(self::SUCCESS);
    }

    /**
     * Setter for success
     *
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
}
