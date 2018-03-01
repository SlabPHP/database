<?php
/**
 * MySQL Provider Test
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Providers\MySQL;

class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test basic functionality
     *
     * @throws \Slab\Database\Exceptions\Query
     */
    public function testProvider()
    {
        $mock = $this->getMockBuilder(\Mysqli::class)
            ->setMethods(['query', 'real_escape_string'])
            ->getMock();

        $mock->expects($this->any())
            ->method('query')
            ->with($this->equalTo("select * from thingy where id = 14 limit 1;"))
            ->will($this->returnCallback(function($query) {

                $this->assertNotEmpty($query);

                $object = new TestObject();
                $object->id = 15;
                $object->query = $query;

                /**
                 * @var \Mysqli_result
                 */
                $resultMock = $this->getMockBuilder(\Mysqli_result::class)
                    ->setMethods(['fetch_object','close'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $resultMock->expects($this->any())
                    ->method('fetch_object')
                    ->willReturn($object, null);

                return $resultMock;
            }));

        $mock->expects($this->any())
            ->method('real_escape_string')
            ->will($this->returnCallback(function($input){ return addslashes($input); }));

        $provider = new \Slab\Database\Providers\MySQL\Provider();

        /**
         * @var \Mysqli $mock
         */
        $provider->setMySQL($mock);

        $this->assertEquals("DELETE FROM `table` WHERE `id` = '156';",  $provider->buildDeleteQuery('table', 'id', 156));
        $this->assertEquals("UPDATE `table` SET `sausage` = '145', `thingy` = 'dog' WHERE where id = 1 LIMIT 1;",  $provider->buildUpdateQuery('table', ['sausage'=>145,'thingy'=>'dog'], 'where id = 1', 1));
        $this->assertEquals("INSERT INTO `table` (`value`, `blargh`) VALUES ('44', 'fallout');", $provider->buildInsertQuery('table', ['value'=>44,'blargh'=>'fallout']));

        $return = $provider->query('select * from thingy where id = 14 limit 1;', [14, 'thing', null]);
        $this->assertNotEmpty($return);
        $this->assertEquals(1, $return->count());

        $row = $return->row();
        $this->assertNotEmpty($row);
        $this->assertNotEmpty($row->id);
        $this->assertNotEmpty($row->query);
        $this->assertNotEmpty($row->dynamicField);

        $this->assertEquals(15, $row->id);
        $this->assertEquals('select * from thingy where id = 14 limit 1;', $row->query);
        $this->assertEquals('true', $row->dynamicField);

    }
}


class TestObject
{
    public $id;

    public $query;

    public function __mapObject()
    {
        $this->dynamicField = 'true';
    }
}