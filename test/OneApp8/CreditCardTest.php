<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

/**
 * Class CreditCardTest
 */
class CreditCardTest extends TestCase
{
    private $resource = null;

    private static $creditCard;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        $faker = Faker::create('it_IT');
        self::$creditCard = [
            'holder' => $faker->lastName() . ' ' . $faker->firstNameMale(),
            'pan'    => '4943319608775645',
            'expireMonth' => '01',
            'expireYear' => '2018',
            'securityCode' => '429'
        ];
    }
    /**
     * @before
     */
    public function setUp()
    {
        $this->resource = new CreditCards();
    }

    /**
     * @group creditcards
     */
    public function testCreate()
    {

        $result = $this->resource->create($card);
        $this->assertNotNull($result);
        print_r($result);
    }

    /**
     * @return array The credit card data
     */
    public static function getCreditCard()
    {
        self::setUpClass();

        return self::$creditCard;
    }
}
