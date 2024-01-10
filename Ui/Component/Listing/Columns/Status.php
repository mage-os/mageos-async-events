<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            switch ($item['success']) {
                case 0:
                    $class = 'critical';
                    $message = 'Failed';
                    break;
                case 1:
                    $class = 'notice';
                    $message = 'Success';
                    break;
                default:
                    $class = 'minor';
                    $message = 'Unknown';
                    break;
            }
            $item['success'] = '<span class="grid-severity-' . $class . '">' . $message . '</span>';
        }

        return $dataSource;
    }
}
