<?php
/**
 * Loader for Season
 *
 * @author SlabPHP
 * @package My
 * @subpackage Models
 */
namespace Slab\Tests\Database\Mocks\Models\Season;

class Loader extends \Slab\Database\Models\MySQL\Loader
{
    const DATA_OBJECT_CLASS = '\Slab\Tests\Database\Mocks\Models\Season\DataObject';

    const TABLE_NAME = 'test_season';

    const ID_COLUMN = 'test_id';

    /**
     * @var string[]
     */
    protected $mapping = [
        "test_id" => "testId",
        "updated_date" => "updatedDate:date",
        "season" => "season",
    ];
}
