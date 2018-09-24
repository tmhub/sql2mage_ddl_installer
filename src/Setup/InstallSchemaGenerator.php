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

        $str = "\n/* {$filename} */\n\$table = \$installer->getConnection()\n"
            . "{$t}->newTable(\$installer->getTable('{$tableName}'))\n";

        $arrayStart = $this->magentoVersion == 2 ? '[' : 'array(';
        $arrayEnd = $this->magentoVersion == 2 ? ']' : ')';

        foreach ($this->getColumns() as $column) {
            $str .= "{$t}->addColumn('{$column['name']}', {$column['type']}, {$column['length']}, {$arrayStart}\n" .
                    ($column['identity'] ? "{$t}{$t}'identity'  => true,\n" : '') .
                    ($column['unsigned'] ? "{$t}{$t}'unsigned'  => true,\n" : '') .
                    ($column['nullable'] !== null ?
                        "{$t}{$t}'nullable'  => " . ($column['nullable'] ? 'true' : 'false') . ",\n" : '') .
                    ($column['default'] !== false ? "{$t}{$t}'default'  => {$column['default']},\n" : '') .
                    // "'nullable'  => false,\n" .
                    ($column['primary'] ? "{$t}{$t}'primary'   => true,\n" : '') .
                    "{$t}{$arrayEnd}, '{$column['comment']}')\n"
            ;
        }
        foreach ($this->getIndexes() as $index) {
            $fields = $index['fields'];
            foreach ($fields as &$field) {
                $field = "'{$field}'";
            }
            $fields = implode(", ", $fields);

            $str .= "{$t}->addIndex(\$installer->getIdxName('{$index['table']}', {$arrayStart}{$fields}{$arrayEnd}),\n"
                . "{$t}{$t}{$arrayStart}{$fields}{$arrayEnd})\n";
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

            $str .= "{$t}->addForeignKey("
                . "\$installer->getFkName('{$tableName}', '{$priColumnName}', '{$refTableName}', '{$refColumnName}'),\n"
                . "{$t}{$t}'{$priColumnName}', \$installer->getTable('{$refTableName}'), '{$refColumnName}',\n"
                . "{$t}{$onDelete}{$onUpdate})\n";
        }
        $str .= ";\n\$installer->getConnection()->createTable(\$table);";

        return $str;
    }
}
