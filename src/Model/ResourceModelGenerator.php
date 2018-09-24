<?php

namespace Swissup\Model;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class ResourceModelGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Model/ResourceModel/{$modelName}.php";
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
        $str .= "\n/* {$filename} */\n<?php
namespace {$vendor}\\{$moduleName}\\Model\\ResourceModel;

/**
 * {$moduleName} {$modelName} mysql resource
 */
class {$modelName} extends \\Magento\\Framework\\Model\\ResourceModel\\Db\\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        \$this->_init('{$tableName}', '{$primaryKey}');
    }
";
        $str .= "\n}";
        return $str;
    }
}
