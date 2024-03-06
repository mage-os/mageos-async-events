<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use CloudEvents\Serializers\JsonDeserializer;
use CloudEvents\V1\CloudEventImmutable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\SerializerInterface;
use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Helper\Config;
use MageOS\AsyncEvents\Helper\NotifierResult;
use MageOS\AsyncEvents\Service\AsyncEvent\NotifierFactoryInterface;
use MageOS\AsyncEvents\Service\AsyncEvent\RetryManager;

class RetryHandler
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AsyncEventRepositoryInterface $asyncEventRepository
     * @param NotifierFactoryInterface $notifierFactory
     * @param AsyncEventLogFactory $asyncEventLogFactory
     * @param AsyncEventLogRepository $asyncEventLogRepository
     * @param RetryManager $retryManager
     * @param SerializerInterface $serializer
     * @param Config $config
     */
    public function __construct(
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly AsyncEventRepositoryInterface $asyncEventRepository,
        private readonly NotifierFactoryInterface $notifierFactory,
        private readonly AsyncEventLogFactory $asyncEventLogFactory,
        private readonly AsyncEventLogRepository $asyncEventLogRepository,
        private readonly RetryManager $retryManager,
        private readonly SerializerInterface $serializer,
        private readonly Config $config
    ) {
    }

    /**
     * Process a retry message
     *
     * @param array $message
     * @return void
     */
    public function process(array $message): void
    {
        $subscriptionId = $message[RetryManager::SUBSCRIPTION_ID];
        $deathCount = $message[RetryManager::DEATH_COUNT];
        $data = $message[RetryManager::CONTENT];
        $uuid = $message[RetryManager::UUID];

        $subscriptionId = (int)$subscriptionId;
        $deathCount = (int)$deathCount;
        $maxDeaths = $this->config->getMaximumDeaths();

        $event = CloudEventImmutable::createFromInterface(JsonDeserializer::create()->deserializeStructured($data));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 1)
            ->addFilter('subscription_id', $subscriptionId)
            ->create();

        $asyncEvents = $this->asyncEventRepository->getList($searchCriteria)->getItems();

        foreach ($asyncEvents as $asyncEvent) {
            $handler = $asyncEvent->getMetadata();
            $notifier = $this->notifierFactory->create($handler);

            $result = $notifier->notify($asyncEvent, $event);

            $result->setUuid($uuid);
            $this->log($result);

            if (!$result->getIsSuccessful() && $result->getIsRetryable()) {
                if ($deathCount < $maxDeaths) {
                    $this->retryManager->place(
                        ++$deathCount,
                        $subscriptionId,
                        $event,
                        $uuid,
                        $result->getRetryAfter()
                    );
                } else {
                    $this->retryManager->kill($subscriptionId, $event);
                }
            }
        }
    }

    /**
     * Log a retry, this is what allows us to find a trace of an asynchronous event dispatch
     *
     * @param NotifierResult $response
     * @return void
     */
    private function log(NotifierResult $response): void
    {
        /** @var AsyncEventLog $asyncEventLog */
        $asyncEventLog = $this->asyncEventLogFactory->create();
        $asyncEventLog->setSuccess($response->getIsSuccessful());
        $asyncEventLog->setSubscriptionId($response->getSubscriptionId());
        $asyncEventLog->setResponseData($response->getResponseData());
        $asyncEventLog->setUuid($response->getUuid());
        $asyncEventLog->setSerializedData($response->getAsyncEventData());

        try {
            $this->asyncEventLogRepository->save($asyncEventLog);
        } catch (AlreadyExistsException) {
            return;
        }
    }
}
