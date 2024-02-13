<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\ResultInterface;

interface NotifierInterface
{
    /**
     * The notifier method
     *
     * @param AsyncEventInterface $asyncEvent
     * @param array $data
     * @return ResultInterface
     */
    public function notify(AsyncEventInterface $asyncEvent, array $data): ResultInterface;
}
