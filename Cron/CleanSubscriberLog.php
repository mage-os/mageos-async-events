<?php

namespace MageOS\AsyncEvents\Cron;

use MageOS\AsyncEvents\Model\AsyncEventCleanSubscriberLogs;
use Psr\Log\LoggerInterface;

class CleanSubscriberLog
{
    private $logger;
    private $cleanSubscriberLogs;

    /**
     * @param LoggerInterface $logger
     * @param AsyncEventCleanSubscriberLogs $cleanSubscriberLogs
     */
    public function __construct(LoggerInterface $logger, AsyncEventCleanSubscriberLogs $cleanSubscriberLogs)
    {
        $this->logger = $logger;
        $this->cleanSubscriberLogs = $cleanSubscriberLogs;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->cleanSubscriberLogs->cleanSubscriberLogs();
        } catch (\Exception $e) {
            $this->logger->error("Could not clean subscriber logs", ["Exception" => $e]);
        }
    }
}
