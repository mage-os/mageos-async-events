<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Service\AsyncEvent;

use CloudEvents\Serializers\Normalizers\V1\Normalizer;
use CloudEvents\V1\CloudEventImmutable;
use MageOS\AsyncEvents\Api\Data\AsyncEventInterface;
use MageOS\AsyncEvents\Helper\NotifierResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Encryption\EncryptorInterface;
use CloudEvents\Serializers\JsonSerializer;

/**
 * Example HTTP notifier
 */
class HttpNotifier implements NotifierInterface
{
    /**
     * Hash algorithm. Changing this in future will be a breaking change
     */
    private const HASHING_ALGORITHM = 'sha256';

    /**
     * @param Client $client
     * @param EncryptorInterface $encryptor
     * @param Normalizer $normalizer
     */
    public function __construct(
        private readonly Client $client,
        private readonly EncryptorInterface $encryptor,
        private readonly Normalizer $normalizer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function notify(AsyncEventInterface $asyncEvent, CloudEventImmutable $event): NotifierResult
    {
        $body = JsonSerializer::create()->serializeStructured($event);

        // Sign the payload that the client can verify.
        $headers = [
            'x-magento-signature' => hash_hmac(
                self::HASHING_ALGORITHM,
                $body,
                $this->encryptor->decrypt($asyncEvent->getVerificationToken())
            )
        ];

        $notifierResult = new NotifierResult();
        $notifierResult->setSubscriptionId($asyncEvent->getSubscriptionId());
        $notifierResult->setAsyncEventData($this->normalizer->normalize($event, false));

        try {
            $response = $this->client->post(
                $asyncEvent->getRecipientUrl(),
                [
                    'headers' => $headers,
                    'body' => $body,
                    'timeout' => 15,
                    'connect_timeout' => 5
                ]
            );

            $notifierResult->setIsSuccessful(true);
            $notifierResult->setResponseData($response->getBody()->getContents());

        } catch (RequestException $exception) {
            /**
             * Catch a RequestException, so we cover even the network layer exceptions which might sometimes
             * not have a response.
             */
            $notifierResult->setIsSuccessful(false);

            if ($exception->hasResponse()) {
                $response = $exception->getResponse();
                $responseContent = $response->getBody()->getContents();
                $responseStatusCode = $response->getStatusCode();
                $exceptionMessage = !empty($responseContent) ? $responseContent : $response->getReasonPhrase();

                $notifierResult->setResponseData($exceptionMessage);
                $notifierResult->setIsRetryable($responseStatusCode >= 500 || $responseStatusCode === 429);

                if ($response->hasHeader('Retry-After')) {
                    $retryAfter = $response->getHeader('Retry-After')[0];
                    if (is_numeric($retryAfter)) {
                        $notifierResult->setRetryAfter((int)$retryAfter);
                    }
                }
            } else {
                $notifierResult->setResponseData(
                    $exception->getMessage()
                );
            }
        }

        return $notifierResult;
    }
}
