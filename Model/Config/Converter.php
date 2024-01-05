<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Config;

use DOMNode;
use InvalidArgumentException;
use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * @param $source
     * @return array
     */
    public function convert($source): array
    {
        $output = [];
        $asyncEvents = $source->getElementsByTagName('async_event');

        /** @var DOMNode $asyncEventConfig */
        foreach ($asyncEvents as $asyncEventConfig) {
            $eventName = $asyncEventConfig->attributes->getNamedItem('name')->nodeValue;

            $eventService = [];
            $eventResources = [];

            /** @var DOMNode $serviceConfig */
            foreach ($asyncEventConfig->childNodes as $child) {
                if ($child->nodeName === 'service') {
                    $eventService = $this->convertServiceConfig($child);
                } elseif ($child->nodeName === 'resources') {
                    $eventResources = $this->convertResourcesConfig($child);
                }
            }
            // phpcs:ignore
            $output[mb_strtolower($eventName)] = array_merge($eventService, $eventResources);
        }

        return $output;
    }

    /**
     * @param DOMNode $observerConfig
     * @return array
     */
    private function convertServiceConfig(DomNode $observerConfig): array
    {
        $output = [];

        $classAttribute = $observerConfig->attributes->getNamedItem('class');
        if (!$classAttribute || !$classAttribute->nodeValue) {
            throw new InvalidArgumentException('Attribute class is missing');
        }

        $methodAttribute = $observerConfig->attributes->getNamedItem('method');
        if (!$methodAttribute || !$methodAttribute->nodeValue) {
            throw new InvalidArgumentException('Attribute method is missing');
        }

        // check that the specified class/method exists and is public
        try {
            $serviceClass = new \ReflectionClass($classAttribute->nodeValue);
        } catch (\ReflectionException $e) {
            throw new InvalidArgumentException(
                sprintf('Class %s does not exist', $classAttribute->nodeValue)
            );
        }
        try {
            $serviceMethod = $serviceClass->getMethod($methodAttribute->nodeValue);
        } catch (\ReflectionException $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Method %s does not exist is class %s',
                    $methodAttribute->nodeValue,
                    $classAttribute->nodeValue
                )
            );
        }

        $output['class'] = $classAttribute->nodeValue;
        $output['method'] = $methodAttribute->nodeValue;

        return $output;
    }

    /**
     * @param DOMNode $resourcesConfig
     * @return array[]
     */
    private function convertResourcesConfig(DOMNode $resourcesConfig): array
    {
        $resources = [];
        foreach ($resourcesConfig->childNodes as $resourceNode) {
            if ($resourceNode->nodeName === 'resource') {
                $resources[] = $resourceNode->nodeValue;
            }
        }

        return ['resources' => $resources];
    }
}
