<?php

namespace MageOS\AsyncEvents\Test\Integration;

use MageOS\AsyncEvents\Helper\QueueMetadataInterface;
use MageOS\AsyncEvents\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Amqp;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use PHPUnit\Framework\TestCase;

class EventRetryTest extends TestCase
{
    /** @var Amqp|null */
    private ?Amqp $helper;

    /** @var PublisherInterface|null */
    private ?PublisherInterface $publisher;

    /** @var Json|null */
    private ?Json $json;

    /** @var ResourceConnection|null */
    private ?ResourceConnection $connection;

    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                Config::class => TestConfig::class
            ]
        ]);

        $this->helper = Bootstrap::getObjectManager()->create(Amqp::class);
        $this->publisher = Bootstrap::getObjectManager()->create(PublisherInterface::class);
        $this->json = Bootstrap::getObjectManager()->create(Json::class);
        $this->connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);

        if (!$this->helper->isAvailable()) {
            $this->fail('This test relies on RabbitMQ Management Plugin.');
        }
    }

    /**
     * Test event retries
     *
     * Disable database isolation because the transactions need to be committed so that the consumer can
     * retrieve the subscriptions from database in a separate consumer process.
     *
     * @magentoDataFixture MageOS_AsyncEvents::Test/_files/http_async_events.php
     * @magentoDbIsolation disabled
     */
    public function testRetry()
    {
        $this->publisher->publish(
            QueueMetadataInterface::EVENT_QUEUE,
            [
                'example.event',
                $this->json->serialize([
                    'countryId' => 'AU'
                ]),
            ]
        );

        $consumerInitParams = Bootstrap::getInstance()->getAppInitParams();

        $consumerInitParams['TESTS_BASE_DIR'] = INTEGRATION_TESTS_DIR;
        $consumerInitParams['INTEGRATION_TESTS_CLI_AUTOLOADER'] = INTEGRATION_TESTS_DIR . '/framework/autoload.php';

        $publisherConsumerController = Bootstrap::getObjectManager()->create(
            PublisherConsumerController::class,
            [
                'consumers' => ['event.trigger.consumer', 'event.retry.consumer'],
                'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'maxMessages' => 10,
                'appInitParams' => $consumerInitParams
            ]
        );

        try {
            $publisherConsumerController->startConsumers();
            sleep(16);
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        } finally {
            $publisherConsumerController->stopConsumers();
        }

        $table = $this->connection->getTableName('async_event_subscriber_log');
        $connection = $this->connection->getConnection();
        $select = $connection->select()
            ->from($table, 'uuid')
            ->columns(['events' => new \Zend_Db_Expr('COUNT(*)')])
            ->group('uuid');

        $events = $connection->fetchAll($select);

        $this->assertNotEmpty($events);

        foreach ($events as $event) {
            // A batch should be retired for 3 times after the first attempt. 1 + 3
            $this->assertEquals(4, $event['events']);
        }
    }
}
