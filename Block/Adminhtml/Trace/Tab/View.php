<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Block\Adminhtml\Trace\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class View extends Template implements TabInterface
{
    /**
     * Getter for tab label
     *
     * @return Phrase
     */
    public function getTabLabel(): Phrase
    {
        return __('Overview');
    }

    /**
     * Getter for tab title
     *
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Overview');
    }

    /**
     * Getter for tab class
     *
     * @return string
     */
    public function getTabClass(): string
    {
        return '';
    }

    /**
     * Getter for tab url
     *
     * @return string
     */
    public function getTabUrl(): string
    {
        return '';
    }

    /**
     * Getter for is ajax loaded
     *
     * @return bool
     */
    public function isAjaxLoaded(): bool
    {
        return false;
    }

    /**
     * Getter for can show tab
     *
     * @return bool
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * Getter for is hidden
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return false;
    }
}
