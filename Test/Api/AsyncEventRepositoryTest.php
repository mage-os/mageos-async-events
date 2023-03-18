<?php


declare(strict_types=1);

namespace MageOS\AsyncEvents\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class AsyncEventRepositoryTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture MageOS_AsyncEvents::Test/_files/http_async_events.php
     */
    public function testGet()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/async_event/1',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        $this->assertArrayHasKey('subscription_id', $response);
        $this->assertArrayHasKey('event_name', $response);
        $this->assertArrayHasKey('recipient_url', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('subscribed_at', $response);

        // Make sure that the verification token is not exposed even if it is encrypted.
        $this->assertArrayNotHasKey('verification_token', $response);
    }

    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => ''
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/async_events' . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertIsArray($response['items'][0]);

        $this->assertArrayHasKey('subscription_id', $response['items'][0]);
        $this->assertArrayHasKey('event_name', $response['items'][0]);
        $this->assertArrayHasKey('recipient_url', $response['items'][0]);
        $this->assertArrayHasKey('status', $response['items'][0]);
        $this->assertArrayHasKey('subscribed_at', $response['items'][0]);

        // Make sure that the verification token is not exposed even if it is encrypted.
        $this->assertArrayNotHasKey('verification_token', $response['items'][0]);
    }
}
