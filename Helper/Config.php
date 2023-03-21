<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_INDEXING_ENABLED = 'system/async_events/indexing_enabled';
    private const XML_PATH_CLEANUP_CRON_ENABLED = 'system/async_events/subscriber_log_cleanup_cron';
    private const XML_PATH_CLEANUP_CRON_DELETE_PERIOD = 'system/async_events/subscriber_log_cron_delete_period';
    private const XML_PATH_MAX_DEATHS = 'system/async_events/max_deaths';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * Get if async event indexing is enabled
     *
     * @return bool
     */
    public function isIndexingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INDEXING_ENABLED
        );
    }

    /**
     * Get if clean up cron is enabled
     *
     * @return bool
     */
    public function isCleanUpCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CLEANUP_CRON_ENABLED
        );
    }

    /**
     * Get clean up cron delete period
     *
     * @return int
     */
    public function getCleanUpCronDeletePeriod(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_CLEANUP_CRON_DELETE_PERIOD
        );
    }

    /**
     * Get maximum death count for async event delivery failures
     *
     * @return int
     */
    public function getMaximumDeaths(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_MAX_DEATHS
        );
    }
}
