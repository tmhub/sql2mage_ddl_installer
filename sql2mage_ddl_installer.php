<?php

// namespace Tmhub;

class SQLCreateStatemant2Mage2DdlTableConvertor
{

    // protected $_sql;

    protected $tableName = '';

    protected $vendorName;

    protected $moduleName;

    protected $modelName;

    protected $primary = array();

    protected $columns = array();

    protected $indexes = array();

    protected $foreignKeys = array();

    protected $magentoVersion = 2;

    public function __construct($sql, $version = 2)
    {
        $this->magentoVersion = (int) $version;

        $sql = str_replace(",\n", ",,", $sql);
        $sql = str_replace(array("\n", "  ", "\t"), " ", $sql);
        $parts = explode(",,", $sql);

        list($tableName, $parts0) = explode("(", $parts[0], 2);
        $parts[0] = $parts0;
        // array_unshift($parts, $tableName);

        list($partLast, $engine) = explode(") ENGINE", end($parts), 2);
        array_pop($parts);

        array_push($parts, $partLast);
        $engine = "ENGINE" . $engine;
        // array_push($parts, $engine);

        $tableName = str_replace(
            array("CREATE", 'TABLE', ' ', '`', "'", '"'),
            '',
            $tableName
        );
        $this->tableName = $tableName;

        if (substr_count($tableName, "_") == 2) {
            list($vendor, $moduleName, $modelName) = explode('_', $tableName, 3);
        } else {
            list($vendor, $moduleName) = explode('_', $tableName, 2);
            $modelName = $moduleName;
        }
        $this->vendorName = ucfirst($vendor);
        $this->moduleName = ucfirst($moduleName);
        $this->modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $modelName)));

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

        $this->primary = $_primary;
        // $this->columns = $columns;

        foreach ($columns as $column) {
            // Zend_Debug::dump($column);
            $column = trim($column, "\n\t ");

            @list($columnName, $_column) = explode(" ", $column, 2);
            $columnName = str_replace(array("'", "\"", "`"), '', $columnName);

            @list($type, $_column) = explode(" ", $_column, 2);

            @list($type, $length) = explode("(", trim($type, ")"));

            if (empty($length)) {
                $length = 'null';
            }
            $_type = $type;
            $type = $this->getType($type);

            $identity = $unsigned = $isprimary = $default = false;
            $nullable = null;

            $unsigned = strstr($column, " UNSIGNED ") || strstr($column, " unsigned ");
            $identity = strstr($column, "AUTO_INCREMENT");
            $isprimary = in_array($columnName, $_primary);

            if (strstr($column, " DEFAULT ")) {
                list(, $default) = explode(" DEFAULT ", $column, 2);
                $default = explode(" ", $default);
                $default = array_shift($default);
                $default = str_replace(array("'", "\"", "`"), '', $default);

                if ($default == '') {
                    $default = "''";
                }

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

            $this->columns[] = array(
                'name'     => $columnName,
                'type'     => $type,
                '_type'    => $_type,
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
            $fields = array_filter($fields);

            $this->indexes[] = array(
                'table'  => $tableName,
                'fields' => $fields,
            );

        }
        // $this->foreignKeys = $fKeys;
        foreach ($fKeys as $key) {
            $key = str_replace(array("'", "\"", "`"), '', $key);

            @list($key, $onUpdate) = explode('ON UPDATE ', $key);
            $onUpdate = trim($onUpdate);
            $onUpdate = $this->getAction($onUpdate);

            @list($key, $onDelete) = explode('ON DELETE ', $key);
            $onDelete = trim($onDelete);
            $onDelete = $this->getAction($onDelete);


            list($key, $reference) = explode('REFERENCES ', $key);
            // Zend_Debug::dump($reference);
            $reference = str_replace(array(",", "(", ")"), "", $reference);
            list($refTableName, $refColumnName) = explode(" ", $reference, 2);
            $refColumnName = trim($refColumnName);

            list($key, $priColumnName) = explode('FOREIGN KEY ', $key);
            $priColumnName = str_replace(array(",", "(", ")"), "", $priColumnName);
            $priColumnName = trim($priColumnName);

            $this->foreignKeys[] = array(
                'table'            => $tableName,
                'column'           => $priColumnName,
                'reference_table'  => $refTableName,
                'reference_column' => $refColumnName,
                'on_update'        => $onUpdate,
                'on_delete'        => $onDelete,
            );
        }
    }

    protected function getAction($action)
    {
        if (empty($action)) {
            $action = 'cascade';
        }
        $prefix = '\Magento\Framework\DB\Ddl\Table';

        if ($this->magentoVersion != 2) {
            $prefix = 'Varien_Db_Ddl_Table';
        }
        return "{$prefix}::ACTION_" . strtoupper(str_replace(" ", "_", $action));
    }

    protected function getType($type)
    {
        if ($type === 'int' || $type === 'mediumint') {
            $type = 'integer';
        }
        if ($type === 'tinyint' && $this->magentoVersion == 2) {
            $type = 'smallint';
        }
        if ($type === 'varchar' && $this->magentoVersion == 2) {
            $type = 'text';
        }
        $prefix = '\Magento\Framework\DB\Ddl\Table';
        if ($this->magentoVersion != 2) {
            $prefix = 'Varien_Db_Ddl_Table';
        }
        return "{$prefix}::TYPE_" . strtoupper($type);
    }

    public function __toString()
    {
        $t = '    ';
        $filename = "{$this->vendorName}/{$this->moduleName}/" . ($this->magentoVersion == 2 ?
            'Setup/InstallSchema.php' :
            strtolower("sql/{$this->vendorName}_{$this->moduleName}_setup/mysql4-install-1.0.0.php"));

        $str = "\n/* {$filename} */\n\$table = \$installer->getConnection()\n"
            . "{$t}->newTable(\$installer->getTable('{$this->tableName}'))\n";

        $arrayStart = $this->magentoVersion == 2 ? '[' : 'array(';
        $arrayEnd = $this->magentoVersion == 2 ? ']' : ')';

        foreach ($this->columns as $column) {
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
        foreach ($this->indexes as $index) {
            $fields = $index['fields'];
            foreach ($fields as &$field) {
                $field = "'{$field}'";
            }
            $fields = implode(", ", $fields);

            $str .= "{$t}->addIndex(\$installer->getIdxName('{$index['table']}', {$arrayStart}{$fields}{$arrayEnd}),\n"
                . "{$t}{$t}{$arrayStart}{$fields}{$arrayEnd})\n";
        }

        $tableName = $this->tableName;

        foreach ($this->foreignKeys as $key) {
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

    public function generateItnterface()
    {
        $vendor = $this->vendorName;
        $moduleName = $this->moduleName;
        $modelName = $this->modelName;
        //$interfacename = str_replace('_', '\\', $this->tableName);
        $_str = array();
        $filename = "{$vendor}/{$moduleName}/Api/Data/{$modelName}Interface.php";
        $str = "\n/* {$filename} */\n<?php\nnamespace {$vendor}\\{$moduleName}\\Api\\Data;\n\n\n"
            . "interface {$modelName}Interface\n{\n";
        $t = '    ';
        foreach ($this->columns as $column) {
            $_column = $column['name'];
            $str .= "{$t}const " . strtoupper($_column) . ' = ' . "'{$_column}';\n";
        }
        $str .= "\n";
        foreach ($this->columns as $column) {
            $name   = str_replace(' ', '', ucwords(str_replace('_', ' ', $column['name'])));
            $type    = 'string';
            if (false != strstr($column['_type'], 'int')) {
                $type = 'int';
            }
            $comment = "{$t}/**\n"
                . "{$t} * Get {$column['name']}\n"
                . "{$t} * \n"
                . "{$t} * @return {$type}\n"
                . "{$t} */";
            $str .= "{$comment}\n{$t}public function get{$name}();\n\n";
        }

        $str .= "\n";
        foreach ($this->columns as $column) {
            $name    = str_replace(' ', '', ucwords(str_replace('_', ' ', $column['name'])));
            $param   = '$' . lcfirst($name);
            $type    = 'string';
            if (false != strstr($column['_type'], 'int')) {
                $type = 'int';
            }
            $comment = "{$t}/**\n"
                . "{$t} * Set {$column['name']}\n"
                . "{$t} * \n"
                . "{$t} * @param {$type} {$param} \n"
                . "{$t} * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface\n"
                . "{$t} */";

            $str .= "{$comment}\n{$t}public function set{$name}({$param});\n\n";
        }

        $str .= "\n}";
        // return $str;
        $_str[$filename] = $str;

        $filename = "{$vendor}/{$moduleName}/Api/Data/{$modelName}SearchResultsInterface.php";
        $str = "\n/* {$filename} */\n<?php\nnamespace {$vendor}\\{$moduleName}\\Api\\Data;\n
use {$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface;\n\n"
            . "interface {$modelName}SearchResultsInterface\n{\n";
        $t = '    ';

        $str .= $t . "/**
     * Get list.
     *
     * @return {$modelName}Interface[]
     */
    public function getItems();

    /**
     * Set list.
     *
     * @param {$modelName}Interface[] \$items
     * @return \$this
     */
    public function setItems(array \$items);";
        $str .= "\n}";
        $_str[$filename] = $str;

        $str = '';
        $tag = strtolower($moduleName . '_' . $modelName);
        $filename = "{$vendor}/{$moduleName}/Model/{$modelName}.php";
        $str .= "\n/* {$filename} */\n<?php namespace {$vendor}\\{$moduleName}\\Model;

use {$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface;
use Magento\Framework\DataObject\IdentityInterface;

class {$modelName} extends \\Magento\\Framework\\Model\\AbstractModel
    implements {$modelName}Interface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = '{$tag}';

    /**
     * @var string
     */
    protected \$_cacheTag = '{$tag}';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected \$_eventPrefix = '{$tag}';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        \$this->_init('{$vendor}\\{$moduleName}\\Model\\Resource\\{$modelName}');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . \$this->getId()];
    }
    ";
        $str .= "\n";
        foreach ($this->columns as $column) {
            $name   = str_replace(' ', '', ucwords(str_replace('_', ' ', $column['name'])));
            $type    = 'string';
            if (false != strstr($column['_type'], 'int')) {
                $type = 'int';
            }
            $comment = "{$t}/**\n"
                . "{$t} * Get {$column['name']}\n"
                . "{$t} * \n"
                . "{$t} * @return {$type}\n"
                . "{$t} */";
            $const = 'self::' . strtoupper($column['name']);
            $str .= "{$comment}\n{$t}public function get{$name}()
    {
        return \$this->getData({$const});
    }\n\n";
        }

        $str .= "\n";
        foreach ($this->columns as $column) {
            $name    = str_replace(' ', '', ucwords(str_replace('_', ' ', $column['name'])));
            $param   = '$' . lcfirst($name);
            $const   = 'self::' . strtoupper($column['name']);
            $type    = 'string';
            if (false != strstr($column['_type'], 'int')) {
                $type = 'int';
            }
            $comment = "\n\n{$t}/**\n"
                . "{$t} * Set {$column['name']}\n"
                . "{$t} * \n"
                . "{$t} * @param {$type} {$param} \n"
                . "{$t} * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface\n"
                . "{$t} */";

            $str .= "{$comment}\n{$t}public function set{$name}({$param})
    {
        return \$this->setData({$const}, {$param});
    }";
        }

        $str .= "\n}";
        $_str[$filename] = $str;
        $str = '';
        // generate resource

        $primaryKey = end($this->primary);
        $filename = "{$vendor}/{$moduleName}/Model/Resource/{$modelName}.php";
        $str .= "\n/* {$filename} */\n<?php
namespace {$vendor}\\{$moduleName}\\Model\\Resource;

/**
 * {$moduleName} {$modelName} mysql resource
 */
class {$modelName} extends \\Magento\\Framework\\Model\\Resource\\Db\\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        \$this->_init('{$this->tableName}', '{$primaryKey}');
    }
";
        $str .= "\n}";
        $_str[$filename] = $str;
        // $str = '';
        // print_r(array_keys($_str));
        return implode('', $_str);
    }
}

if (6 > count($argv)) {
    echo "Usage: php -f {$argv[0]} host user password database table magento_version\n";
    return;
}

$host      = $argv[1];
$username  = $argv[2];
$password  = $argv[3];
$database  = $argv[4];
$tableName = $argv[5];
$magentoVersion = isset($argv[6]) ? $argv[6] : 1;

$link = mysqli_connect($host, $username, $password);
mysqli_select_db($link, $database);
$query = "SHOW CREATE TABLE {$tableName}";

$result = mysqli_query($link, $query);

$_sql = array();
$sql = '';
while ($line = mysqli_fetch_array($result)) {
    foreach ($line as $value) {
        $_sql[] = $value;
    }
}
mysqli_close($link);

$sql = $_sql[2];
$line = "***************************";
$line .= $line . $line;
echo "\n{$line}\n" . $sql . "\n{$line}\n";

$convertor = new SQLCreateStatemant2Mage2DdlTableConvertor($sql, $magentoVersion);

echo $convertor->generateItnterface();
echo "\n{$line}\n";
echo $convertor;


echo "\n{$line}\n";
