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

    public function getLogId()
    {
        $this->getData('log_id');
    }

    public function setLogId($logId)
    {
        $this->setData('log_id', $logId);
        return $this;
    }

    public function getSubscriptionId()
    {
        $this->getData('subscription_id');
    }

    public function setSubscriptionId($subscriptionId)
    {
        $this->setData('subscription_id', $subscriptionId);
        return $this;
    }

    public function getSuccess()
    {
        $this->getData('success');
    }

    public function setSuccess($success)
    {
        $this->setData('success', $success);
        return $this;
    }

    public function getCreated()
    {
        $this->getData('created');
    }

    public function setCreated($created)
    {
        $this->setData('created', $created);
        return $this;
    }

    public function getResponseData()
    {
        $this->getData('response_data');
    }

    public function setResponseData($responseData)
    {
        $this->setData('response_data', $responseData);
        return $this;
    }

    public function getUuid(): string
    {
        return (string) $this->getData('uuid');
    }

    public function setUuid(string $uuid)
    {
        $this->setData('uuid', $uuid);
    }

    public function getSerializedData(): array
    {
        return $this->getData('serialized_data');
    }

    public function setSerializedData(array $serializedData)
    {
        $this->setData('serialized_data', $serializedData);
    }
}
