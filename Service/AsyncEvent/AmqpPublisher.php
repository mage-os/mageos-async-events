<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use Magento\Framework\Amqp\Config;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * The AmqpPublisher is a simple publisher that allows to take control over an AMQP Message and the channel as there
 * is no way with any of the other Publishers to add more headers to an AMQP Message AND also to publish to a queue that
 * is dynamically created (not configured via communication.xml, queue_publisher.xml and queue_topology.xml)
 *
 * @see \Magento\Framework\MessageQueue\PublisherInterface
 */
class AmqpPublisher implements PublisherInterface
{
    /**
     * @param Config $amqpConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param Json $json
     */
    public function __construct(
        private readonly Config $amqpConfig,
        private readonly EnvelopeFactory $envelopeFactory,
        private readonly Json $json
    ) {
    }

    /**
     * Publish an AMQP message to RabbitMQ
     *
     * @param string $topicName
     * @param mixed $data
     * @return null
     */
    public function publish($topicName, $data)
    {
        $body = $this->json->serialize($data);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $body,
                'properties' => [
                    'delivery_mode' => 2,
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(uniqid($topicName)),
                    'application_headers' => new AMQPTable([
                        // Since we have to conform to the interface, there is no nice way of passing this information
                        // when calling this method unless we allow temporal coupling
                        'x-retry-count' => $data[RetryManager::DEATH_COUNT]
                    ])
                ]
            ]
        );

        $channel = $this->amqpConfig->getChannel();
        $msg = new AMQPMessage($envelope->getBody(), $envelope->getProperties());
        $channel->basic_publish($msg, QueueMetadataInterface::FAILOVER_EXCHANGE, $topicName);

        return null;
    }
}
