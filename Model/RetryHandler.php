<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface;
use MageOS\AsyncEvents\Helper\Config;
use MageOS\AsyncEvents\Helper\NotifierResult;
use MageOS\AsyncEvents\Service\AsyncEvent\NotifierFactoryInterface;
use MageOS\AsyncEvents\Service\AsyncEvent\RetryManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\SerializerInterface;

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
        private readonly SearchCriteriaBuilder         $searchCriteriaBuilder,
        private readonly AsyncEventRepositoryInterface $asyncEventRepository,
        private readonly NotifierFactoryInterface      $notifierFactory,
        private readonly AsyncEventLogFactory          $asyncEventLogFactory,
        private readonly AsyncEventLogRepository       $asyncEventLogRepository,
        private readonly RetryManager                  $retryManager,
        private readonly SerializerInterface           $serializer,
        private readonly Config                        $config
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

        $subscriptionId = (int) $subscriptionId;
        $deathCount = (int) $deathCount;
        $maxDeaths = $this->config->getMaximumDeaths();

        $data = $this->serializer->unserialize($data);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 1)
            ->addFilter('subscription_id', $subscriptionId)
            ->create();

        $asyncEvents = $this->asyncEventRepository->getList($searchCriteria)->getItems();

        foreach ($asyncEvents as $asyncEvent) {
            $handler = $asyncEvent->getMetadata();
            $notifier = $this->notifierFactory->create($handler);
            $response = $notifier->notify($asyncEvent, [
                'data' => $data
            ]);
            $response->setUuid($uuid);
            $this->log($response);

            if (!$response->getSuccess()) {
                if ($deathCount < $maxDeaths) {
                    $this->retryManager->place($deathCount + 1, $subscriptionId, $data, $uuid);
                } else {
                    $this->retryManager->kill($subscriptionId, $data);
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
        $asyncEventLog->setSuccess($response->getSuccess());
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
