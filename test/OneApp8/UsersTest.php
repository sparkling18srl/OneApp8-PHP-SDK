<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

/**
 * Class UsersTest
 */
class UsersTest extends TestCase
{
    const MERCHANT_ID = 1140;
    const EXTERNAL_ID = 1234;
    private $resource = null;
    private static $user;
    private static $createdUserId;

    /**
     * @beforeClass
     */
    public static function setUpClass()
    {
        $faker = Faker::create('it_IT');
        $firstName = $faker->firstNameMale();
        $lastName = $faker->lastName();
        self::$user = [
            'externalId' => self::EXTERNAL_ID,
            'merchant' => [
                'id' => self::MERCHANT_ID
            ],
            'info' => [
                'birthCity' => 'Palermo',
                'country' => 'IT',
                'name' => $firstName,
                'surname' => $lastName,
                'birthDate' => strtotime('1/6/1978'),
                'city' => 'Palermo',
                'ssn' => 'ABCXWZ78E01F126K',
                'birthCountry' => 'IT',
            ],
            'email' => [
                'address' => $firstName.'.'.$lastName.'@'.$faker->domainName(),
                'valid' => true
            ],
            'cellphone' => [
                'number' => substr($faker->e164PhoneNumber(), 4),
                'country' => 'IT',
                'valid' => true
            ],
            'userid' => strtolower($firstName.'.'.$lastName),
            'password' => strtolower($firstName.'.'.$lastName)
        ];
    }

    /**
     * @before
     */
    public function init()
    {
        $this->resource = new Users();
    }

    /**
     * @group users
     */
    public function testCreateWithSuccess()
    {
        $result = $this->resource->create(self::$user);
        $this->assertNotNull($result);
        $this->assertNotEmpty($result['id']);
        $this->assertNotEmpty($result['userid']);
        self::$createdUserId = $result['id'];
    }

    /**
     * @group users
     */
    public function testReadExternal()
    {
        $this->markTestSkipped();
        $users = $this->resource->readExternal(self::MERCHANT_ID, self::EXTERNAL_ID);
        $this->assertNotNull($users);
    }

    /**
     * @group users
     */
    public function testUpdate()
    {
        self::$user['cellphone']['number'] = '3227071888';
        $result = $this->resource->update(self::$user);
        $this->assertNotNull($result);
    }

    /**
     * @group users
     */
    public function testReadWithSuccess()
    {
        $user = $this->resource->read(self::$createdUserId);
        $this->assertNotNull($user);
        $this->resource->delete($user['id']);
    }

    /**
     * Returns a new user array
     *
     * @return array The user array
     */
    public static function getUser()
    {
        self::setUpClass();

        return self::$user;
    }
}
