<?php

/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\AsyncEvents\Model\Adapter\FieldMapper;

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
            "created" => [
                "type" => "date",
                "format" => "yyyy-MM-dd HH:mm:ss"
            ],
        ];
    }
}
