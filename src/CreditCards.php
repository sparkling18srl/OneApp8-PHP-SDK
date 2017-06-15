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

    /**
     * To delete a credit card
     * @param array The array with keys marchantId and walletId
     */
    public function delete($param)
    {
        if (!is_array($param)
            && !array_key_exists('merchantId', $param)
            && !array_key_exists('walletId', $param)) {
            throw new \BadMethodCallException('The proveded input param is not correct');
        }
        $path = self::ENDPOINT
            .'/wallet?merchantId='.$param['merchantId']
            .'/walletId='.$param['walletId'];

        return $this->apiCaller->request('DELETE', $path, []);
   }

    public function create($data)
    {
        if (empty($data) || !is_array($data)) {
            throw new \BadMethodCallException('The param you passed in is not correct');
        }

        return $this->apiCaller->request('POST', self::ENDPOINT, $data);
    }
}
