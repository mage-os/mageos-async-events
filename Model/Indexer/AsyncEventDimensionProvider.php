<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Indexer;

use MageOS\AsyncEvents\Model\ResourceModel\AsyncEvent\CollectionFactory as AsyncEventCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;
use SplFixedArray;
use Traversable;

class AsyncEventDimensionProvider implements DimensionProviderInterface
{
    /**
     * Name for asynchronous event dimension for multidimensional indexer
     * 'ae' - stands for 'asynchronous_event'
     */
    public const DIMENSION_NAME = 'ae';

    /**
     * @var SplFixedArray
     */
    private $asyncEventDataIterator;

    /**
     * @param AsyncEventCollectionFactory $asyncEventCollectionFactory
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        private readonly AsyncEventCollectionFactory $asyncEventCollectionFactory,
        private readonly DimensionFactory $dimensionFactory
    ) {
    }

    /**
     * Get dimension iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->getAsyncEvents() as $asyncEvent) {
            yield [self::DIMENSION_NAME => $this->dimensionFactory->create(self::DIMENSION_NAME, $asyncEvent)];
        }
    }

    /**
     * Get unique async events
     *
     * The source of truth for this data is the `async_events.xml` configuration file. However, if some events are
     * configured but do not have any subscribers, it might be useless to create empty indices in Elasticsearch, so we
     * get it from the database by unique name.
     *
     * @return array
     */
    public function getAsyncEvents(): array
    {
        if ($this->asyncEventDataIterator === null) {
            $asyncEvents = $this->asyncEventCollectionFactory->create()
                ->addFieldToSelect('event_name')
                ->distinct(true)
                ->getColumnValues('event_name')
            ;

            $this->asyncEventDataIterator = $asyncEvents;
        }

        return $this->asyncEventDataIterator;
    }
}
