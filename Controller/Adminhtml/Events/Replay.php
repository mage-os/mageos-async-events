<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Controller\Adminhtml\Events;

use MageOS\AsyncEvents\Service\AsyncEvent\RetryManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;

class Replay extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageOS_AsyncEvents::async_events_logs_replay';

    /**
     * Retry Constructor
     *
     * @param Context $context
     * @param RetryManager $retryManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        private readonly RetryManager $retryManager,
        private readonly SerializerInterface $serializer
    ) {
        parent::__construct($context);
    }

    /**
     * Execute page load
     *
     * @return void
     */
    public function execute(): void
    {
        $data = $this->getRequest()->getPostValue()['general'];

        $this->retryManager->init(
            (int) $data['subscription_id'],
            $this->serializer->unserialize($data['serialized_data'])['data'],
            $data['uuid']
        );

        $this->_redirect('*/logs/trace/uuid/' . $data['uuid'], ['_current' => true]);
    }
}
