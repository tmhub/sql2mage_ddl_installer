<?php

class SQLCreateStatemant2MageDdlTableConvertor {

    // protected $_sql;

    protected $_tableName = '';

    protected $_primary = array();

    protected $_columns = array();

    protected $_indexes = array();

    protected $_foreignKeys = array();

    public function __construct($sql)
    {
        $sql = str_replace(array("\n", "  ", "\t"), " ", $sql);
        $parts = explode(",", $sql);

        list($tableName, $parts0) = explode("(", $parts[0], 2);
        $parts[0] = $parts0;
        // array_unshift($parts, $tableName);

        list($partLast, $engine) = explode(") ENGINE", end($parts), 2);
        array_pop($parts);

        array_push($parts, $partLast);
        $engine = "ENGINE" . $engine;
        // array_push($parts, $engine);

        $tableName = str_replace(
            array("CREATE", 'TABLE', ' ', '`', "'", '"'), '', $tableName
        );
        $this->_tableName = $tableName;
        // Zend_Debug::dump($tableName);

        // Zend_Debug::dump($parts);

        $columns = $keys = $fKeys = $primary = array();
        foreach ($parts as $part) {

            if (strstr($part, "PRIMARY KEY")) {

                $primary[] = $part;
                continue;
            }

            if (strstr($part, "FOREIGN KEY")) {

                $fKeys[] = $part;
                continue;
            }

            if (strstr($part, "KEY")) {

                $keys[] = $part;
                continue;
            }
            $columns[] = $part;
        }
        // $primary[] = "PRIMARY KEY (sd, dd)";

        $_primary = array();
        foreach ($primary as $p) {
            $p = str_replace(array("(", ")", "PRIMARY KEY", "`" , " "), "", $p);
            $_primary = array_merge(explode(",", $p), $_primary);
        }

        $this->_primary = $_primary;
        // $this->_columns = $columns;

        foreach ($columns as $column) {
            // Zend_Debug::dump($column);
            $column = trim($column, "\n\t ");

            list($columnName, $_column) = explode(" ", $column, 2);
            $columnName = str_replace(array("'", "\"", "`"), '', $columnName);

            @list($type, $_column) = explode(" ", $_column, 2);

            @list($type, $length) = explode("(", trim($type, ")"));

            if (empty($length)) {
                $length = 'null';
            }
            $type = $this->_getType($type);

            $identity = $unsigned = $isprimary = $default = false;
            $nullable = null;

            $unsigned = strstr($column, " UNSIGNED ") || strstr($column, " unsigned ");
            $identity = strstr($column, "AUTO_INCREMENT");
            $isprimary = in_array($columnName, $_primary);

            if (strstr($column, " DEFAULT ")) {
                list(, $default) = explode(" DEFAULT ", $column, 2);
                $default = array_shift(explode(" ", $default));
                $default = str_replace(array("'", "\"", "`"), '', $default);
                if ($default == 'NULL' || $default == 'null') {
                    $default = 'NULL';
                    $nullable = true;
                }
            }

            list($_column,) = explode(" DEFAULT ", $column, 2);
            if (strstr($_column, "NOT NULL")) {
                $nullable = false;
                // Zend_Debug::dump($default);
            } elseif (strstr($_column, "NULL")) {
                $nullable = true;
            }
            $comment = ucwords(str_replace("_", " ", $columnName));

            $this->_columns[] = array(
                'name'     => $columnName,
                'type'     => $type,
                'length'   => $length,
                'identity' => $identity,
                'unsigned' => $unsigned,
                'nullable' => $nullable,
                'default'  => $default,
                'primary'  => $isprimary,
                'comment'  => $comment
            );
        }

        // $this->_keys = $keys;
        foreach ($keys as $key) {
            $key = str_replace(array("'", "\"", "`"), '', $key);
            list(, $fields) = explode("(", $key);
            $fields = explode(" ", str_replace(array(",", "(", ")"), "", $fields));


            $this->_indexes[] = array(
                'table'  => $tableName,
                'fields' => $fields,
            );

        }
        // $this->_foreignKeys = $fKeys;
        foreach ($fKeys as $key) {
            $key = str_replace(array("'", "\"", "`"), '', $key);

            list($key, $onUpdate) = explode('ON UPDATE ', $key);
            $onUpdate = trim($onUpdate);
            $onUpdate = $this->_getAction($onUpdate);

            list($key, $onDelete) = explode('ON DELETE ', $key);
            $onDelete = trim($onDelete);
            $onDelete = $this->_getAction($onDelete);


            list($key, $reference) = explode('REFERENCES ', $key);
            // Zend_Debug::dump($reference);
            $reference = str_replace(array(",", "(", ")"), "", $reference);
            list($refTableName, $refColumnName) = explode(" ", $reference, 2);
            $refColumnName = trim($refColumnName);

            list($key, $priColumnName) = explode('FOREIGN KEY ', $key);
            $priColumnName = str_replace(array(",", "(", ")"), "", $priColumnName);
            $priColumnName = trim($priColumnName);

            $this->_foreignKeys[] = array(
                'table'            => $tableName,
                'column'           => $priColumnName,
                'reference_table'  => $refTableName,
                'reference_column' => $refColumnName,
                'on_update'        => $onUpdate,
                'on_delete'        => $onDelete,
            );
        }
    }

