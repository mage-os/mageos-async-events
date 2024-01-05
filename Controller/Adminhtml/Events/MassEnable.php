<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Controller\Adminhtml\Events;

use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Model\AsyncEvent;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\Collection;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassEnable extends Action implements HttpPostActionInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var AsyncEventRepositoryInterface
     */
    private $asyncEventRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param AsyncEventRepositoryInterface $asyncEventRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        AsyncEventRepositoryInterface $asyncEventRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->asyncEventRepository = $asyncEventRepository;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('async_events/events/index');

        $asyncEventCollection = $this->collectionFactory->create();
        $this->filter->getCollection($asyncEventCollection);
        $this->enableAsyncEvents($asyncEventCollection);

        return $resultRedirect;
    }

    /**
     * @param Collection $asyncEventCollection
     * @return void
     */
    private function enableAsyncEvents(Collection $asyncEventCollection)
    {
        $enabled = 0;
        $alreadyEnabled = 0;

        /** @var AsyncEvent $asyncEvent */
        foreach ($asyncEventCollection as $asyncEvent) {
            $alreadyEnabled++;
            if (!$asyncEvent->getStatus()) {
                try {
                    $asyncEvent->setStatus(true);
                    $this->asyncEventRepository->save($asyncEvent, false);
                    $alreadyEnabled--;
                    $enabled++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        if ($enabled) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 event(s) have been enabled.', $enabled)
            );
        }

        if ($alreadyEnabled) {
            $this->messageManager->addNoticeMessage(
                __('A total of %1 event(s) are already enabled.', $alreadyEnabled)
            );
        }
    }
}
