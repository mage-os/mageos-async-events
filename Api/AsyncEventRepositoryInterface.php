<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api;

use MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface AsyncEventRepositoryInterface
{
    /**
     * Get a single asynchronous event by id
     *
     * @param int $subscriptionId
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface
     * @throws NoSuchEntityException
     */
    public function get(int $subscriptionId): AsyncEventDisplayInterface;

    /**
     * Get a list of asynchronous events by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AsyncEventSearchResultsInterface;

    /**
     * Save an asynchronous event
     *
     * @param \MageOS\AsyncEvents\Api\Data\AsyncEventInterface $asyncEvent
     * @param bool $checkResources
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws AlreadyExistsException
     * @throws AuthorizationException
     */
    public function save(AsyncEventInterface $asyncEvent, bool $checkResources = true): AsyncEventDisplayInterface;
}
