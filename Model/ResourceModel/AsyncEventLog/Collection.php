<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog;

use MageOS\AsyncEvents\Model\AsyncEventLog;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog as AsyncEventLogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            AsyncEventLog::class,
            AsyncEventLogResource::class
        );
    }
}
