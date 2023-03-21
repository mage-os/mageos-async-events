<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Helper\NotifierResult;

interface NotifierInterface
{
    /**
     * The notifier method
     *
     * @param AsyncEventInterface $asyncEvent
     * @param array $data
     * @return NotifierResult
     */
    public function notify(AsyncEventInterface $asyncEvent, array $data): NotifierResult;
}
