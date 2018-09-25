<?php

namespace Swissup\Setup;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class InstallSchemaGenerator extends \Swissup\GeneratorAbstract
{
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

        $t = '    ';
        $filename = $this->getFilename();

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
    {\n{$t}{$t}\$table = \$installer->getConnection()\n"
            . "{$t}{$t}->newTable(\$installer->getTable('{$tableName}'))\n";

        $arrayStart = $this->magentoVersion == 2 ? '[' : 'array(';
        $arrayEnd = $this->magentoVersion == 2 ? ']' : ')';

        foreach ($this->getColumns() as $column) {
            $str .= "{$t}{$t}->addColumn('{$column['name']}', {$column['type']}, {$column['length']}, {$arrayStart}\n" .
                    ($column['identity'] ? "{$t}{$t}{$t}'identity'  => true,\n" : '') .
                    ($column['unsigned'] ? "{$t}{$t}{$t}'unsigned'  => true,\n" : '') .
                    ($column['nullable'] !== null ?
                        "{$t}{$t}{$t}'nullable'  => " . ($column['nullable'] ? 'true' : 'false') . ",\n" : '') .
                    ($column['default'] !== false ? "{$t}{$t}{$t}'default'  => {$column['default']},\n" : '') .
                    // "'nullable'  => false,\n" .
                    ($column['primary'] ? "{$t}{$t}{$t}'primary'   => true,\n" : '') .
                    "{$t}{$t}{$arrayEnd}, '{$column['comment']}')\n"
            ;
        }
        foreach ($this->getIndexes() as $index) {
            $fields = $index['fields'];
            foreach ($fields as &$field) {
                $field = "'{$field}'";
            }
            $fields = implode(", ", $fields);

            $str .= "{$t}{$t}->addIndex(\$installer->getIdxName('{$index['table']}', {$arrayStart}{$fields}{$arrayEnd}),\n"
                . "{$t}{$t}{$t}{$arrayStart}{$fields}{$arrayEnd})\n";
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

            $str .= "{$t}{$t}->addForeignKey("
                . "\$installer->getFkName('{$tableName}', '{$priColumnName}', '{$refTableName}', '{$refColumnName}'),\n"
                . "{$t}{$t}{$t}'{$priColumnName}', \$installer->getTable('{$refTableName}'), '{$refColumnName}',\n"
                . "{$t}{$t}{$onDelete}{$onUpdate})\n";
        }
        $str .= "{$t}{$t};\n{$t}{$t}\$installer->getConnection()->createTable(\$table);
    }
}
";
        return $str;
    }
}
