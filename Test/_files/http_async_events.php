<?php

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();

$connection->insertOnDuplicate('async_event_subscriber', [
    'subscription_id' => 1,
    'event_name' => 'example.event',
    'recipient_url' => 'https://mock.codes/500',
    'status' => 1,
    'metadata' => 'http',
    'verification_token' => 'secret',
    'subscribed_at' => (new DateTime())->format(DateTimeInterface::ATOM)
]);
