<?php

use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$asyncEventFactory = $objectManager->get(AsyncEventInterfaceFactory::class);

$asyncEventRepository = $objectManager->get(\MageOS\AsyncEvents\Api\AsyncEventRepositoryInterface::class);

/** @var AsyncEventInterface $asyncEvent */
$asyncEvent = $asyncEventFactory->create(
    [
        'data' => [
            'event' => 'example.event',
            'recipient_url' => 'http://host.docker.internal:3001/failable',
            'verification_token' => 'supersecret',
            'status' => 1
        ]
    ]
);
$asyncEvent->setEventName('example.event');
$asyncEventRepository->save($asyncEvent);
