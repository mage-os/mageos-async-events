<?php

namespace MageOS\AsyncEvents\Cron;

use MageOS\AsyncEvents\Model\AsyncEventCleanSubscriberLogs;
use Exception;
use Psr\Log\LoggerInterface;

class CleanSubscriberLog
{
    /**
     * @param LoggerInterface $logger
     * @param AsyncEventCleanSubscriberLogs $cleanSubscriberLogs
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AsyncEventCleanSubscriberLogs $cleanSubscriberLogs
    ) {
    }

    /**
     * Execute page load
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->cleanSubscriberLogs->cleanSubscriberLogs();
        } catch (Exception $e) {
            $this->logger->error("Could not clean subscriber logs", ["Exception" => $e]);
        }
    }
}
