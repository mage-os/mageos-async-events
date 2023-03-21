<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Details;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ReplayButton implements ButtonProviderInterface
{
    /**
     * Get button data with options
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Replay'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save']
                ],
                'form-role' => 'save',
            ],
        ];
    }
}
