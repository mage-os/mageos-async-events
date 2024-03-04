<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Api\RetryManagementInterface;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use Magento\Framework\Amqp\ConfigPool;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingFactory;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemFactory;
use Magento\Framework\Serialize\SerializerInterface;

class RetryManager implements RetryManagementInterface
{
    public const DEATH_COUNT = 'death_count';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const CONTENT = 'content';
    public const UUID = 'uuid';

    /**
     * @param ConfigPool $configPool
     * @param QueueInstaller $queueInstaller
     * @param BindingInstallerInterface $bindingInstaller
     * @param AmqpPublisher $publisher
     * @param QueueConfigItemFactory $queueConfigItemFactory
     * @param BindingFactory $bindingFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly ConfigPool $configPool,
        private readonly QueueInstaller $queueInstaller,
        private readonly BindingInstallerInterface $bindingInstaller,
        private readonly AmqpPublisher $publisher,
        private readonly QueueConfigItemFactory $queueConfigItemFactory,
        private readonly BindingFactory $bindingFactory,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Start the chain for retrying an asynchronous event that has failed
     *
     * @param int $subscriptionId
     * @param mixed $data
     * @param string $uuid
     * @return void
     */
    public function init(int $subscriptionId, mixed $data, string $uuid): void
    {
        $this->assertDelayQueue(
            1,
            QueueMetadataInterface::RETRY_INIT_ROUTING_KEY,
            QueueMetadataInterface::RETRY_INIT_ROUTING_KEY
        );

        $this->publisher->publish(QueueMetadataInterface::RETRY_INIT_ROUTING_KEY, [
            self::SUBSCRIPTION_ID => $subscriptionId,
            self::DEATH_COUNT => 1,
            self::CONTENT => $this->serializer->serialize($data),
            self::UUID => $uuid
        ]);
    }

    /**
     * Place an asynchronous event to be retried for the nth time
     *
     * @param int $deathCount
     * @param int $subscriptionId
     * @param mixed $data
     * @param string $uuid
     * @param int|null $backoff
     * @return void
     */
    public function place(int $deathCount, int $subscriptionId, mixed $data, string $uuid, ?int $backoff): void
    {
        if (!$backoff) {
            $backoff = $this->calculateBackoff($deathCount);
        }

        $queueName = 'event.delay.' . $backoff;
        $retryRoutingKey = 'event.retry.' . $backoff;

        $this->assertDelayQueue($backoff, $queueName, $retryRoutingKey);
        $this->publisher->publish($retryRoutingKey, [
            self::SUBSCRIPTION_ID => $subscriptionId,
            self::DEATH_COUNT =>  $deathCount,
            self::CONTENT => $this->serializer->serialize($data),
            self::UUID => $uuid
        ]);
    }

    /**
     * Kill the asynchronous event and send it to the DEAD LETTERS department
     *
     * @param int $subscriptionId
     * @param mixed $data
     * @return void
     */
    public function kill(int $subscriptionId, mixed $data): void
    {
        $this->publisher->publish(
            QueueMetadataInterface::DEAD_LETTER_KILL_KEY,
            [
                self::SUBSCRIPTION_ID => $subscriptionId,
                self::DEATH_COUNT => 0,
                self::CONTENT => $this->serializer->serialize($data)
            ]
        );
    }

    /**
     * Asserts the delay queue and binds it to the fail-over exchange.
     *
     * In RabbitMQ creating a queue is idempotent.
     * https://www.rabbitmq.com/tutorials/tutorial-one-php.html
     *
     * @param int $backoff
     * @param string $queueName
     * @param string $retryRoutingKey
     * @return void
     */
    private function assertDelayQueue(int $backoff, string $queueName, string $retryRoutingKey): void
    {
        $config = $this->configPool->get('amqp');
        $backoff = abs($backoff);

        $queueConfigItem = $this->queueConfigItemFactory->create();
        $queueConfigItem->setData([
            'name' => $queueName,
            'connection' => 'amqp',
            'durable' => true,
            'autoDelete' => true,
            'arguments' => [
                'x-dead-letter-exchange' => QueueMetadataInterface::FAILOVER_EXCHANGE,
                'x-dead-letter-routing-key' => QueueMetadataInterface::DEAD_LETTER_ROUTING_KEY,
                'x-message-ttl' => $backoff * 1000,
                'x-expires' => $backoff * 1000 * 2
            ]
        ]);

        $this->queueInstaller->install($config->getChannel(), $queueConfigItem);

        $bindingConfig = $this->bindingFactory->create();
        $bindingConfig->setData([
            'id' => 'EventRetry' . $backoff . 'Binding',
            'destinationType' => 'queue',
            'destination' => $queueName,
            'arguments' => [],
            'topic' => $retryRoutingKey,
            'disabled' => false
        ]);

        $this->bindingInstaller->install(
            $config->getChannel(),
            $bindingConfig,
            QueueMetadataInterface::FAILOVER_EXCHANGE
        );
    }

    /**
     * Exponential back off. Change the exponent to determine cubical back off or quartic back off
     *
     * @param int $deathCount
     * @return int
     */
    private function calculateBackoff(int $deathCount): int
    {
        return min(60, pow($deathCount, 2));
    }
}
