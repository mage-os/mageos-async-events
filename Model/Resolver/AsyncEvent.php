<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Resolver;

use MageOS\AsyncEvents\Model\Indexer\AsyncEventScopeFactory;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory as AsyncEventCollectionFactory;
use Magento\Framework\App\ScopeResolverInterface;

class AsyncEvent implements ScopeResolverInterface
{
    /**
     * @var AsyncEventCollectionFactory
     */
    private $asyncEventCollectionFactory;

    /**
     * @var AsyncEventScopeFactory
     */
    private $asyncEventScopeFactory;

    /**
     * @param AsyncEventCollectionFactory $asyncEventCollectionFactory
     * @param AsyncEventScopeFactory $asyncEventScopeFactory
     */
    public function __construct(
        AsyncEventCollectionFactory $asyncEventCollectionFactory,
        AsyncEventScopeFactory $asyncEventScopeFactory
    ) {
        $this->asyncEventCollectionFactory = $asyncEventCollectionFactory;
        $this->asyncEventScopeFactory = $asyncEventScopeFactory;
    }

    /**
     * @inheritDoc
     */
    public function getScope($scopeId = null)
    {
        $asyncEventScope = $this->asyncEventScopeFactory->create();
        $asyncEventScope->setId($scopeId);

        return $asyncEventScope;
    }

    /**
     * @inheritDoc
     */
    public function getScopes(): array
    {
        $asyncEvents = $this->asyncEventCollectionFactory->create()->getData();

        $scope = [];
        foreach ($asyncEvents as $asyncEvent) {
            $asyncEventScope = $this->asyncEventScopeFactory->create([
                'data' => $asyncEvent
            ]);

            $asyncEventScope->setId($asyncEvent['event_name']);

            $scope[] = $asyncEventScope;
        }

        return $scope;
    }
}
