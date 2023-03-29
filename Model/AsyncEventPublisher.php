<?php

namespace MageOS\AsyncEvents\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;
use MageOS\AsyncEvents\Api\AsyncEventPublisherInterface;
use MageOS\AsyncEvents\Helper\QueueMetadataInterface;

class AsyncEventPublisher implements AsyncEventPublisherInterface
{
    /**
     * @param PublisherInterface $publisher
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly SerializerInterface$serializer
    ) {
    }

    /**
     * Publish an asynchronous event
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function publish(string $eventName, array $data): void
    {
        $arguments = $this->serializer->serialize($data);

        $data = [
            $eventName,
            $arguments,
        ];

        $this->publisher->publish(QueueMetadataInterface::EVENT_QUEUE, $data);
    }
}
