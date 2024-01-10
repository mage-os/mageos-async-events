<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use Exception;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Search;
use Magento\Elasticsearch\Model\Config;

class LuceneSearch extends Search
{

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param ConnectionManager $connectionManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        ConnectionManager $connectionManager,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $components, $data);
        $this->connectionManager = $connectionManager;
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $client = $this->connectionManager->getConnection();
        $value = $this->getContext()->getRequestParam('search');
        $indexPrefix = $this->config->getIndexPrefix();

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
                $filter = $this->filterBuilder->setConditionType('in')
                    ->setField($this->getName())
                    ->setValue($asyncEventIds)
                    ->create();

                $this->getContext()->getDataProvider()->addFilter($filter);
            }
        } catch (Exception $exception) {
            // Fallback to default filter search
            parent::prepare();
        }
    }
}
