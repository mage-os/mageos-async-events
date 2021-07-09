<?php

namespace Aligent\Webhooks\Service\Webhook;

use Aligent\Webhooks\Api\Data\WebhookInterface;
use Aligent\Webhooks\Helper\NotifierResult;

class ExampleNotifier implements NotifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function notify(WebhookInterface $webhook, array $data): NotifierResult
    {
        // Do something here with any data
        $data = "Example notifier with some data: " . $data["objectId"];

        $result = new NotifierResult();
        $result->setSuccess(true);
        $result->setSubscriptionId($webhook->getSubscriptionId());
        $result->setResponseData($data);

        return $result;
    }
}