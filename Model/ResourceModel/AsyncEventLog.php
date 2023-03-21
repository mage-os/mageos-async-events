<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AsyncEventLog extends AbstractDb
{
    /**
     * @var array
     */
    protected $_serializableFields = ['serialized_data' => [[],[]]];

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('async_event_subscriber_log', 'log_id');
    }
}
