<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use MageOS\AsyncEvents\Api\Data\AsyncEventSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class AsyncEventSearchResults extends SearchResults implements AsyncEventSearchResultsInterface
{
}
