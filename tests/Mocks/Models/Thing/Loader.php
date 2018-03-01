<?php
/**
 * Loader for Thing
 *
 * @author SlabPHP
 * @package My
 * @subpackage Models
 */
namespace Slab\Tests\Database\Mocks\Models\Thing;

class Loader extends \Slab\Database\Models\MySQL\Loader
{
    const DATA_OBJECT_CLASS = '\Slab\Tests\Database\Mocks\Models\Thing\DataObject';

    const TABLE_NAME = 'testingtable';

    const ID_COLUMN = 'id';

    /**
     * @var string[]
     */
    protected $mapping = [
        "id" => "id",
        "name" => "name",
        "description" => "description",
        "created" => "created:date",
    ];
}
