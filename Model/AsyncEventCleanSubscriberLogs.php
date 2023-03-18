<?php

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Helper\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;

class AsyncEventCleanSubscriberLogs
{
    /**
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly Config $config,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Deletes all logs older than specified days using an SQL command
     *
     * @return void
     */
    public function cleanSubscriberLogs(): void
    {
        // check if cron is enabled or disabled in system configuration
        $shouldRunCron = $this->config->isCleanUpCronEnabled();

        if ($shouldRunCron) {
            $connection = $this->resourceConnection->getConnection();

            // retrieve amount of days for oldest log from system config
            $timePeriodInDays = $this->config->getCleanUpCronDeletePeriod();

            // creates a date that is x amount of days in the past and checks if the log record is older than that
            $now = $this->dateTime->formatDate(time());
            $periodDate = $this->dateTime->formatDate(strtotime("$now -$timePeriodInDays days"));

            // deletes all logs older than period date
            $connection->delete("async_event_subscriber_log", ["created < ?" => $periodDate]);

            $this->resourceConnection->closeConnection();
        }
    }
}
