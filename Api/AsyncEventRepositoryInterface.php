<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api;

use MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface AsyncEventRepositoryInterface
{
    /**
     * @param int $subscriptionId
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $subscriptionId): AsyncEventDisplayInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AsyncEventSearchResultsInterface;

    /**
     * @param \MageOS\AsyncEvents\Api\Data\AsyncEventInterface $asyncEvent
     * @param bool $checkResources
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\AuthorizationException
     */
    public function save(AsyncEventInterface $asyncEvent, bool $checkResources = true): AsyncEventDisplayInterface;
}
