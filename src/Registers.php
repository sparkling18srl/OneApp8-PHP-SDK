<?php
namespace OneApp8;

/**
 * Registers class
 *
 * @package OneApp8
 */
class Registers implements RestService
{
    const ENDPOINT = 'registers';

    private $apiCaller = null;

    /**
     * Default constructor
     */
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
        if (!empty($id) && is_numeric($id)) {
            return $this->apiCaller->request('GET', self::ENDPOINT . "/$id", []);
        }

        throw new \InvalidArgumentException('Invalid paramenter ' . $id);
    }

    public function delete($id)
    {
        throw new \BadMethodCallException("Method not implemented");
    }

    public function create($data)
    {
        throw new \BadMethodCallException("Method not implemented");
    }
}
