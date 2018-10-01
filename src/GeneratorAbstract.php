<?php

namespace Swissup;

abstract class GeneratorAbstract
{

    protected $statementData;

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->statementData->getTableName();
    }

    /**
     * @param mixed $tableName
     *
     * @return self
     */
    public function setTableName($tableName)
    {
        $this->statementData->setTableName($tableName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->statementData->getVendorName();
    }

    /**
     * @param mixed $vendorName
     *
     * @return self
     */
    public function setVendorName($vendorName)
    {
        $this->statementData->setVendorName($vendorName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->statementData->getModuleName();
    }

    /**
     * @param mixed $moduleName
     *
     * @return self
     */
    public function setModuleName($moduleName)
    {
        $this->statementData->setModuleName($moduleName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->statementData->getModelName();
    }

    /**
     * @param mixed $modelName
     *
     * @return self
     */
    public function setModelName($modelName)
    {
        $this->statementData->setModelName($modelName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimary()
    {
        return $this->statementData->getPrimary();
    }

    /**
     * @param mixed $primary
     *
     * @return self
     */
    public function setPrimary($primary)
    {
        $this->statementData->setPrimary($primary);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->statementData->getColumns();
    }

    /**
     * @param mixed $columns
     *
     * @return self
     */
    public function setColumns($columns)
    {
        $this->statementData->setColumns($columns);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIndexes()
    {
        return $this->statementData->getIndexes();
    }

    /**
     * @param mixed $indexes
     *
     * @return self
     */
    public function setIndexes($indexes)
    {
        $this->statementData->setIndexes($indexes);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignKeys()
    {
        return $this->statementData->getForeignKeys();
    }

    /**
     * @param mixed $foreignKeys
     *
     * @return self
     */
    public function setForeignKeys($foreignKeys)
    {
        $this->statementData->setForeignKeys($foreignKeys);

        return $this;
    }

    /**
     * @param mixed $replacements
     *
     * @return self
     */
    public function setReplacements($replacements)
    {
        $this->statementData->setReplacements($replacements);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatementData()
    {
        return $this->statementData;
    }

    /**
     * @param mixed $statementData
     *
     * @return self
     */
    public function setStatementData($statementData)
    {
        $this->statementData = $statementData;

        return $this;
    }
}
