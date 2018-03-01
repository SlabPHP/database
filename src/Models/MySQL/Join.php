<?php
/**
 * Class Join
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Models\MySQL;

class Join
{
    /**
     * @var \Slab\Database\Models\MySQL\Loader
     */
    private $loader;

    /**
     * Class name of object we'll be creating
     *
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * Build a join for a data object class
     *
     * @param $className
     * @param $tableAlias
     * @param $destinationFieldName
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function __construct($className, $tableAlias, $destinationFieldName)
    {
        if (empty($className) || !class_exists($className))
        {
            throw new \Slab\Database\Exceptions\Mapping("Invalid class " . $className . " specified for Join.");
        }

        $loaderName = $className::DATA_LOADER;

        $this->fieldName = $destinationFieldName;
        $this->alias = $tableAlias;
        $this->className = $className;
        $this->prefix = $this->alias . '_';

        $this->loader = new $loaderName();
        $this->loader
            ->setDestructiveMapping(true)
            ->setFieldPrefix($this->prefix)
            ->setAssignNonMappedColumns(false)
            ->setThrowIfEmptyId(true);
    }

    /**
     * Resolve data by splicing in the completed object
     *
     * @param $databaseResult
     */
    public function resolve($databaseResult)
    {
        $className = $this->className;

        try {
            $object = new $className();

            $this->loader
                ->mapObjectWithOptions($object, $databaseResult);

            $databaseResult->{$this->fieldName} = $object;
        } catch (\Exception $e) {
            //This is fine, just an empty join
            echo $e->getMessage();
        }
    }

    /**
     * Get mapping sql from the object
     *
     * @return string
     */
    public function getMappingSQL()
    {
        return $this->loader->getColumnsAsSQL($this->alias, $this->prefix);
    }

    /**
     * Get table sql from the object
     *
     * @return string
     */
    public function getTable()
    {
        return $this->loader->getTable($this->alias);
    }

    /**
     * Print out Join SQL
     *
     * @param $foreignKeyColumn
     * @return string
     */
    public function joinOn($foreignKeyColumn)
    {
        return 'join ' . $this->getTable() . ' on ' . $this->loader->getPrimaryKey($this->alias) . ' = ' . $foreignKeyColumn . ' ';
    }

    /**
     * Print out left join sql
     *
     * @param $foreignKeyColumn
     * @return string
     */
    public function leftJoinOn($foreignKeyColumn)
    {
        return 'left ' . $this->joinOn($foreignKeyColumn);
    }
}