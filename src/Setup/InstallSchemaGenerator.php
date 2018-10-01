<?php

namespace Swissup\Setup;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class InstallSchemaGenerator extends \Swissup\GeneratorAbstract
{
    protected $tables = [];

    protected $magentoVersion = 2;

    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();

        // $filename = "{$vendor}/{$moduleName}/" . ($this->magentoVersion == 2 ?
        //     'Setup/InstallSchema.php' :
        //     strtolower("sql/{$vendor}_{$moduleName}_setup/mysql4-install-1.0.0.php"));

        $filename = "{$vendor}/{$moduleName}/Setup/InstallSchema.php";
        return $filename;
    }

    public function __toString()
    {
        $tableName = $this->getTableName();
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = $this->getFilename();

        $this->addTable();

        $str = "<?php
namespace {$vendor}\\{$moduleName}\\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/* {$filename} */

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface \$setup, ModuleContextInterface \$context)
    {
        \$installer = \$setup;
        \$installer->startSetup();
        \$connection = \$installer->getConnection();" .
        $this->getTables() . "
        \$installer->endSetup();
    }
}
";
        return $str;
    }

    protected function getTables()
    {
        $str = '';
        foreach ($this->tables as $tableName => $_str) {
            $str .= "
        /* generation table '$tableName' */{$_str}";
        }

        return $str;
    }

    protected function addTable()
    {
        $tableName = $this->getTableName();
        // $vendor = $this->getVendorName();
        // $moduleName = $this->getModuleName();
        // $modelName = $this->getModelName();

        $t = '    ';
        $tt = $t . $t;
        $ttt = $t . $t . $t;
        $tttt = $t . $t . $t . $t;

        $str = "\n
        \$tableName = \$installer->getTable('{$tableName}');
        if (\$connection->isTableExists(\$tableName)) {
            throw new \Zend_Db_Exception(sprintf('Table \"%s\" already exists', \$tableName));
        }
        \$table = \$connection\n"
            . "{$ttt}->newTable(\$tableName)\n";

        $arrayStart = $this->magentoVersion == 2 ? '[' : 'array(';
        $arrayEnd = $this->magentoVersion == 2 ? ']' : ')';

        foreach ($this->getColumns() as $column) {
            $str .= "{$ttt}->addColumn('{$column['name']}', {$column['type']}, {$column['length']}, {$arrayStart}\n" .
                    ($column['identity'] ? "{$tttt}'identity'  => true,\n" : '') .
                    ($column['unsigned'] ? "{$tttt}'unsigned'  => true,\n" : '') .
                    ($column['nullable'] !== null ?
                        "{$tttt}'nullable'  => " . ($column['nullable'] ? 'true' : 'false') . ",\n" : '') .
                    ($column['default'] !== false ? "{$tttt}'default'  => {$column['default']},\n" : '') .
                    // "'nullable'  => false,\n" .
                    ($column['primary'] ? "{$tttt}'primary'   => true,\n" : '') .
                    "{$ttt}{$arrayEnd}, '{$column['comment']}')\n"
            ;
        }
        foreach ($this->getIndexes() as $index) {
            $fields = $index['fields'];
            foreach ($fields as &$field) {
                $field = "'{$field}'";
            }
            $fields = implode(", ", $fields);

            $str .= "{$ttt}->addIndex(\n{$tttt}\$installer->getIdxName('{$index['table']}', {$arrayStart}{$fields}{$arrayEnd}),\n"
                . "{$tttt}{$arrayStart}{$fields}{$arrayEnd}\n{$ttt})\n";
        }

        foreach ($this->getForeignKeys() as $key) {
            $priColumnName = $key['column'];
            $refTableName  = $key['reference_table'];
            $refColumnName = $key['reference_column'];
            $onDelete      = $key['on_delete'];
            $onUpdate      = '';
            if ($this->magentoVersion != 2) {
                $onUpdate = ', ' . $key['on_update'];
            }

            $str .= "{$ttt}->addForeignKey(\n"
                . "{$tttt}\$installer->getFkName('{$tableName}', '{$priColumnName}', '{$refTableName}', '{$refColumnName}'),\n"
                . "{$tttt}'{$priColumnName}',\n"
                . "{$tttt}\$installer->getTable('{$refTableName}'),\n"
                . "{$tttt}'{$refColumnName}',\n"
                . "{$tttt}{$onDelete}{$onUpdate}\n"
                . "{$ttt})\n";
        }
        $str .= "{$tt};\n{$tt}\$connection->createTable(\$table);
       ";

        $this->tables[$tableName] = $str;
    }
}
