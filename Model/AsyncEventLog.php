<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use Magento\Framework\Model\AbstractModel;

class AsyncEventLog extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'async_event_subscriber_log';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AsyncEventLog::class);
    }

    /**
     * Getter for log id
     *
     * @return int
     */
    public function getLogId(): int
    {
        return (int) $this->getData('log_id');
    }

    /**
     * Setter for log id
     *
     * @param int $logId
     * @return void
     */
    public function setLogId(int $logId): void
    {
        $this->setData('log_id', $logId);
    }

    /**
     * Getter for subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData('subscription_id');
    }

    /**
     * Setter for subscription id
     *
     * @param int $subscriptionId
     * @return void
     */
    public function setSubscriptionId(int $subscriptionId): void
    {
        $this->setData('subscription_id', $subscriptionId);
    }

    /**
     * Getter for success
     *
     * @return bool
     */
    public function getSuccess(): bool
    {
        return (bool) $this->getData('success');
    }

    /**
     * Setter for success
     *
     * @param bool $success
     * @return void
     */
    public function setSuccess(bool $success): void
    {
        $this->setData('success', $success);
    }

    /**
     * Getter for created
     *
     * @return string
     */
    public function getCreated(): string
    {
        return (string) $this->getData('created');
    }

    /**
     * Setter for created
     *
     * @param string $created
     * @return void
     */
    public function setCreated(string $created): void
    {
        $this->setData('created', $created);
    }

    /**
     * Getter for response data
     *
     * @return string
     */
    public function getResponseData(): string
    {
        return (string) $this->getData('response_data');
    }

    /**
     * Setter for response data
     *
     * @param string $responseData
     * @return void
     */
    public function setResponseData(string $responseData): void
    {
        $this->setData('response_data', $responseData);
    }

    /**
     * Getter for UUID
     *
     * @return string
     */
    public function getUuid(): string
    {
        return (string) $this->getData('uuid');
    }

    /**
     * Setter for UUID
     *
     * @param string $uuid
     * @return void
     */
    public function setUuid(string $uuid): void
    {
        $this->setData('uuid', $uuid);
    }

    /**
     * Getter for serialised data
     *
     * @return array
     */
    public function getSerializedData(): array
    {
        return $this->getData('serialized_data');
    }

    /**
     * Setter for serialised data
     *
     * @param array $serializedData
     * @return void
     */
    public function setSerializedData(array $serializedData): void
    {
        $this->setData('serialized_data', $serializedData);
    }
}
