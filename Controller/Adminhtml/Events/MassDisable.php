<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Controller\Adminhtml\Events;

use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Model\AsyncEvent;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\Collection;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassDisable extends Action implements HttpPostActionInterface
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
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->asyncEventRepository = $asyncEventRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('async_events/events/index');

        $asyncEventCollection = $this->collectionFactory->create();
        $this->filter->getCollection($asyncEventCollection);
        $this->disableAsyncEvents($asyncEventCollection);

        return $resultRedirect;
    }

    /**
     * @param Collection $asyncEventCollection
     * @return void
     */
    private function disableAsyncEvents(Collection $asyncEventCollection)
    {
        $disabled = 0;
        $alreadyDisabled = 0;

        /** @var AsyncEvent $asyncEvent */
        foreach ($asyncEventCollection as $asyncEvent) {
            $alreadyDisabled++;
            if ($asyncEvent->getStatus()) {
                try {
                    $asyncEvent->setStatus(false);
                    $this->asyncEventRepository->save($asyncEvent, false);
                    $alreadyDisabled--;
                    $disabled++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        if ($disabled) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 event(s) have been disabled.', $disabled)
            );
        }

        if ($alreadyDisabled) {
            $this->messageManager->addNoticeMessage(
                __('A total of %1 event(s) are already disabled.', $alreadyDisabled)
            );
        }
    }
}
