<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

/**
 * Class OrdersTest
 *
 * Copyright (c) 2017-present, Sparkling18 S.p.A.
 */
class OrdersTest extends TestCase
{
    private $resource = null;

    /**
     * The created order id
     */
    private static $orderId;

    private static $order;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        $faker = Faker::create('it_IT');
        self::$order = [
            'externalId' => $faker->randomNumber(),
            'created' => date('c'),
            'updated' => date('c'),
            'actions' => [],
            'description' => $faker->sentence(32, false),
            'notes' => $faker->text(32),
            'total' => [
                'currency' => 'EUR',
                'amount' => round($faker->randomFloat(4, 2, 100), 2) * 100,
                'decimalDigits' => 2
            ],
            'register' => [
                'id' => 243,
                'name' => 'Negozio di test Spin Reg'
            ]
        ];
    }

    /**
     * @before
     */
    protected function setUp()
    {
        $this->resource = new Orders();
    }

    /**
     * @group orders
     */
    public function testCreate()
    {
        $result = $this->resource->create(self::$order);
        $this->assertNotNull($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertNotEmpty($result['id']);
        self::$orderId = $result['id'];
        $this->assertEquals(self::$orderId, $result['id']);
    }

    /**
     * @group orders
     */
    public function testRead()
    {
        $result = $this->resource->read(self::$orderId);
        $this->assertNotNull($result);
        $this->assertEquals(self::$orderId, $result['id']);
        $this->assertEquals(self::$order['total']['amount'], $result['total']['amount']);
    }

    /**
     * @group orders
     */
    public function testReadAll()
    {
        $orders = $this->resource->readAll();
        $this->assertNotNull($orders);
        $this->assertArrayHasKey('total', $orders);
        $this->assertArrayHasKey('items', $orders);
        $this->assertTrue(count($orders['total']) > 0);
        $this->assertTrue(count($orders['items']) > 0);
    }

    public static function getOrder()
    {
        self::setUpClass();

        return self::$order;
    }
}
