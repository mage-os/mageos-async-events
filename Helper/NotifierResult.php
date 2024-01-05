<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

use Magento\Framework\DataObject;

class NotifierResult extends DataObject
{
    const SUCCESS = 'success';

    /**
     * Refers back to the actual event entity id that is associated,
     * not the subscription name id like customer_created, customer_updated
     * and so on
     */
    const SUBSCRIPTION_ID = 'subscription_id';

    /**
     * Ideally this is a json encoded string
     */
    const RESPONSE_DATA = 'response_data';

    /**
     * @var string
     */
    const UUID = 'uuid';

    /**
     * @var array
     */
    const DATA = 'data';

    public function getSuccess()
    {
        return $this->getData(self::SUCCESS);
    }

    public function setSuccess($success)
    {
        $this->setData(self::SUCCESS, $success);
    }

    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA);
    }

    public function setResponseData($responseData)
    {
        $this->setData(self::RESPONSE_DATA, $responseData);
    }

    public function getUuid(): string
    {
        return $this->getData(self::UUID);
    }

    public function setUuid(string $uuid)
    {
        $this->setData(self::UUID, $uuid);
    }

    /**
     * @param array $eventData
     * @return void
     */
    public function setAsyncEventData(array $eventData)
    {
        $this->setData(self::DATA, $eventData);
    }

    /**
     * @return array
     */
    public function getAsyncEventData(): array
    {
        return $this->getData(self::DATA);
    }
}
