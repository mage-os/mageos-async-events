<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DataObject;

/**
 * This data object class only exists for the sake of a ScopeInterface which delegates getId()
 * to a getName();
 *
 * The reason for this is that almost everything upstream (at least in magento/module-elasticsearch-7) assumes
 * the id is a store id
 *
 * For example vendor/magento/module-elasticsearch/Model/Indexer/IndexerHandler.php:113 will eventually call
 * vendor/magento/framework/App/Config.php:68
 * and try to load the relevant store id which will fail if the given id is an id of a different entity. But if it's a
 * string it does not throw an exception and simply returns null instead.
 *
 * For this reason and the auto generated index names being nicer, this class delegates getId() to getName()
 * (magento2_async_event_sales.order.created_V1 instead of magento2_async_event_1_V1)
 */
class AsyncEventScope extends DataObject implements ScopeInterface
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->getName();
    }

    /**
     * Setter for id
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->setName($id);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return (string) $this->getData('code');
    }

    /**
     * Setter for code
     *
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void
    {
        $this->setData('code', $code);
    }

    /**
     * @inheritDoc
     */
    public function getScopeType(): string
    {
        return 'async_event';
    }

    /**
     * @inheritDoc
     */
    public function getScopeTypeName(): string
    {
        return 'Async Event';
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->getData('name');
    }

    /**
     * Setter for name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData('name', $name);
    }
}
