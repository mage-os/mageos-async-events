<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_INDEXING_ENABLED = 'system/async_events/indexing_enabled';
    const XML_PATH_CLEANUP_CRON_ENABLED = 'system/async_events/subscriber_log_cleanup_cron';
    const XML_PATH_CLEANUP_CRON_DELETE_PERIOD = 'system/async_events/subscriber_log_cron_delete_period';
    const XML_PATH_MAX_DEATHS = 'system/async_events/max_deaths';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isIndexingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INDEXING_ENABLED
        );
    }

    /**
     * @return bool
     */
    public function isCleanUpCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CLEANUP_CRON_ENABLED
        );
    }

    /**
     * @return int
     */
    public function getCleanUpCronDeletePeriod(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_CLEANUP_CRON_DELETE_PERIOD
        );
    }

    /**
     * @return int
     */
    public function getMaximumDeaths(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_MAX_DEATHS
        );
    }
}
