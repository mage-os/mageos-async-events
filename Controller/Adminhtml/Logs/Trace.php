<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Controller\Adminhtml\Logs;

use MageOS\AsyncEvents\Model\AsyncEventLogFactory;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Trace extends Action implements HttpGetActionInterface
{
    const MENU_ID = 'MageOS_AsyncEvents::logs';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AsyncEventLog
     */
    private $asyncEventLogResource;

    /**
     * @var AsyncEventLogFactory
     */
    private $asyncEventLogFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AsyncEventLog $asyncEventLogResource
     * @param AsyncEventLogFactory $asyncEventLogFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AsyncEventLog $asyncEventLogResource,
        AsyncEventLogFactory $asyncEventLogFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->asyncEventLogResource = $asyncEventLogResource;
        $this->asyncEventLogFactory = $asyncEventLogFactory;
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Trace'));

        $uuid = $this->getRequest()->getParam('uuid');
        $asyncEventLog = $this->asyncEventLogFactory->create();
        $this->asyncEventLogResource->load($asyncEventLog, $uuid, 'uuid');

        if (!$asyncEventLog->getId()) {
            $this->messageManager->addErrorMessage(__('This asynchronous event trace no longer exists.'));
            $this->_redirect('*/*');
        }

        return $resultPage;
    }
}
