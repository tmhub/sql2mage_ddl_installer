<?php

namespace Swissup\Api;

include_once ROOT_DIR . '/src/GeneratorAbstract.php';

class RepositoryInterfaceGenerator extends \Swissup\GeneratorAbstract
{
    public function getFilename()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $filename = "{$vendor}/{$moduleName}/Api/{$modelName}RepositoryInterface.php";
        return $filename;
    }

    public function __toString()
    {
        $vendor = $this->getVendorName();
        $moduleName = $this->getModuleName();
        $modelName = $this->getModelName();

        $lowModelName = strtolower($modelName);
        $filename = $this->getFilename();
        $str = "<?php\nnamespace {$vendor}\\{$moduleName}\\Api;\n\n/* {$filename} */\n
/**
 * {$modelName} CRUD interface.
 * @api
 */\n
interface {$modelName}RepositoryInterface\n{\n
    /**
     * Save {$lowModelName}.
     *
     * @throws \\Magento\\Framework\\Exception\\LocalizedException
     *
     * @param \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface \${$lowModelName} The {$lowModelName}
     * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface
     */
    public function save(Data\\{$modelName}Interface \${$lowModelName});

    /**
     * Retrieve {$lowModelName} by {$lowModelName} id
     *
     * @throws \\Magento\\Framework\\Exception\\LocalizedException
     *
     * @param int \$id {$lowModelName} id.
     * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface
     */
    public function getById(\$id);

    /**
     * Retrieve {$lowModelName}s matching the specified criteria.
     *
     * @throws \\Magento\\Framework\\Exception\\LocalizedException
     *
     * @param \\Magento\\Framework\\Api\\SearchCriteriaInterface \$searchCriteria The search criteria
     * @return \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}SearchResultsInterface
     */
    public function getList(\\Magento\\Framework\\Api\\SearchCriteriaInterface \$searchCriteria);

    /**
     * Delete {$lowModelName}.
     *
     * @throws \\Magento\\Framework\\Exception\\LocalizedException
     *
     * @param \\{$vendor}\\{$moduleName}\\Api\\Data\\{$modelName}Interface \${$lowModelName} The {$lowModelName}
     * @return bool true on success
     */
    public function delete(Data\\{$modelName}Interface \${$lowModelName});

    /**
     * Delete {$lowModelName} by ID.
     *
     * @throws \\Magento\\Framework\\Exception\\NoSuchEntityException
     * @throws \\Magento\\Framework\\Exception\\LocalizedException
     *
     * @param int \$id The {$lowModelName} Id
     * @return bool true on success
     */
    public function deleteById(\$id);";

        $str .= "\n}";

        return $str;
    }
}
