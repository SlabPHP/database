<?php
/**
 * Base Model Loader
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Models;

abstract class BaseLoader implements LoaderInterface
{
    const DATA_OBJECT_CLASS = '\Slab\Database\Models\BaseDataObject';

    /**
     * BaseLoader constructor.
     * @param null $driver
     */
    public function __construct($driver = null)
    {
        if (!empty($driver))
        {
            $this->setDriver($driver);
        }
    }
}