<?php

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Helper\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;

class AsyncEventCleanSubscriberLogs
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param Config $config
     */
    public function __construct(
        ResourceConnection   $resourceConnection,
        DateTime             $dateTime,
        Config $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    /**
     * Deletes all logs older than specified days using an SQL command
     *
     * @return void
     */
    public function cleanSubscriberLogs()
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
