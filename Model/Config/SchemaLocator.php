<?php

declare(strict_types=1);

namespace MageOS\AsyncEvents\Model\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged configs
     *
     * @var string
     */
    private $_schema;

    /**
     * Path to corresponding XSD file with validation rules for individual configs
     *
     * @var string
     */
    private $_schemaFile;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $dir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'MageOS_AsyncEvents');
        $this->_schema = $dir . '/async_events.xsd';
        $this->_schemaFile = $dir . '/async_events.xsd';
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->_schema;
    }

    /**
     * @return string
     */
    public function getPerFileSchema(): string
    {
        return $this->_schemaFile;
    }
}
