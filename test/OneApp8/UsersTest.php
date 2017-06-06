<?php

namespace OneApp8;

use PHPUnit\Framework\TestCase;

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
    public static function initClass()
    {
        self::$user = [
            'externalId' => self::EXTERNAL_ID,
            'merchant' => [
                'id' => self::MERCHANT_ID
            ],
            'info' => [
                'birthCity' => 'Palermo',
                'country' => 'IT',
                'name' => 'Charlie',
                'surname' => 'Brown',
                'birthDate' => strtotime('1/5/1979'),
                'city' => 'Palermo',
                'ssn' => 'LBRGPP79E01F126K',
                'birthCountry' => 'IT',
            ],
            'email' => [
                'address' => 'charlie.brownie@sparkling18.com',
                'valid' => true
            ],
            'cellphone' => [
                'number' => '3337170846',
                'country' => 'IT',
                'valid' => true
            ],
            'userid' => 'charlie.brownie',
            'password' => 'p@ssword',
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
}
