<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Config;

use Magento\Framework\Config\Reader\Filesystem;

class Reader extends Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/async_event' => 'name'
    ];
}
