<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use Magento\Framework\Config\DataInterface;

class Config
{
    /**
     * @var DataInterface
     */
    protected $_dataStorage;

    /**
     * @param DataInterface $dataStorage
     */
    public function __construct(DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return $this->_dataStorage->get($key, []);
    }
}
