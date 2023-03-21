<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use Exception;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Search;

class LuceneSearch extends Search
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param ConnectionManager $connectionManager
     * @param Config $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        private readonly ConnectionManager $connectionManager,
        private readonly Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $components, $data);
    }

    /**
     * Prepare the query
     *
     * This function just simply opens a connection to Elasticsearch and delegates the lucene compatible search
     * string to Elasticsearch. If an exception occurs fallback to the default database fulltext search
     * on the log_id column.
     *
     * @return void
     */
    public function prepare(): void
    {
        $client = $this->connectionManager->getConnection();
        $value = $this->getContext()->getRequestParam('search');
        $indexPrefix = $this->config->getIndexPrefix();
        $filter = $this->filterBuilder->setConditionType('in')
            ->setField($this->getName());

        if ($value === "") {
            return;
        }

        try {
            $rawResponse = $client->query(
                [
                    'index' => $indexPrefix . '_async_event_*',
                    'q' => $value,
                    // the default page size is 10. The highest limit is 10000. If we want to traverse further, we will
                    // have to use the search after parameter. There are no plans to implement this right now.
                    'size' => 100
                ]
            );

            $rawDocuments = $rawResponse['hits']['hits'] ?? [];
            $asyncEventIds = array_column($rawDocuments, '_id');

            if (!empty($asyncEventIds)) {
                $filter->setValue($asyncEventIds);
            } else {
                $filter->setValue("0");
            }
        } catch (Exception) {
            // If we're unable to connect to Elasticsearch, we'll return nothing
            $filter->setValue("0");
        }

        $this->getContext()->getDataProvider()->addFilter($filter->create());
    }
}
