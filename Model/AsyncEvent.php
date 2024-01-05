<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class AsyncEvent extends AbstractExtensibleModel implements AsyncEventInterface, AsyncEventDisplayInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'async_event_subscriber';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AsyncEvent::class);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getData('subscription_id');
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionId(int $id)
    {
        $this->setData('subscription_id', $id);
    }

    /**
     * @inheritDoc
     */
    public function getEventName(): string
    {
        return (string) $this->getData('event_name');
    }

    /**
     * @inheritDoc
     */
    public function setEventName(string $eventName)
    {
        $this->setData('event_name', $eventName);
    }

    /**
     * @inheritDoc
     */
    public function getRecipientUrl(): string
    {
        return (string) $this->getData('recipient_url');
    }

    /**
     * @inheritDoc
     */
    public function setRecipientUrl(string $recipientURL)
    {
        $this->setData('recipient_url', $recipientURL);
    }

    /**
     * @inheritDoc
     */
    public function getVerificationToken(): string
    {
        return (string) $this->getData('verification_token');
    }

    /**
     * @inheritDoc
     */
    public function setVerificationToken(string $verificationToken)
    {
        $this->setData('verification_token', $verificationToken);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): bool
    {
        return (bool) $this->getData('status');
    }

    /**
     * @inheritDoc
     */
    public function setStatus(bool $status)
    {
        $this->setData('status', $status);
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedAt(): string
    {
        return (string) $this->getData('subscribed_at');
    }

    /**
     * @inheritDoc
     */
    public function setSubscribedAt(string $subscribedAt)
    {
        $this->setData('subscribed_at', $subscribedAt);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): string
    {
        return (string) $this->getData('metadata');
    }

    /**
     * @inheritDoc
     */
    public function setMetadata(string $metadata)
    {
        $this->setData('metadata', $metadata);
    }
}
