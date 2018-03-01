<?php
/**
 * DataObject Model for Thing
 *
 * @author SlabPHP
 * @package My
 * @subpackage Models
 */
namespace Slab\Tests\Database\Mocks\Models\Thing;

class DataObject extends \Slab\Database\Models\MySQL\DataObject
{
    const DATA_LOADER = '\Slab\Tests\Database\Mocks\Models\Thing\Loader';

    /**
     * @var number
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var \DateTime
     */
    public $created;

}
