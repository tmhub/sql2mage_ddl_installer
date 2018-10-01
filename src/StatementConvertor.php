<?php

namespace Swissup;

include_once ROOT_DIR . '/src/StatementData.php';
include_once ROOT_DIR . '/src/GeneratorAbstract.php';
include_once ROOT_DIR . '/src/Api/Data/InterfaceGenerator.php';
include_once ROOT_DIR . '/src/Api/Data/SearchResultsInterfaceGenerator.php';
include_once ROOT_DIR . '/src/Api/RepositoryInterfaceGenerator.php';
include_once ROOT_DIR . '/src/Model/ModelGenerator.php';
include_once ROOT_DIR . '/src/Model/ResourceModelGenerator.php';
include_once ROOT_DIR . '/src/Model/ResourceModel/CollectionGenerator.php';
include_once ROOT_DIR . '/src/Setup/InstallSchemaGenerator.php';

// class SQLCreateStatemant2Mage2DdlTableConvertor
class StatementConvertor extends GeneratorAbstract
{

    // protected $_sql;

    // protected $tableName = '';

    // protected $vendorName;

    // protected $moduleName;

    // protected $modelName;

    // protected $primary = array();

    // protected $columns = array();

    // protected $indexes = array();

    // protected $foreignKeys = array();

    protected $magentoVersion = 2;

    protected $generators = [];

    public function __construct($version = 2)
    {
        $this->statementData = new \Swissup\StatementData();
        $this->magentoVersion = (int) $version;
    }

    public function parse($sql, $version = 2)
    {
        // $this->statementData = new \Swissup\StatementData();
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
        $this->setTableName($tableName);

        if (substr_count($tableName, "_") > 2) {
            list($vendor, $moduleName, $modelName) = explode('_', $tableName, 3);
        } elseif (substr_count($tableName, "_") == 2) {
            list($vendor, $moduleName, $modelName) = explode('_', $tableName, 3);
        } else {
            list($vendor, $moduleName) = explode('_', $tableName, 2);
            $modelName = $moduleName;
        }
        $vendor = ucfirst($vendor);
        $this->setVendorName($vendor);
        $moduleName = ucfirst($moduleName);
        $this->setModuleName($moduleName);

        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $modelName)));
        $this->setModelName($modelName);

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

        $this->setPrimary($_primary);
        // $this->columns = $columns;

        $columnsInfo = array();
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
                    $default = 'null';
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

            $columnsInfo[] = array(
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

        $this->setColumns($columnsInfo);

        $indexesInfo = [];
        // $this->_keys = $keys;
        foreach ($keys as $key) {
            $key = str_replace(array("'", "\"", "`"), '', $key);
            list(, $fields) = explode("(", $key);
            $fields = explode(" ", str_replace(array(",", "(", ")"), "", $fields));
            $fields = array_filter($fields);

            $indexesInfo[] = array(
                'table'  => $tableName,
                'fields' => $fields,
            );
        }
        $this->setIndexes($indexesInfo);

        $foreignKeysInfo = [];
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

            $foreignKeysInfo[] = array(
                'table'            => $tableName,
                'column'           => $priColumnName,
                'reference_table'  => $refTableName,
                'reference_column' => $refColumnName,
                'on_update'        => $onUpdate,
                'on_delete'        => $onDelete,
            );
        }
        $this->setForeignKeys($foreignKeysInfo);
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

    public function getGenerator($className)
    {
        if (!isset($this->generators[$className])) {
            $this->generators[$className] = new $className();
        }

        $generator = $this->generators[$className];

        $generator->setStatementData($this->getStatementData());

        return $generator;
    }

    public function __toString()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();
        $files = array();

        $classes = [
            \Swissup\Api\Data\InterfaceGenerator::class,
            // \Swissup\Api\Data\SearchResultsInterfaceGenerator::class,
            // \Swissup\Api\RepositoryInterfaceGenerator::class,
            \Swissup\Model\ModelGenerator::class,
            \Swissup\Model\ResourceModelGenerator::class,
            \Swissup\Model\ResourceModel\CollectionGenerator::class,
            \Swissup\Setup\InstallSchemaGenerator::class,
        ];

        foreach ($classes as $class) {
            $generator = $this->getGenerator($class);
            $files[$generator->getFilename()] = (string) $generator;
        }

        $dir = ROOT_DIR . '/tmp/';
        if (!file_exists($dir) && !is_dir($dir)) {
            mkdir($dir);
        }

        foreach ($files as $filename => $content) {
            $filename = $dir . $filename;

            $dirname = dirname($filename);
            if (!file_exists($dirname) && !is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
            file_put_contents($filename, $content);
        }

        // $str = '';
        // print_r(array_keys($files));
        // return '';
        return implode("\n-------------------\n", $files);
    }
}
