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
    private $traceCache = [];

    /**
     * @var AsyncEventRepositoryInterface
     */
    private $asyncEventRepository;

    /**
     * @var AsyncEventLogCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        AsyncEventLogCollectionFactory $collectionFactory,
        AsyncEventRepositoryInterface  $asyncEventRepository
    ) {
        $this->asyncEventRepository = $asyncEventRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
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

        } catch (NoSuchEntityException $exception) {
            // Do nothing because an uuid cannot exist without its subscription
        }

        return $this->traceCache[$uuid];
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getLogs(string $uuid): array
    {
        $this->getDetails($uuid);

        return $this->traceCache[$uuid]['traces'];
    }

    /**
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
