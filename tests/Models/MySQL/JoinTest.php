<?php
/**
 * Base MySQL Loader Test
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Models\MySQL;

class JoinTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function testJoin()
    {
        $join = new \Slab\Database\Models\MySQL\Join('\Slab\Tests\Database\Mocks\Models\Season\DataObject', 's', 'season');

        $this->assertEquals('`test_season` as `s`', $join->getTable());
        $this->assertEquals('`s`.`test_id` as `s_test_id`,`s`.`updated_date` as `s_updated_date`,`s`.`season` as `s_season`', $join->getMappingSQL());
        $this->assertEquals('join `test_season` as `s` on `s`.`test_id` = test ', $join->joinOn('test'));
        $this->assertEquals('left join `test_season` as `s` on `s`.`test_id` = test ', $join->leftJoinOn('test'));

        $testObject = new \Slab\Tests\Database\Mocks\Models\Thing\DataObject();
        $testObject->id = 44;
        $testObject->s_test_id = 13;
        $testObject->s_updated_date = '2018-03-01 00:00:00';
        $testObject->s_season = 'Fall';

        $join->resolve($testObject);

        $this->assertEquals(44, $testObject->id);
        $this->assertNotEmpty($testObject->season);
        $this->assertEquals(13, $testObject->season->testId);
        $this->assertEquals(new \DateTime('2018-03-01 00:00:00'), $testObject->season->updatedDate);
        $this->assertEquals('Fall', $testObject->season->season);
    }

    /**
     * @throws \Slab\Database\Exceptions\Mapping
     * @expectedException \Slab\Database\Exceptions\Mapping
     */
    public function testBadClass()
    {
        $join = new \Slab\Database\Models\MySQL\Join('badClass', 'thing', 'thang');
    }
}