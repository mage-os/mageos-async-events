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

class Retry extends Action implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RetryManager
     */
    private $retryManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RetryManager $retryManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RetryManager $retryManager,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->retryManager = $retryManager;
        $this->serializer = $serializer;
    }

    public function execute()
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
