<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

/**
 * Class PaymentTest
 */
class PaymentTest extends TestCase
{
    private $usersResource = null;

    private $paymentsResourse = null;

    private $ordersResouce = null;

    private $creditCardResource = null;

    private static $order;

    private static $user;

    private static $creditCard;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        self::$order = OrdersTest::getOrder();
        self::$user = UsersTest::getUser();
        self::$creditCard = CreditCardTest::getCreditCard();
    }

    /**
     * @before
     */
    public function setUp()
    {
        $this->usersResource = new Users();
        $this->paymentsResourse = new Payments();
        $this->ordersResouce = new Orders();
        $this->creditCardResource = new CreditCards();
    }


    /**
     * @group payments
     */
    public function testCreate()
    {
        /*
        $user = $this->usersResource->create(self::$user);
        $this->assertNotNull($user);
        $this->assertArrayHasKey('id', $user);
         */


        /*
        self::$creditCard["userId"] = $user['userid'];
        self::$creditCard['skipValidation'] = true;
        $creditCard = $this->creditCardResource->create(self::$creditCard);
         */

        $order = $this->ordersResouce->create(self::$order);
        $this->assertNotNull($order);
        $this->assertArrayHasKey('id', $order);

        $payment = [
            'order' => [
                'id' => $order['id']
            ],
            'walletId' => 17163910 // userId alessio.risso, id credit card: 17183
        ];

        $result = $this->paymentsResourse->create($payment);
    }

    /**
     * @group payments
     */
    public function testReadAll()
    {
        $this->markTestSkipped();
        $payments = $this->resource->readAll();
        print_r($payments);
    }
}
