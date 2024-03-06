<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer\Mview;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\ActionInterface;

class Action implements ActionInterface
{
    /**
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(private readonly IndexerInterfaceFactory $indexerFactory)
    {
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
