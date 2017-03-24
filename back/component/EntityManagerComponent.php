<?php

namespace Component;

use Exception;

class EntityManagerComponent
{
    private $_em = null;
    private static $_instance = null;

    private function __construct()
    {
        global $EM;
        $this->_em = $EM;
    }

    protected function __clone() {}

    static public function getInstance()
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function get()
    {
        $instance = self::getInstance();
        return $instance->_em;
    }

}
