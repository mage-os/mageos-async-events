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
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ServiceOutputProcessor
     */
    private $outputProcessor;

    /**
     * @var AsyncEventConfig
     */
    private $asyncEventConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ServiceInputProcessor
     */
    private $inputProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
        EventDispatcher        $dispatcher,
        ServiceOutputProcessor $outputProcessor,
        ObjectManagerInterface $objectManager,
        AsyncEventConfig       $asyncEventConfig,
        ServiceInputProcessor  $inputProcessor,
        Json                   $json,
        LoggerInterface        $logger
    ) {
        $this->dispatcher = $dispatcher;
        $this->json = $json;
        $this->outputProcessor = $outputProcessor;
        $this->asyncEventConfig = $asyncEventConfig;
        $this->objectManager = $objectManager;
        $this->inputProcessor = $inputProcessor;
        $this->logger = $logger;
    }

    /**
     * @param array $queueMessage
     */
    public function process(array $queueMessage)
    {
        try {
            // In every publish the data is an array of strings, the first string is the hook name itself, the second
            // name is a serialised string of parameters that the service method accepts.
            // In a future major version this will change to a schema type e.g: AsyncEventMessageInterface
            $eventName = $queueMessage[0];
            $output = $this->json->unserialize($queueMessage[1]);

            $configData = $this->asyncEventConfig->get($eventName);
            $serviceClassName = $configData['class'];
            $serviceMethodName = $configData['method'];
            $service = $this->objectManager->create($serviceClassName);
            $inputParams = $this->inputProcessor->process($serviceClassName, $serviceMethodName, $output);

            $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);

            $outputData = $this->outputProcessor->process(
                $outputData,
                $serviceClassName,
                $serviceMethodName
            );

            $this->dispatcher->dispatch($eventName, $outputData);
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
