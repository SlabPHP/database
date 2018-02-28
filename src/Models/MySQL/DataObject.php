<?php
/**
 * Base MySQL Data Object
 *
 * @package Slab
 * @subpackage Models
 * @author Eric
 */
namespace Slab\Database\Models\MySQL;

class DataObject extends \Slab\Database\Models\BaseDataObject
{
    const DATA_LOADER = '\Slab\Database\Models\MySQL\Loader';

    /**
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function __mapObject()
    {
        $className = static::DATA_LOADER;

        if (!class_exists($className))
        {
            throw new \Slab\Database\Exceptions\Mapping('Class ' . static::DATA_LOADER . ' does not exist.');
        }

        /**
         * @var \Slab\Database\Models\MySQL\Loader $loader
         */
        $loader = new $className();
        $loader->performMappingOnObject($this);
    }
}