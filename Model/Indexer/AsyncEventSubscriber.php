<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use MageOS\AsyncEvents\Helper\Config;
use MageOS\AsyncEvents\Model\Indexer\DataProvider\AsyncEventSubscriberLogs;
use MageOS\AsyncEvents\Model\Resolver\AsyncEvent;
use ArrayIterator;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Traversable;

class AsyncEventSubscriber implements
    IndexerActionInterface,
    MviewActionInterface,
    DimensionalIndexerInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'asynchronous_event_subscriber_log';

    /**
     * Default batch size
     */
    const BATCH_SIZE = 100;

    /**
     * Deployment config path
     *
     * @var string
     */
    const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var AsyncEventSubscriberLogs
     */
    private $asyncEventSubscriberLogsDataProvider;

    /**
     * @var AsyncEvent
     */
    private $asyncEventScopeResolver;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array index structure
     */
    protected $data;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var DeploymentConfig|null
     */
    private $deploymentConfig;

    /**
     * @param DimensionProviderInterface $dimensionProvider
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param AsyncEventSubscriberLogs $asyncEventSubscriberLogsDataProvider
     * @param AsyncEvent $asyncEventScopeResolver
     * @param IndexStructureInterface $indexStructure
     * @param Config $config
     * @param array $data
     * @param int|null $batchSize
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        DimensionProviderInterface $dimensionProvider,
        IndexerHandlerFactory $indexerHandlerFactory,
        AsyncEventSubscriberLogs $asyncEventSubscriberLogsDataProvider,
        AsyncEvent $asyncEventScopeResolver,
        IndexStructureInterface $indexStructure,
        Config $config,
        array $data,
        int $batchSize = null,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->dimensionProvider = $dimensionProvider;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->asyncEventSubscriberLogsDataProvider = $asyncEventSubscriberLogsDataProvider;
        $this->config = $config;
        $this->data = $data;
        $this->batchSize = $batchSize ?? self::BATCH_SIZE;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->asyncEventScopeResolver = $asyncEventScopeResolver;
        $this->indexStructure = $indexStructure;
    }

    /**
     * Full indexing can be implemented if required
     *
     * @inheritDoc
     */
    public function executeFull()
    {
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, null);
        }
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, new ArrayIterator($ids));
        }
    }

    /**
     * @inheritDoc
     */
    public function executeByDimensions(array $dimensions, Traversable $entityIds = null)
    {
        if (!$this->config->isIndexingEnabled()) {
            return;
        }

        $saveHandler = $this->indexerHandlerFactory->create(
            [
                'data' => $this->data,
                'scopeResolver' => $this->asyncEventScopeResolver,
                'indexStructure' => $this->indexStructure
            ]
        );

        if ($entityIds === null) {
            $asyncEventDimension = $dimensions[AsyncEventDimensionProvider::DIMENSION_NAME]->getValue();
            $saveHandler->cleanIndex($dimensions);
            $saveHandler->saveIndex(
                $dimensions,
                $this->asyncEventSubscriberLogsDataProvider->getAsyncEventLogs($asyncEventDimension, null)
            );
        } else {
            $asyncEventIds = iterator_to_array($entityIds);

            $this->batchSize = $this->deploymentConfig->get(
                self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . self::INDEXER_ID . '/partial_reindex'
            ) ?? $this->batchSize;

            $asyncEventBatches = array_chunk($asyncEventIds, $this->batchSize);

            foreach ($asyncEventBatches as $asyncEventBatch) {
                $this->processBatch(
                    $saveHandler,
                    $dimensions,
                    $asyncEventBatch
                );
            }
        }
    }

    /**
     * @param IndexerInterface $saveHandler
     * @param array $dimensions
     * @param array $asyncEventLogIds
     * @return void
     */
    private function processBatch(
        IndexerInterface $saveHandler,
        array $dimensions,
        array $asyncEventLogIds
    ) {
        $asyncEvent = $dimensions[AsyncEventDimensionProvider::DIMENSION_NAME]->getValue();

        if ($saveHandler->isAvailable($dimensions)) {
            $saveHandler->deleteIndex($dimensions, new ArrayIterator($asyncEventLogIds));
            $saveHandler->saveIndex(
                $dimensions,
                $this->asyncEventSubscriberLogsDataProvider->getAsyncEventLogs(
                    $asyncEvent,
                    $asyncEventLogIds
                )
            );
        }
    }
}
