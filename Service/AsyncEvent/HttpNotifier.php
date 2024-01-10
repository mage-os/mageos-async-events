<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Helper\NotifierResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Example HTTP notifier
 */
class HttpNotifier implements NotifierInterface
{
    /**
     * Hash algorithm. Changing this in future will be a breaking change
     */
    const HASHING_ALGORITHM = 'sha256';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Client $client
     * @param Json $json
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Client $client,
        Json $json,
        EncryptorInterface $encryptor
    ) {
        $this->client = $client;
        $this->json = $json;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function notify(AsyncEventInterface $asyncEvent, array $data): NotifierResult
    {
        $body = $data;

        // Sign the payload that the client can verify.
        $headers = [
            'x-magento-signature' => hash_hmac(
                self::HASHING_ALGORITHM,
                $this->json->serialize($body),
                $this->encryptor->decrypt($asyncEvent->getVerificationToken())
            )
        ];

        $notifierResult = new NotifierResult();
        $notifierResult->setSubscriptionId($asyncEvent->getSubscriptionId());
        $notifierResult->setAsyncEventData($body);

        try {
            $response = $this->client->post(
                $asyncEvent->getRecipientUrl(),
                [
                    'headers' => $headers,
                    'json' => $body,
                    'timeout' => 15,
                    'connect_timeout' => 5
                ]
            );

            $notifierResult->setSuccess(
                $response->getStatusCode() >= 200
                && $response->getStatusCode() < 300
            );

            $notifierResult->setResponseData($response->getBody()->getContents());

        } catch (RequestException $exception) {
            /**
             * Catch a RequestException, so we cover even the network layer exceptions which might sometimes
             * not have a response.
             */
            $notifierResult->setSuccess(false);

            if ($exception->hasResponse()) {
                $response = $exception->getResponse();
                $responseContent = $response->getBody()->getContents();
                $exceptionMessage = !empty($responseContent) ? $responseContent : $response->getReasonPhrase();

                $notifierResult->setResponseData($exceptionMessage);
            } else {
                $notifierResult->setResponseData(
                    $exception->getMessage()
                );
            }
        }

        return $notifierResult;
    }
}
