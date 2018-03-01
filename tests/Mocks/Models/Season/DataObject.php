<?php
/**
 * DataObject Model for Season
 *
 * @author SlabPHP
 * @package My
 * @subpackage Models
 */
namespace Slab\Tests\Database\Mocks\Models\Season;

class DataObject extends \Slab\Database\Models\MySQL\DataObject
{
    const DATA_LOADER = '\Slab\Tests\Database\Mocks\Models\Season\Loader';

    /**
     * @var number
     */
    public $testId;

    /**
     * @var \DateTime
     */
    public $updatedDate;

    /**
     * @var string
     */
    public $season;

}
