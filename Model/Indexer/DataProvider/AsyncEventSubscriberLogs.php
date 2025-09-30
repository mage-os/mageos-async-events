<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer\DataProvider;

use Generator;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageOS\AsyncEvents\Model\Indexer\AsyncEventSubscriber;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\Collection;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\CollectionFactory as AsyncEventLogCollectionFactory;
use Zend_Db_Expr;

class AsyncEventSubscriberLogs
{
    private AdapterInterface $connection;

    private const DEPLOYMENT_CONFIG_INDEXER_BATCHES_PREFIX = 'indexer/batch_size/';

    /**
     * @param AsyncEventLogCollectionFactory $collectionFactory
     * @param ResourceConnection $resource
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        private readonly AsyncEventLogCollectionFactory $collectionFactory,
        private readonly ResourceConnection $resource,
        private readonly DeploymentConfig $deploymentConfig
    ) {
        $this->connection = $resource->getConnection();
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

    public function rebuildIndex(string $asyncEvent): Generator
    {
        $tableName = $this->resource->getTableName('async_event_subscriber_log');

        $batchSize = $this->deploymentConfig->get(
            self::DEPLOYMENT_CONFIG_INDEXER_BATCHES_PREFIX . AsyncEventSubscriber::INDEXER_ID . '/mysql_get'
        ) ?? 10_000;

        $lastId = 0;

        while (true) {
            $select = $this->connection->select()
                ->from(['main_table' => $tableName])
                ->join(
                    ['ae' => 'async_event_subscriber'],
                    'ae.subscription_id = main_table.subscription_id',
                    ['event_name']
                )
                ->where('ae.event_name = ?', $asyncEvent)
                ->where('main_table.log_id > ?', $lastId)
                ->order('main_table.log_id ASC')
                ->limit($batchSize);

            $result = $this->connection->fetchAll($select);

            if (empty($result)) {
                break;
            }

            foreach ($result as $row) {
                yield $row['log_id'] => $row;
            }

            $lastId = end($result)['log_id'];
        }
    }
}
