<?php

namespace Swissup\Model;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class ModelGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Model/{$modelName}.php";
        return $filename;
    }

    public function __toString()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $str = '';
        $t = '    ';
        $tag = strtolower($moduleName . '_' . $modelName);
        $filename = $this->getFilename();

        $str .= "<?php namespace {$vendor}\\{$moduleName}\\Model;

use {$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface;
use Magento\Framework\DataObject\IdentityInterface;

/* {$filename} */

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
        \$this->_init('{$vendor}\\{$moduleName}\\Model\\ResourceModel\\{$modelName}');
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
            $const = 'self::' . strtoupper($column['name']);
            $str .= "{$comment}\n{$t}public function get{$name}()
    {
        return \$this->getData({$const});
    }\n\n";
        }

        $str .= "\n";
        foreach ($this->getColumns() as $column) {
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

        return $str;
    }
}
