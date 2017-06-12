<?php

namespace OneApp8;

/**
 * Class CreditCards
 */
class CreditCards implements RestService
{
    const ENDPOINT = 'creditcards';

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
        throw new \BadMethodCallException();
    }

    public function delete($id)
    {
        throw new \BadMethodCallException();
    }

    public function create($data)
    {
        if (empty($data) || !is_array($data)) {
            throw new \BadMethodCallException('The param you passed in is not correct');
        }

        return $this->apiCaller->request('POST', self::ENDPOINT, $data);
    }
}
