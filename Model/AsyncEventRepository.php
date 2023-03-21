<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterfaceFactory as SearchResultsFactory;
use MageOS\AsyncEvents\Model\Config as AsyncEventConfig;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent as AsyncEventResource;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory as AsyncEventCollectionFactory;
use DateTime;
use DateTimeInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AsyncEventRepository implements AsyncEventRepositoryInterface
{
    /**
     * @param AsyncEventFactory $asyncEventFactory
     * @param AsyncEventResource $asyncEventResource
     * @param AsyncEventConfig $asyncEventConfig
     * @param SearchResultsFactory $searchResultsFactory
     * @param AsyncEventCollectionFactory $asyncEventCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EncryptorInterface $encryptor
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        private readonly AsyncEventFactory $asyncEventFactory,
        private readonly AsyncEventResource $asyncEventResource,
        private readonly AsyncEventConfig $asyncEventConfig,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly AsyncEventCollectionFactory $asyncEventCollectionFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly EncryptorInterface $encryptor,
        private readonly AuthorizationInterface $authorization
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AsyncEventSearchResultsInterface
    {
        $collection = $this->asyncEventCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $asyncEvents = [];

        /** @var AsyncEvent $asyncEvent */
        foreach ($collection as $asyncEvent) {
            $asyncEvents[] = $asyncEvent;
        }

        return $this->searchResultsFactory->create()
            ->setItems($asyncEvents)
            ->setTotalCount($collection->getSize())
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function save(AsyncEventInterface $asyncEvent, bool $checkResources = true): AsyncEventDisplayInterface
    {
        if ($checkResources) {
            $this->validateResources($asyncEvent);
        }

        if (!$asyncEvent->getSubscriptionId()) {
            $asyncEvent->setStatus(true);
            $asyncEvent->setSubscribedAt((new DateTime())->format(DateTimeInterface::ATOM));
            $secretVerificationToken = $this->encryptor->encrypt($asyncEvent->getVerificationToken());
            $asyncEvent->setVerificationToken($secretVerificationToken);

        } else {
            if ($asyncEvent->getStatus() === null) {
                throw new LocalizedException(__("Status is required"));
            }

            $newStatus = $asyncEvent->getStatus();
            $newMetadata = $asyncEvent->getMetadata();

            $asyncEvent = $this->get($asyncEvent->getSubscriptionId());
            $asyncEvent->setStatus($newStatus);

            if ($newMetadata) {
                $asyncEvent->setMetadata($newMetadata);
            }
        }

        $this->asyncEventResource->save($asyncEvent);

        return $asyncEvent;
    }

    /**
     * Validate ACL resource permissions
     *
     * Check that the current user has all the permissions listed as required in the config definition in order
     * to create an asynchronous event subscription
     *
     * @param AsyncEventInterface $asyncEvent
     * @return void
     * @throws AuthorizationException
     */
    private function validateResources(AsyncEventInterface $asyncEvent): void
    {
        $configData = $this->asyncEventConfig->get($asyncEvent->getEventName());
        $resources = $configData['resources'] ?? [];
        foreach ($resources as $resource) {
            if (!$this->authorization->isAllowed($resource)) {
                throw new AuthorizationException(
                    __(
                        "The consumer isn't authorized to access %resources.",
                        ['resources' => $resources]
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function get(int $subscriptionId): AsyncEventDisplayInterface
    {
        $asyncEvent = $this->asyncEventFactory->create();
        $this->asyncEventResource->load($asyncEvent, $subscriptionId);

        if (!$asyncEvent->getId()) {
            throw new NoSuchEntityException(
                __('Async event with subscription ID %1 does not exist', $subscriptionId)
            );
        }

        return $asyncEvent;
    }
}
