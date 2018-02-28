<?php
/**
 * Database Driver Tests
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database;

class DriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Simple tests of the driver
     */
    public function testDriver()
    {
        $provider = new Mocks\Provider();

        $driver = new \Slab\Database\Driver();
        $driver->setProvider($provider);

        $this->assertEquals($provider, $driver->getProvider());

        $response = $driver->delete('table', 'where', 'thing');

        $this->assertEquals(4, $response->count());
        $this->assertEquals('["delete:table:where:thing","1-none","Slab\\\\Tests\\\\Database\\\\Mocks\\\\Provider"]', json_encode($response->result()));
        $this->assertEquals('row', $response->row());

        $response = $driver->update('table', ['thing1','thing2'], 'where', 14);

        $this->assertEquals('["update:table:thing1,thing2:where:14","1-none","Slab\\\\Tests\\\\Database\\\\Mocks\\\\Provider"]', json_encode($response->result()));

        $response = $driver->insert('table', ['thing1','thing2']);

        $this->assertEquals('["insert:table:thing1,thing2","1-none","Slab\\\\Tests\\\\Database\\\\Mocks\\\\Provider"]', json_encode($response->result()));

        $response = $driver->query('this is some ? query ? thing', ['one','two','three'], get_called_class());
        $this->assertEquals('["this is some |eno| query |owt| thing","1-none","Slab\\\\Tests\\\\Database\\\\Mocks\\\\Provider"]', json_encode($response->result()));
    }
}