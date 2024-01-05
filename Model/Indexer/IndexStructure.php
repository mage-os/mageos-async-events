<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;

class IndexStructure implements IndexStructureInterface
{

    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param ElasticsearchAdapter $adapter
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ElasticSearchAdapter $adapter,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->adapter = $adapter;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @inheritDoc
     */
    public function delete($index, array $dimensions = [])
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($scopeId, $index);
    }

    /**
     * @inheritDoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->checkIndex($scopeId, $index, false);
    }
}
