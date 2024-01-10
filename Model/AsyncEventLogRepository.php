<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog as AsyncEventLogResource;
use Magento\Framework\Exception\AlreadyExistsException;

class AsyncEventLogRepository
{
    /**
     * @var AsyncEventLogResource
     */
    private $asyncEventLogResource;

    /**
     * @param AsyncEventLogResource $asyncEventLog
     */
    public function __construct(
        AsyncEventLogResource $asyncEventLog
    ) {
        $this->asyncEventLogResource = $asyncEventLog;
    }

    /**
     * @param AsyncEventLog $asyncEvent
     * @throws AlreadyExistsException
     */
    public function save(AsyncEventLog $asyncEvent)
    {
        $this->asyncEventLogResource->save($asyncEvent);
    }
}
