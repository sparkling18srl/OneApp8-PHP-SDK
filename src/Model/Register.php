<?php

namespace OneApp8\Model;

/**
 * Class register model
 *
 * @package OneApp8\Model
 */
class Register
{
    private $fields = array();

    public function __set($key, $value) {
        $this->fields[$key] = $value;
    }

    public function __get($key)
    {
        return $this->fields[$key];
    }
} // END class Register