    protected function _getAction($action)
    {
        return 'Varien_Db_Ddl_Table::ACTION_' . strtoupper(str_replace(" ", "_", $action));
    }

    protected function _getType($type)
    {
        if ($type === 'int') {
            $type = 'integer';
        }
        return 'Varien_Db_Ddl_Table::TYPE_' . strtoupper($type);
    }

    public function __toString()
    {
        $t = '    ';
        $str = "\$table = \$installer->getConnection()\n"
            . "{$t}->newTable(\$installer->getTable('{$this->_tableName}'))\n";

        foreach ($this->_columns as $column) {
            $str .= "{$t}->addColumn('{$column['name']}', {$column['type']}, {$column['length']}, array(\n" .
                    ($column['identity'] ? "{$t}{$t}'identity'  => true,\n" : '') .
                    ($column['unsigned'] ? "{$t}{$t}'unsigned'  => true,\n" : '') .
                    ($column['nullable'] !== null ?
                        "{$t}{$t}'nullable'  => " . ($column['nullable'] ? 'true' : 'false') . ",\n" : '') .
                    ($column['default'] !== false ? "{$t}{$t}'default'  => {$column['default']},\n" : '') .
                    // "'nullable'  => false,\n" .
                    ($column['primary'] ? "{$t}{$t}'primary'   => true,\n" : '') .
                    "{$t}), '{$column['comment']}')\n"
            ;
        }
        foreach ($this->_indexes as $index) {
            $fields = $index['fields'];
            foreach ($fields as &$field) {
                $field = "'{$field}'";
            }
            $fields = implode(", ", $fields);

            $str .= "{$t}->addIndex(\$installer->getIdxName('{$index['table']}', array({$fields})),\n"
                . "{$t}{$t}array({$fields}))\n";
        }

        $tableName = $this->_tableName;

        foreach ($this->_foreignKeys as $key) {
            $priColumnName = $key['column'];
            $refTableName  = $key['reference_table'];
            $refColumnName = $key['reference_column'];
            $onUpdate      = $key['on_update'];
            $onDelete      = $key['on_delete'];

            $str .= "{$t}->addForeignKey(\$installer->getFkName('{$tableName}', '{$priColumnName}', '{$refTableName}', '{$refColumnName}'),\n"
                . "{$t}{$t}'{$priColumnName}', \$installer->getTable('{$refTableName}'), '{$refColumnName}',\n"
                . "{$t}{$onDelete}, {$onUpdate})\n";
        }
        $str .= ";\n\$installer->getConnection()->createTable(\$table);";
        return $str;
    }
}
    if (6 != count($argv)) {
        echo "Usage: php -f {$argv[0]} host user password database table\n";
        return;
    }

    $host      = $argv[1];
    $username  = $argv[2];
    $password  = $argv[3];
    $database  = $argv[4];
    $tableName = $argv[5];

    $link = mysql_connect($host, $username, $password);
    mysql_select_db($database);
    $query = "SHOW CREATE TABLE {$tableName}";

    $result = mysql_query($query);

    $_sql = array();
    $sql = '';
    while ($line = mysql_fetch_array($result))
    {
        foreach ($line as $value)
        {
            $_sql[] = $value;
        }
    }
    mysql_close($link);

    $sql = $_sql[2];
    $line = "***************************";
    $line .= $line . $line;
    echo "\n{$line}\n" . $sql . "\n{$line}\n";

//     $sql = "CREATE TABLE `tm_helpmate_theard` (
//   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//   `ticket_id` int(11) unsigned NOT NULL,
//   `message_id` varchar(255) NOT NULL,
//   `created_at` datetime DEFAULT NULL,
//   `text` text,
//   `file` varchar(255) DEFAULT NULL,
//   `user_id` int(10) unsigned DEFAULT NULL,
//   `status` tinyint(1) NOT NULL DEFAULT '1',
//   `priority` tinyint(1) NOT NULL DEFAULT '1',
//   `department_id` int(11) unsigned NOT NULL,
//   `enabled` tinyint(1) NOT NULL DEFAULT '1',
//   PRIMARY KEY (`id`),
//   KEY `FK_LINK_USER_HELPMATE_THEARD` (`user_id`),
//   KEY `FK_LINK_DEPARTMENT_HELPMATE_THEARD` (`department_id`),
//   KEY `FK_LINK_TICKET_HELPMATE_THEARD` (`ticket_id`),
//   CONSTRAINT `FK_LINK_DEPARTMENT_HELPMATE_THEARD` FOREIGN KEY (`department_id`) REFERENCES `tm_helpmate_department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
//   CONSTRAINT `FK_LINK_TICKET_HELPMATE_THEARD` FOREIGN KEY (`ticket_id`) REFERENCES `tm_helpmate_ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
//   CONSTRAINT `FK_LINK_USER_HELPMATE_THEARD` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


    $convertor = new SQLCreateStatemant2MageDdlTableConvertor($sql);

    echo $convertor;

    echo "\n{$line}\n"
?>