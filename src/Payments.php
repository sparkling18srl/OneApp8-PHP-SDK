<?php

namespace OneApp8;

/**
 * Class Payments
 */
class Payments implements RestService
{
    private $apiCaller = null;

    public function __construct()
    {
        $this->apiCaller = new ApiCaller();
    }

    public function readAll()
    {
        return $this->apiCaller->request('GET', 'paymens', []);
    }

    public function read($id)
    {
    }

    public function delete($id)
    {
    }

    public function create($data)
    {
        if (empty($data) || !is_array($data)) {
            throw new \BadMethodCallException('The payment passed in is not valid');
        }

        return $this->apiCaller->request('POST', 'payments/wallet/moto', $data);
    }
}
