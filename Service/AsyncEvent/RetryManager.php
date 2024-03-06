<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use CloudEvents\Serializers\JsonSerializer;
use CloudEvents\V1\CloudEventImmutable;
use MageOS\AsyncEvents\Api\RetryManagementInterface;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use Magento\Framework\Amqp\ConfigPool;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingFactory;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemFactory;

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
     */
    public function __construct(
        private readonly ConfigPool $configPool,
        private readonly QueueInstaller $queueInstaller,
        private readonly BindingInstallerInterface $bindingInstaller,
        private readonly AmqpPublisher $publisher,
        private readonly QueueConfigItemFactory $queueConfigItemFactory,
        private readonly BindingFactory $bindingFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function init(int $subscriptionId, CloudEventImmutable $event, string $uuid): void
    {
        $this->assertDelayQueue(
            1,
            QueueMetadataInterface::RETRY_INIT_ROUTING_KEY,
            QueueMetadataInterface::RETRY_INIT_ROUTING_KEY
        );

        $this->publisher->publish(QueueMetadataInterface::RETRY_INIT_ROUTING_KEY, [
            self::SUBSCRIPTION_ID => $subscriptionId,
            self::DEATH_COUNT => 1,
            self::CONTENT => JsonSerializer::create()->serializeStructured($event),
            self::UUID => $uuid
        ]);
    }

    /**
     * @inheritDoc
     */
    public function place(
        int $deathCount,
        int $subscriptionId,
        CloudEventImmutable $event,
        string $uuid,
        ?int $backoff
    ): void {
        if (!$backoff) {
            $backoff = $this->calculateBackoff($deathCount);
        }

        $queueName = 'event.delay.' . $backoff;
        $retryRoutingKey = 'event.retry.' . $backoff;

        $this->assertDelayQueue($backoff, $queueName, $retryRoutingKey);
        $this->publisher->publish($retryRoutingKey, [
            self::SUBSCRIPTION_ID => $subscriptionId,
            self::DEATH_COUNT =>  $deathCount,
            self::CONTENT => JsonSerializer::create()->serializeStructured($event),
            self::UUID => $uuid
        ]);
    }

    /**
     * @inheritDoc
     */
    public function kill(int $subscriptionId, CloudEventImmutable $event): void
    {
        $this->publisher->publish(
            QueueMetadataInterface::DEAD_LETTER_KILL_KEY,
            [
                self::SUBSCRIPTION_ID => $subscriptionId,
                self::DEATH_COUNT => 0,
                self::CONTENT => JsonSerializer::create()->serializeStructured($event)
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
