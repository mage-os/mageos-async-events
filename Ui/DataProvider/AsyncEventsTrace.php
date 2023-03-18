<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Ui\DataProvider;

use MageOS\AsyncEvents\Model\Details;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MageOS\AsyncEvents\Model\ResourceModel\AsyncEventLog\CollectionFactory as AsyncEventLogCollectionFactory;

class AsyncEventsTrace extends AbstractDataProvider
{

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param AsyncEventLogCollectionFactory $collectionFactory
     * @param Details $traceDetails
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        AsyncEventLogCollectionFactory $collectionFactory,
        private readonly Details $traceDetails,
        private readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $uuid = $this->request->getParam($this->requestFieldName);
        $details = $this->traceDetails->getDetails($uuid);
        $trace = current($details['traces']);

        /**
         * Prettify JSON by decoding and re-encoding with the JSON_PRETTY_PRINT flag
         */
        $prettyPrint = json_decode($trace['serialized_data'], true);
        $prettyPrint = json_encode($prettyPrint, JSON_PRETTY_PRINT);
        $trace['serialized_data'] = $prettyPrint;

        return [
            $uuid => [
                'general' => $trace
            ]
        ];
    }
}
