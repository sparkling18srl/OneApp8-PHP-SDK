<?php

namespace OneApp8;

/**
 * Class RestService
 */
interface RestService
{
    public function readAll();

    public function read($id);

    public function delete($id);

    public function create($object);
}
