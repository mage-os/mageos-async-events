<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Plugin;

use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;

class MapStringScopeToInt
{
    /**
     * Remap string to int
     *
     * From Magento version 2.4.5, a new synonym filter is introduced. This is strictly typed to be an int and will
     * fail with a PHP Type error since this module's scope is a string (the async event name). Since we do not care
     * about stemming and synonyms as we are literally just using it for the Lucene Query Syntax we can safely ignore
     * this and remap and provide a fake store view id of 0.
     *
     * @param BuilderInterface $subject
     * @param int|string $storeId
     * @return array
     */
    public function beforeSetStoreId(BuilderInterface $subject, $storeId): array
    {
        if (!is_numeric($storeId) && is_string($storeId)) {
            $storeId = 0;
        }

        return [$storeId];
    }
}
