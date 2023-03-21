<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer\DataProvider;

use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\Collection;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\CollectionFactory as AsyncEventLogCollectionFactory;

class AsyncEventSubscriberLogs
{
    /**
     * @param AsyncEventLogCollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly AsyncEventLogCollectionFactory $collectionFactory
    ) {
    }

    /**
     * Get async event logs by log ids
     *
     * @param string $asyncEvent
     * @param array|null $logIds
     * @return Collection
     */
    public function getAsyncEventLogs(string $asyncEvent, ?array $logIds): Collection
    {
        $logCollection = $this->collectionFactory->create();

        $logCollection->getSelect()
            ->join(
                ['ae' => 'async_event_subscriber'],
                'ae.subscription_id = main_table.subscription_id',
                ['event_name']
            )
            ->where('ae.event_name = ?', $asyncEvent);

        if ($logIds !== null) {
            $logCollection->addFieldToFilter('log_id', ['in' => $logIds]);
        }

        return $logCollection;
    }
}
