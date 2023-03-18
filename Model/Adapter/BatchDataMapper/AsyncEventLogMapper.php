<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Framework\Serialize\SerializerInterface;

class AsyncEventLogMapper implements BatchDataMapperInterface
{

    /**
     * @param Builder $builder
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly Builder $builder,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Map database entities into elasticsearch documents
     *
     * @param array $documentData
     * @param int|string $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = []): array
    {
        $documents = [];

        foreach ($documentData as $asyncEventLogId => $indexData) {
            $this->builder->addField('log_id', $indexData['log_id']);
            $this->builder->addField('uuid', $indexData['uuid']);
            $this->builder->addField('event_name', $indexData['event_name']);
            $this->builder->addField('success', (bool) $indexData['success']);
            $this->builder->addField('created', $indexData['created']);

            $this->builder->addField(
                'data',
                $this->serializer->unserialize($indexData['serialized_data'])
            );

            $documents[$asyncEventLogId] = $this->builder->build();
        }

        return $documents;
    }
}
