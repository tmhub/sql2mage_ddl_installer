# sql2mage_ddl_installer
php tool for generating  magento ddl installer script

## usage
~~~bash
$ php -f sql2mage_installer.php 127.0.0.1 user password magento tm_helpmate_status

*********************************************************************************
CREATE TABLE `tm_helpmate_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) DEFAULT NULL COMMENT 'Name',
  `status` smallint(6) DEFAULT '0' COMMENT 'Status',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort order',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Statuses Table'
*********************************************************************************
/* Tm/Helpmate/Setup/InstallSchema.php */
<?php namespace Tm\Helpmate\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface , ModuleContextInterface )
    {
        $table = $installer->getConnection()
        ->newTable($installer->getTable('tm_helpmate_status'))
        ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, [
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ], 'Id')
        ->addColumn('name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [
            'nullable'  => true,
            'default'  => NULL,
        ], 'Name')
        ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, [
            'default'  => 0,
        ], 'Status')
        ->addColumn('sort_order', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5, [
            'unsigned'  => true,
            'nullable'  => false,
            'default'  => 0,
        ], 'Sort Order')
        ;
        $installer->getConnection()->createTable($table);
    }
}


*********************************************************************************
~~~

## how works

- SHOW CREATE TABLE {$tableName}
- convert to mage installer script

