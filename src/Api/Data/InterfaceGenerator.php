<?php

namespace Swissup\Api\Data;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class InterfaceGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Api/Data/{$modelName}Interface.php";
        return $filename;
    }

    public function __toString()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = $this->getFilename();
        $str = "<?php\nnamespace {$vendor}\\{$moduleName}\\Api\\Data;\n\n/* {$filename} */\n"
            . "interface {$modelName}Interface\n{\n";
        $t = '    ';
        foreach ($this->getColumns() as $column) {
            $_column = $column['name'];
            $str .= "{$t}const " . strtoupper($_column) . ' = ' . "'{$_column}';\n";
        }
        $str .= "\n";
        foreach ($this->getColumns() as $column) {
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
        foreach ($this->getColumns() as $column) {
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

        return $str;
    }
}
