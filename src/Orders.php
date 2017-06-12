<?php

namespace OneApp8;

/**
 * Class Orders
 *
 * Copyright (c) 2017-present, Sparkling18 S.p.A.
 */
class Orders implements RestService
{
    const ENDPOINT = 'orders';

    private $apiCaller = null;

    public function __construct()
    {
        $this->apiCaller = new ApiCaller();
    }

    public function readAll()
    {
        return $this->apiCaller->request('GET', self::ENDPOINT, []);
    }

    public function read($id)
    {
        if (empty($id) || !is_numeric($id)) {
            throw new \BadMethodCallException('The order id passed in is not valid');
        }

        return $this->apiCaller->request('GET', self::ENDPOINT . "/$id", []);
    }

    public function delete($id)
    {
        throw new \BadMethodCallException('An order cannot be deleted');
    }

    public function create($order)
    {
        if (empty($order) || !is_array($order)) {
            throw new \BadMethodCallException('The order data passed in is not valid');
        }

        return $this->apiCaller->request('POST', self::ENDPOINT, $order);
    }
}
