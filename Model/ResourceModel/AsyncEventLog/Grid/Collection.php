<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Zend_Db_Expr;

class Collection extends SearchResult
{
    /**
     * @inheritDoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->join(
                [
                    'aes' => $this->getTable('async_event_subscriber')
                ],
                'main_table.subscription_id = aes.subscription_id',
                'event_name'
            )->columns(
                [
                    'response_data' => new Zend_Db_Expr('SUBSTRING(response_data, 1, 100)')
                ]
            );

        return $this;
    }
}
