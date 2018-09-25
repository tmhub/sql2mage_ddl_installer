<?php

namespace Swissup\Api\Data;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class SearchResultsInterfaceGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Api/Data/{$modelName}SearchResultsInterface.php";
        return $filename;
    }

    public function __toString()
    {

        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();
        $filename = $this->getFilename();

        $str = "<?php\nnamespace {$vendor}\\{$moduleName}\\Api\\Data;\n\n/* {$filename} */\n"
            . "interface {$modelName}SearchResultsInterface\n{\n";
        $t = '    ';

        $str .= $t . "/**
     * Get list.
     *
     * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface[]
     */
    public function getItems();

    /**
     * Set list.
     *
     * @param \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface[] \$items
     * @return \$this
     */
    public function setItems(array \$items);";
        $str .= "\n}";

        return $str;
    }
}
