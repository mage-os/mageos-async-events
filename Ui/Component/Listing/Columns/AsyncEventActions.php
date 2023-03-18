<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AsyncEventActions extends Column
{
    private const URL_PATH_TRACE = 'async_events/logs/trace';

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        private readonly UrlInterface $urlBuilder,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare the data source for action column
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['uuid'])) {
                    $item[$this->getData('name')] = [
                        'trace' => [
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_TRACE,
                                [
                                    'uuid' => $item['uuid']
                                ]
                            ),
                            'label' => __('Trace'),
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
