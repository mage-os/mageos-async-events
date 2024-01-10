<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Plugin;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

class AddEntityTypeContext
{
    /**
     * The reason this plugin exists is that the callee of map does not provide a nice way to provide a context value
     * AND the default is hardcoded as 'product'
     *
     * This could be the only callee
     * @see vendor/magento/module-elasticsearch/Model/Adapter/Elasticsearch.php:191
     *
     * The below always resolves to product (\Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper)
     * if an entityType context is not set.
     * @see vendor/magento/module-elasticsearch/Model/Adapter/BatchDataMapper/DataMapperResolver.php:41
     *
     * @param BatchDataMapperInterface $subject
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function beforeMap(
        BatchDataMapperInterface $subject,
        array $documentData,
        $storeId,
        array $context = []
    ): array {
        if (!is_numeric($storeId) && is_string($storeId)) {
            $context['entityType'] = 'async_event';
        }

        return [$documentData, $storeId, $context];
    }
}
