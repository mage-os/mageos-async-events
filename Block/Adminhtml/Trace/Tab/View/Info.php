<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Tab\View;

use MageOS\AsyncEvents\Model\Details;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Info extends Template
{
    /**
     * @var string
     */
    private string $uuid;

    /**
     * @param Context $context
     * @param Details $details
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Details $details,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->uuid = $this->getRequest()->getParam('uuid', '');
    }

    /**
     * Getter for uuid
     *
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Getter for logs
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->details->getLogs($this->uuid);
    }

    /**
     * Getter for status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->details->getStatus($this->uuid);
    }

    /**
     * Getter for first attempt
     *
     * @return string
     */
    public function getFirstAttempt(): string
    {
        return $this->details->getFirstAttempt($this->uuid);
    }

    /**
     * Getter for last attempt
     *
     * @return string
     */
    public function getLastAttempt(): string
    {
        return $this->details->getLastAttempt($this->uuid);
    }

    /**
     * Getter for asynchronous event name
     *
     * @return string
     */
    public function getAsynchronousEventName(): string
    {
        return $this->details->getAsynchronousEventName($this->uuid);
    }

    /**
     * Getter for current status
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->details->getCurrentStatus($this->uuid);
    }

    /**
     * Getter for recipient
     *
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->details->getRecipient($this->uuid);
    }

    /**
     * Getter for subscribed at
     *
     * @return string
     */
    public function getSubscribedAt(): string
    {
        return $this->details->getSubscribedAt($this->uuid);
    }
}
