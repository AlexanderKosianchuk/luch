<?php

namespace Controller;

use Exception;

class EntryController extends CController
{
    public $curPage = 'indexPage';

    function __construct()
    {
        if(!$this->IsAppLoggedIn()) {
            $this->ShowLoginForm();
        }

        $this->setAttributes();

        if (strpos($this->action, '/') !== false) {
            $exp = explode('/', $this->action);
            $controller = ucfirst($exp[0] . 'Controller');
            $method = $exp[1];

            if (file_exists(@SITE_ROOT_DIR."/controller/".$controller.'.php')) {
                require_once(@SITE_ROOT_DIR."/controller/".$controller.'.php');

                $controller = 'Controller\\' . $controller;
                $C = new $controller;

                if (method_exists ($C, $method)) {
                    $C->IsAppLoggedIn();
                    $C->$method($this->data);
                } else {
                    throw new Exception("Called method unexist", 1);
                }
            }
        }
    }
}
