<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model;

use Magento\Framework\Config\DataInterface;

class Config
{
    /**
     * @param DataInterface $dataStorage
     */
    public function __construct(private readonly DataInterface $dataStorage)
    {
    }

    /**
     * Getter for getting config value
     *
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return $this->dataStorage->get($key, []);
    }
}
