<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class View extends Template implements TabInterface
{
    /**
     * @return Phrase
     */
    public function getTabLabel(): Phrase
    {
        return __('Overview');
    }

    /**
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Overview');
    }

    /**
     * @return string
     */
    public function getTabClass(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTabUrl(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isAjaxLoaded(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return false;
    }
}
