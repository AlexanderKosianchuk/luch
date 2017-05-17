<?php

namespace Controller;

use Exception;

class EntryController extends CController
{
    public $curPage = 'indexPage';

    function __construct()
    {
        $this->setAttributes();

        if (!$this->IsAppLoggedIn()
            && ($this->action !== 'user/login')
        ) {
            echo (json_encode('Auth failed'));
            exit;
        }

        if (strpos($this->action, '/') !== false) {
            $exp = explode('/', $this->action);
            $controller = ucfirst($exp[0] . 'Controller');
            $method = $exp[1];

            if (file_exists(@SITE_ROOT_DIR."/controller/".$controller.'.php')) {
                require_once(@SITE_ROOT_DIR."/controller/".$controller.'.php');

                $controller = 'Controller\\' . $controller;
                $C = new $controller;
                $C->action = $this->action;

                if (method_exists ($C, $method)) {
                    $C->IsAppLoggedIn();

                    $C->$method($this->data);
                } else {
                    throw new Exception("Called method unexist. "
                        . "Controller: ". $controller . ", "
                        . "Method: ". $method . ", "
                        . "Args: " . json_encode($this->data), 1);
                }
            }
        }

        exit (0);
    }
}
