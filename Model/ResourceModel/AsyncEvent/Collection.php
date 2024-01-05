<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent;

use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent as AsyncEventResource;
use MageOS\AsyncEvents\Model\AsyncEvent;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'subscription_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            AsyncEvent::class,
            AsyncEventResource::class
        );
    }
}
