<?php

namespace OneApp8;

/**
 * Class Users
 *
 * @property mixed $User
 * @property mixed $Action
 * @property mixed $UserInfo
 * @property mixed $Phone
 * @property mixed $Email
 * @property mixed $CreditCard
 */
class Users implements RestService
{
    const ENDPOINT = 'users';

    private $apiCaller = null;

    public function __construct()
    {
        $this->apiCaller = new ApiCaller();
    }

    public function readAll()
    {
        throw new \BadMethodCallException();
    }

    public function read($id)
    {
        if (!empty($id) && is_numeric($id)) {
            return $this->apiCaller->request('GET', self::ENDPOINT."/$id", []);
        }

        throw new \BadMethodCallException();
    }

    /**
     * To retrieve a list of users with the specified merchant and external identifier
     *
     * @param integer $merchantId, The merchant identifier
     * @param integer $userExternalId, The user external identifier
     */
    public function readExternal($merchantId, $userExternalId)
    {
        $isMerchantIdOk = !empty($merchantId) && is_numeric($merchantId);
        $isUserExternalIdOk = !empty($userExternalId) && is_numeric($userExternalId);
        if ($isMerchantIdOk && $isUserExternalIdOk) {
            $endpoint = self::ENDPOINT."/external/$merchantId/$userExternalId";

            return $this->apiCaller->request('GET', $endpoint, []);
        }

        throw new \BadMethodCallException();
    }

    public function delete($id)
    {
        if (!empty($id) && is_numeric($id)) {
            return $this->apiCaller->request('DELETE', self::ENDPOINT."/$id", []);
        }

        throw new \BadMethodCallException('The id value is wrong or empty');
    }

    /**
     * To create a new user
     *
     * @property integer $merchant, The merchant identifier the user is associated with
     * @property string  $userId, The user's username
     * @property string  $email, The user's principal email address
     * @property mixed   $cellphone, The user's mobile phone number
     * @property string  $password optional, The user's password
     */
    public function create($user)
    {
        return $this->apiCaller->request('POST', self::ENDPOINT, $user);
    }

    /**
     * To update a user
     */
    public function update($user)
    {
        return $this->apiCaller->request('PUT', self::ENDPOINT, $user);
    }
}
