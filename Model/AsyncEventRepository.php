<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface;
use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Model\Config as AsyncEventConfig;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent as AsyncEventResource;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory as AsyncEventCollectionFactory;
use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterfaceFactory as SearchResultsFactory;

use DateTime;
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
     * @var AsyncEventFactory
     */
    private $asyncEventFactory;

    /**
     * @var AsyncEventResource
     */
    private $asyncEventResource;

    /**
     * @var AsyncEventConfig
     */
    private $asyncEventConfig;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var AsyncEventCollectionFactory
     */
    private $asyncEventCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

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
        AsyncEventFactory            $asyncEventFactory,
        AsyncEventResource           $asyncEventResource,
        AsyncEventConfig             $asyncEventConfig,
        SearchResultsFactory         $searchResultsFactory,
        AsyncEventCollectionFactory  $asyncEventCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        EncryptorInterface           $encryptor,
        AuthorizationInterface       $authorization
    ) {
        $this->asyncEventFactory = $asyncEventFactory;
        $this->asyncEventResource = $asyncEventResource;
        $this->asyncEventConfig = $asyncEventConfig;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->asyncEventCollectionFactory = $asyncEventCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->encryptor = $encryptor;
        $this->authorization = $authorization;
    }

    /**
     * @inheritDoc
     */
    public function get(int $subscriptionId): AsyncEventDisplayInterface
    {
        $asyncEvent = $this->asyncEventFactory->create();
        $this->asyncEventResource->load($asyncEvent, $subscriptionId);

        if (!$asyncEvent->getId()) {
            throw new NoSuchEntityException(__('Async event with subscription ID %1 does not exist', $subscriptionId));
        }

        return $asyncEvent;
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
            $asyncEvent->setSubscribedAt((new DateTime())->format(DateTime::ATOM));
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
     * @param AsyncEventInterface $asyncEvent
     * @return void
     * @throws AuthorizationException
     */
    private function validateResources(AsyncEventInterface $asyncEvent)
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
}
