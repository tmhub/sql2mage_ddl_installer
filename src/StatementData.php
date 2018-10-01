<?php

namespace Swissup;

class StatementData
{

    protected $tableName = '';

    protected $vendorName;

    protected $moduleName;

    protected $modelName;

    protected $primary = array();

    protected $columns = array();

    protected $indexes = array();

    protected $foreignKeys = array();

    protected $replacements = array();

    public function setTableName($tableName)
    {
        if (isset($this->replacements['tableName'][$tableName])) {
            $tableName = $this->replacements['tableName'][$tableName];
        }
        $this->tableName = $tableName;

        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @param mixed $vendorName
     *
     * @return self
     */
    public function setVendorName($vendorName)
    {
        if (isset($this->replacements['vendorName'][$vendorName])) {
            $vendorName = $this->replacements['vendorName'][$vendorName];
        }
        $this->vendorName = $vendorName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param mixed $moduleName
     *
     * @return self
     */
    public function setModuleName($moduleName)
    {
        if (isset($this->replacements['moduleName'][$moduleName])) {
            $moduleName = $this->replacements['moduleName'][$moduleName];
        }
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param mixed $modelName
     *
     * @return self
     */
    public function setModelName($modelName)
    {
        if (isset($this->replacements['modelName'][$modelName])) {
            $modelName = $this->replacements['modelName'][$modelName];
        }

        $this->modelName = $modelName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @param mixed $primary
     *
     * @return self
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     *
     * @return self
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param mixed $indexes
     *
     * @return self
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @param mixed $foreignKeys
     *
     * @return self
     */
    public function setForeignKeys($foreignKeys)
    {
        $this->foreignKeys = $foreignKeys;

        return $this;
    }

    /**
     * @param mixed $replacements
     *
     * @return self
     */
    public function setReplacements($replacements)
    {
        $this->replacements = $replacements;

        return $this;
    }
}
