<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\Collection;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\CollectionFactory as AsyncEventLogCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Details
{
    /**
     * @var array
     */
    private array $traceCache = [];

    /**
     * @param AsyncEventLogCollectionFactory $collectionFactory
     * @param AsyncEventRepositoryInterface $asyncEventRepository
     */
    public function __construct(
        private readonly AsyncEventLogCollectionFactory $collectionFactory,
        private readonly AsyncEventRepositoryInterface $asyncEventRepository
    ) {
    }

    /**
     * Get details of an asynchronous event by UUID
     *
     * @param string $uuid
     * @return array
     */
    public function getDetails(string $uuid): array
    {
        if (array_key_exists($uuid, $this->traceCache)) {
            return $this->traceCache[$uuid];
        }

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFilter('uuid', $uuid);

        $traces = $collection->toArray();

        $asyncEventId = $collection->getFirstItem()->getData('subscription_id');

        try {
            $asyncEvent = $this->asyncEventRepository->get((int) $asyncEventId)->getData();
            $this->traceCache[$uuid] = [
                'traces' => $traces['items'],
                'async_event' => $asyncEvent
            ];

        } catch (NoSuchEntityException) {
            // Do nothing because an uuid cannot exist without its subscription
            return [];
        }

        return $this->traceCache[$uuid];
    }

    /**
     * Get log traces of an asynchronous event by UUID
     *
     * @param string $uuid
     * @return array
     */
    public function getLogs(string $uuid): array
    {
        $this->getDetails($uuid);

        return $this->traceCache[$uuid]['traces'];
    }

    /**
     * Get the delivery status of an asynchronous event batch
     *
     * @param string $uuid
     * @return string
     */
    public function getStatus(string $uuid): string
    {
        $this->getDetails($uuid);

        $traces = $this->traceCache[$uuid]['traces'];

        $deathCount = 0;
        $success = false;
        foreach ($traces as $trace) {
            $deathCount++;

            if ($trace['success']) {
                $success = true;
                break;
            }
        }

        // Deduct one which is taking the initial delivery into consideration
        $deathCount--;

        if ($success) {
            $message = 'Delivered';
        } else {
            $message = 'Dead Lettered';
        }

        if ($deathCount > 0) {
            $message = $message . ' with ' . $deathCount . ' retries';
        }

        return $message;
    }

    /**
     * Get the first attempt of an asynchronous event dispatch batch
     *
     * @param string $uuid
     * @return string
     */
    public function getFirstAttempt(string $uuid): string
    {
        $this->getDetails($uuid);

        $traces = $this->traceCache[$uuid]['traces'];
        $firstTrace = current($traces);

        return $firstTrace['created'];
    }

    /**
     * Get the last attempt of an asynchronous event dispatch batch
     *
     * @param string $uuid
     * @return string
     */
    public function getLastAttempt(string $uuid): string
    {
        $this->getDetails($uuid);

        $traces = $this->traceCache[$uuid]['traces'];
        $firstTrace = end($traces);

        return $firstTrace['created'];
    }

    /**
     * Get the name of the asynchronous event
     *
     * @param string $uuid
     * @return string
     */
    public function getAsynchronousEventName(string $uuid): string
    {
        $this->getDetails($uuid);

        $event = $this->traceCache[$uuid]['async_event'];

        return $event['event_name'];
    }

    /**
     * Get the current status of the asynchronous event subscriber
     *
     * @param string $uuid
     * @return string
     */
    public function getCurrentStatus(string $uuid): string
    {
        $this->getDetails($uuid);

        $event = $this->traceCache[$uuid]['async_event'];

        return $event['status'] ? 'Enabled' : 'Disabled';
    }

    /**
     * Get the recipient of the asynchronous event subscriber
     *
     * @param string $uuid
     * @return string
     */
    public function getRecipient(string $uuid): string
    {
        $this->getDetails($uuid);

        $event = $this->traceCache[$uuid]['async_event'];

        return $event['recipient_url'];
    }

    /**
     * Get the date of the subscriber created a subscription
     *
     * @param string $uuid
     * @return string
     */
    public function getSubscribedAt(string $uuid): string
    {
        $this->getDetails($uuid);

        $event = $this->traceCache[$uuid]['async_event'];

        return $event['subscribed_at'];
    }
}
