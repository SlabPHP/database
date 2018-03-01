<?php
/**
 * Base MySQL Loader Test
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Models\MySQL;

class LoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Simple loader tests
     */
    public function testLoader()
    {
        $mockLoader = new \Slab\Tests\Database\Mocks\Models\Thing\Loader();

        $this->assertEquals('`testingtable`', $mockLoader->getTable());
        $this->assertEquals('`testingtable` as `alias`', $mockLoader->getTable('alias'));
        $this->assertEquals('`testingtable` as `alias`', $mockLoader->getTable('alias', false));

        $this->assertEquals('`id`', $mockLoader->getPrimaryKey());
        $this->assertEquals('`alias`.`id`', $mockLoader->getPrimaryKey('alias'));
        $this->assertEquals('`testingtable`.`id`,`testingtable`.`name`,`testingtable`.`description`,`testingtable`.`created`', $mockLoader->getMappingAsSQL());
        $this->assertEquals('`a`.`id`,`a`.`name`,`a`.`description`,`a`.`created`', $mockLoader->getMappingAsSQL(true, 'a'));

        $this->assertEquals('`id`,`name`,`description`,`created`', $mockLoader->getColumnsAsSQL());
        $this->assertEquals('`a`.`id`,`a`.`name`,`a`.`description`,`a`.`created`', $mockLoader->getColumnsAsSQL('a'));
        $this->assertEquals('`a`.`id` as `pre_id`,`a`.`name` as `pre_name`,`a`.`description` as `pre_description`,`a`.`created` as `pre_created`', $mockLoader->getColumnsAsSQL('a', 'pre_'));
    }
}