<?php

namespace Swissup\Model\ResourceModel;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class CollectionGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Model/ResourceModel/{$modelName}/Collection.php";
        return $filename;
    }

    public function __toString()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();
        $tableName = $this->getTableName();
        $filename = $this->getFilename();

        $str = '';
        $t = '    ';
        $primaryKey = $this->getPrimary();
        $primaryKey = end($primaryKey);
        $str .= "<?php
namespace {$vendor}\\{$moduleName}\\Model\\ResourceModel\\$modelName;

/* {$filename} */
/**
 * {$moduleName} {$modelName} Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        \$this->_init(\\$vendor\\$moduleName\\Model\\$modelName::class, \\$vendor\\$moduleName\\Model\\ResourceModel\\$modelName::class);
    }
}";
        return $str;
    }
}
