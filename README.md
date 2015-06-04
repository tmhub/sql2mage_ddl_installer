# sql2mage_ddl_installer
php tool for generating  magento ddl installer script

#usage 
~~~bash
user@user:/var/www/sql2mage_ddl_installer$ php -f sql2mage_ddl_installer.php  127.0.0.1 root front123 magento  tm_helpmate_status  

*********************************************************************************
CREATE TABLE `tm_helpmate_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) DEFAULT NULL COMMENT 'Name',
  `status` smallint(6) DEFAULT '0' COMMENT 'Status',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort order',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Statuses Table'
*********************************************************************************
$table = $installer->getConnection()
    ->newTable($installer->getTable('tm_helpmate_status'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => true,
        'default'  => NULL,
    ), 'Name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
        'default'  => 0,
    ), 'Status')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'  => 0,
    ), 'Sort Order')
;
*********************************************************************************
~~~

#how works 

- SHOW CREATE TABLE {$tableName}
- convert to mage installer script


