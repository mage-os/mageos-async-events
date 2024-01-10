<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

class DynamicFieldMapper implements FieldMapperInterface
{
    /**
     * @inheritDoc
     */
    public function getFieldName($attributeCode, $context = []): string
    {
        return $attributeCode;
    }

    /**
     * @inheritDoc
     */
    public function getAllAttributesTypes($context = []): array
    {
        return [
            "log_id" => [
                "type" => "long",
            ],
            "uuid" => [
                "type" => "keyword",
            ],
            "event_name" => [
                "type" => "keyword",
            ],
            "success" => [
                "type" => "boolean",
            ],
            "created" => [
                "type" => "date",
                "format" => "yyyy-MM-dd HH:mm:ss"
            ],
        ];
    }
}
