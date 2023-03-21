<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AsyncEventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Getter for items
     *
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface[]
     */
    public function getItems();

    /**
     * Setter for items
     *
     * @param \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
