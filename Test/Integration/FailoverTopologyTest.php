<?php


declare(strict_types=1);

namespace MageOS\AsyncEvents\Test\Integration;

use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use MageOS\AsyncEvents\Service\AsyncEvent\RetryManager;
use Magento\TestFramework\Helper\Amqp;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class FailoverTopologyTest extends TestCase
{

    /**
     * @var Amqp|null
     */
    private ?Amqp $helper;

    /**
     * @var RetryManager|null
     */
    private ?RetryManager $retryManager;

    protected function setUp(): void
    {
        $this->helper = Bootstrap::getObjectManager()->create(Amqp::class);
        $this->retryManager = Bootstrap::getObjectManager()->create(RetryManager::class);

        if (!$this->helper->isAvailable()) {
            $this->fail('This test relies on RabbitMQ Management Plugin.');
        }
    }

    public function testDelayQueueCreation(): void
    {
        $exchanges = $this->helper->getExchanges();

        $this->assertArrayHasKey('event.failover', $exchanges);

        /**
         * Because the first delay queue has a life of 2 seconds, create a few
         * messages so that it's alive a little longer.
         */
        $this->retryManager->init(1, 'test', 'uuid');
        $this->retryManager->init(1, 'test', 'uuid');
        $this->retryManager->init(1, 'test', 'uuid');

        /**
         * Place events at different death levels
         */
        $this->retryManager->place(2, 1, 'test', 'uuid');
        $this->retryManager->place(3, 1, 'test', 'uuid');
        $this->retryManager->place(4, 1, 'test', 'uuid');
        $this->retryManager->place(5, 1, 'test', 'uuid');
        $this->retryManager->place(6, 1, 'test', 'uuid');
        $this->retryManager->place(7, 1, 'test', 'uuid');
        $this->retryManager->place(8, 1, 'test', 'uuid');
        $this->retryManager->place(9, 1, 'test', 'uuid');
        $this->retryManager->place(10, 1, 'test', 'uuid');

        $bindings = $this->helper->getExchangeBindings('event.failover');

        $destinations = array_map(function ($binding) {
            return $binding['destination'];
        }, $bindings);

        /**
         * Exponential backoff delay queues
         */
        $this->assertContains('event.retry.init', $destinations);
        $this->assertContains('event.delay.4', $destinations);
        $this->assertContains('event.delay.9', $destinations);
        $this->assertContains('event.delay.16', $destinations);
        $this->assertContains('event.delay.25', $destinations);
        $this->assertContains('event.delay.36', $destinations);
        $this->assertContains('event.delay.49', $destinations);
        $this->assertContains('event.delay.60', $destinations);

        // Backoff thresholds at 60 seconds
        // min(60, pow($deathCount, 2));
        // So for example an event that has 8th (or more) deaths, the max wait time
        // is 60 seconds and not 64, 81, 100 etc.
        $this->assertNotContains('event.delay.64', $destinations);
        $this->assertNotContains('event.delay.81', $destinations);
        $this->assertNotContains('event.delay.100', $destinations);
    }
}
