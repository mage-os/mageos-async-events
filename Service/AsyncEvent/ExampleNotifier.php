<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use CloudEvents\V1\CloudEventImmutable;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Helper\NotifierResult;

class ExampleNotifier implements NotifierInterface
{
    /**
     * @inheritDoc
     */
    public function notify(AsyncEventInterface $asyncEvent, CloudEventImmutable $event): NotifierResult
    {
        // Do something here with any data
//        $data = "Example notifier with some data: " . $data["objectId"];

        $result = new NotifierResult();
        $result->setSuccess(true);
        $result->setSubscriptionId($asyncEvent->getSubscriptionId());
        $result->setResponseData('ok');

        return $result;
    }
}
