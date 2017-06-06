<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;

/**
 * Class OneApp8Test
 */
class OneApp8Test extends TestCase
{
    /**
     * @group old
     */
    public function testOneApp8ClientInit()
    {
        $client = new OneApp8();
        $this->assertNotNull($client);
        $expected = "api.test.sparkling18.com";
        $this->assertEquals($expected, $client->host);
    }

    /**
     * @group old
     */
    public function testGetRegister()
    {
        $this->markTestSkipped();
        $client = new OneApp8();
        $res = $client->getRegister();
        $this->assertNotNull($res);
    }

    /**
     * @group old
     */
    public function testGenerateOrder()
    {
        $client = new OneApp8();
        $payment = new \stdClass;
        $payment->id = 1186;
        $payment->period_start = date('c');
        $payment->period_end = date('c');
        $payment->amount = 40.00;
        $res = $client->generateOrder($payment);
    }
}
