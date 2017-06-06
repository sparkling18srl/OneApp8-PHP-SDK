<?php

namespace OneApp8\Model;

/**
 * Class EntityInfo
 *
 * $id integer
 * $name string
 * $url string
 */
class EntityInfo
{
    private $fields = array();

    public function __set($key, $value) {
        $this->fields[$key] = $value;
    }

    public function __get($key) {
        return $this->fields[$key];
    }
}
