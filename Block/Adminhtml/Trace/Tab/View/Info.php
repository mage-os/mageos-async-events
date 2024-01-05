<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Tab\View;

use MageOS\AsyncEvents\Model\Details;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Info extends Template
{
    /**
     * @var Details
     */
    private $details;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @param Context $context
     * @param Details $details
     * @param array $data
     */
    public function __construct(
        Context $context,
        Details $details,
        array $data = []
    ) {
        $this->details = $details;
        parent::__construct($context, $data);
        $this->uuid = $this->getRequest()->getParam('uuid');
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return array
     */
    public function getLogs(): array
    {
        return $this->details->getLogs($this->uuid);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->details->getStatus($this->uuid);
    }

    /**
     * @return string
     */
    public function getFirstAttempt(): string
    {
        return $this->details->getFirstAttempt($this->uuid);
    }

    /**
     * @return string
     */
    public function getLastAttempt(): string
    {
        return $this->details->getLastAttempt($this->uuid);
    }

    /**
     * @return string
     */
    public function getAsynchronousEventName(): string
    {
        return $this->details->getAsynchronousEventName($this->uuid);
    }

    /**
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->details->getCurrentStatus($this->uuid);
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->details->getRecipient($this->uuid);
    }

    /**
     * @return string
     */
    public function getSubscribedAt(): string
    {
        return $this->details->getSubscribedAt($this->uuid);
    }
}
