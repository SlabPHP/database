<?php
/**
 * MySQL Provider Response Test
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Providers\MySQL;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test response generation
     */
    public function testResponse()
    {
        $resultMock = $this->getMockBuilder(\Mysqli_result::class)
            ->setMethods(['fetch_object','close'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultMock->expects($this->any())
            ->method('fetch_object')
            ->willReturn(
                new OtherTestObject(15),
                new OtherTestObject(33),
                new OtherTestObject(77),
                null
            );

        $response = new \Slab\Database\Providers\MySQL\Response();

        /**
         * @var \Mysqli_result $resultMock
         */
        $response->initializeFromQueryResults($resultMock, 0, '', OtherTestObject::class);

        $responseResult = $response->result();

        $this->assertCount(3, $responseResult);

        $firstObject = $response->row();
        $this->assertNotEmpty($firstObject);
        $this->assertNotEmpty($firstObject->id);
        $this->assertNotEmpty($firstObject->dynamicField);
        $this->assertEquals(15, $firstObject->id);
        $this->assertEquals(30, $firstObject->dynamicField);

        $secondObject = $response->row();
        $this->assertNotEmpty($secondObject);
        $this->assertNotEmpty($secondObject->id);
        $this->assertNotEmpty($secondObject->dynamicField);
        $this->assertEquals(33, $secondObject->id);
        $this->assertEquals(66, $secondObject->dynamicField);

        $thirdObject = $response->row();
        $this->assertNotEmpty($thirdObject);
        $this->assertNotEmpty($thirdObject->id);
        $this->assertNotEmpty($thirdObject->dynamicField);
        $this->assertEquals(77, $thirdObject->id);
        $this->assertEquals(154, $thirdObject->dynamicField);

        $fourthObject = $response->row();
        $this->assertEmpty($fourthObject);
    }
}

class OtherTestObject
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __mapObject()
    {
        $this->dynamicField = $this->id * 2;
    }
}