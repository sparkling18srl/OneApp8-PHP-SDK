<?php
namespace OneApp8;

use PHPUnit\Framework\TestCase;

/**
 * ApiCallerTest
 *
 * @package OneApp8
 */
class RegistersTest extends TestCase
{
    private $resource = null;

    /**
     * @before
     */
    public function init()
    {
        $this->resource = new Registers();
    }

    /**
     * @group registers
     */
    public function testReadAll()
    {
        $registers = $this->resource->readAll();
        $this->assertNotNull($registers);
        $this->assertArrayHasKey('total', $registers);
        $this->assertArrayHasKey('items', $registers);
        $this->assertTrue(count($registers['total']) > 0);
        $this->assertTrue(count($registers['items']) > 0);
        $this->assertGreaterThanOrEqual(1, $registers['items'], "No registers found");
    }

    /**
     * @group registers
     */
    public function testReadWithSuccess()
    {
        $expected = 243;
        $register = $this->resource->read($expected);
        $this->assertNotNull($register);
        $this->assertArrayHasKey('id', $register);
        $this->assertEquals($expected, $register['id']);
    }

    /**
     * @group registers
     * @expectedException InvalidArgumentException
     */
    public function testReadWithFailure()
    {
        $expected = 'abc';
        $register = $this->resource->read($expected);
        $this->assertNotNull($register);
        $this->assertObjectHasAttribute('id', $register);
        $this->assertEquals($expected, $register['id']);
    }

    /**
     * @group registers
     * @expectedException BadMethodCallException
     */
    public function testDelete()
    {
        $this->resource->delete('1243');
    }

    /**
     * @group registers
     * @expectedException BadMethodCallException
     */
    public function testCreate()
    {
        return $this->resource->create([]);
    }
}
