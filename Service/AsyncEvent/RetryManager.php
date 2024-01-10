<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use Magento\Framework\Amqp\ConfigPool;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingFactory;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemFactory;
use Magento\Framework\Serialize\SerializerInterface;

class RetryManager
{
    const DEATH_COUNT = 'death_count';
    const SUBSCRIPTION_ID = 'subscription_id';
    const CONTENT = 'content';
    const UUID = 'uuid';

    /**
     * @var ConfigPool
     */
    private $configPool;

    /**
     * @var QueueInstaller
     */
    private $queueInstaller;

    /**
     * @var BindingInstallerInterface
     */
    private $bindingInstaller;

    /**
     * @var AmqpPublisher
     */
    private $publisher;

    /**
     * @var QueueConfigItemFactory
     */
    private $queueConfigItemFactory;

    /**
     * @var BindingFactory
     */
    private $bindingFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

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
        ConfigPool $configPool,
        QueueInstaller $queueInstaller,
        BindingInstallerInterface $bindingInstaller,
        AmqpPublisher $publisher,
        QueueConfigItemFactory $queueConfigItemFactory,
        BindingFactory $bindingFactory,
        SerializerInterface $serializer
    ) {
        $this->queueInstaller = $queueInstaller;
        $this->bindingInstaller = $bindingInstaller;
        $this->configPool = $configPool;
        $this->publisher = $publisher;
        $this->queueConfigItemFactory = $queueConfigItemFactory;
        $this->bindingFactory = $bindingFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param int $subscriptionId
     * @param $data
     * @param string
     * @return void
     */
    public function init(int $subscriptionId, $data, string $uuid)
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
     * @param int $deathCount
     * @param int $subscriptionId
     * @param $data
     * @param string $uuid
     * @return void
     */
    public function place(int $deathCount, int $subscriptionId, $data, string $uuid)
    {
        $backoff = $this->calculateBackoff($deathCount);
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
     * @param int $subscriptionId
     * @param $data
     * @return void
     */
    public function kill(int $subscriptionId, $data)
    {
        $this->publisher->publish(QueueMetadataInterface::DEAD_LETTER_KILL_KEY, [
            self::SUBSCRIPTION_ID => $subscriptionId,
            self::DEATH_COUNT => 0,
            self::CONTENT => $this->serializer->serialize($data)
        ]);
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
    private function assertDelayQueue(int $backoff, string $queueName, string $retryRoutingKey)
    {
        $config = $this->configPool->get('amqp');

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
