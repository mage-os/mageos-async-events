<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AsyncEventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface[]
     */
    public function getItems();

    /**
     * @param \MageOS\AsyncEvents\Api\Data\AsyncEventDisplayInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
