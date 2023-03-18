<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    private const MENU_ID = 'MageOS_AsyncEvents::logs';

    public const ADMIN_RESOURCE = 'MageOS_AsyncEvents::async_events_logs_list';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Execute page load
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Asynchronous Event Logs'));

        return $resultPage;
    }
}
