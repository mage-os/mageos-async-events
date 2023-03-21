<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Details;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    /**
     * BackButton Constructor
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Get button data with options
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->urlBuilder->getUrl('*/logs/index')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
