<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer\Mview;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\ActionInterface;

class Action implements ActionInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(IndexerInterfaceFactory $indexerFactory)
    {
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        $indexer = $this->indexerFactory->create()->load('async_event');
        $indexer->reindexList($ids);
    }
}
