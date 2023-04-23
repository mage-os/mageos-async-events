<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Model\Config as AsyncEventConfig;
use MageOS\AsyncEvents\Service\AsyncEvent\EventDispatcher;
use Exception;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Psr\Log\LoggerInterface;

class AsyncEventTriggerHandler
{
    /**
     * @param EventDispatcher $dispatcher
     * @param ServiceOutputProcessor $outputProcessor
     * @param ObjectManagerInterface $objectManager
     * @param AsyncEventConfig $asyncEventConfig
     * @param ServiceInputProcessor $inputProcessor
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly EventDispatcher $dispatcher,
        private readonly ServiceOutputProcessor $outputProcessor,
        private readonly ObjectManagerInterface $objectManager,
        private readonly AsyncEventConfig $asyncEventConfig,
        private readonly ServiceInputProcessor $inputProcessor,
        private readonly Json $json,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Process an asynchronous event dispatch
     *
     * @param array $queueMessage
     * @return void
     */
    public function process(array $queueMessage): void
    {
        try {
            // In every publish the data is an array of strings, the first string is the hook name itself, the second
            // name is a serialised string of parameters that the service method accepts.
            // In a future major version this will change to a schema type e.g: AsyncEventMessageInterface
            $eventName = $queueMessage[0];
            $output = $this->json->unserialize($queueMessage[1]);
            $storeId = (int) ($queueMessage[2] ?? 0);

            $configData = $this->asyncEventConfig->get($eventName);
            $serviceClassName = $configData['class'];
            $serviceMethodName = $configData['method'];
            $service = $this->objectManager->create($serviceClassName);
            $inputParams = $this->inputProcessor->process($serviceClassName, $serviceMethodName, $output);

            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);

            $outputData = $this->outputProcessor->process(
                $outputData,
                $serviceClassName,
                $serviceMethodName
            );

            $this->dispatcher->dispatch($eventName, $outputData, $storeId);
        } catch (Exception $exception) {
            $this->logger->critical(
                __('Error when processing %async_event async event', [
                    'async_event' => $eventName
                ]),
                [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]
            );
        }
    }
}
