<?php

namespace Controller;

use Model\User;
use Model\Language;

use Exception;

class CController
{
    public $action;
    public $data;

    public $_user;
    public $lang;
    protected $userLang = 'en';

    protected function setAttributes()
    {
        $get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if (!isset($get['action'])) {
            throw new Exception("Action to execute did not pass. "
                . "GET: " . json_encode($get)
                . "POST: " . json_encode($post), 1);
        }

        $this->action = $get['action'];

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($get['action']);
            $this->data = $get;
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] === 'POST') && empty($post)) {
            throw new Exception('Empty POST data', 1);
        }

        $this->data = $post;
        return;
    }

    public function IsAppLoggedIn()
    {
        $post = $_POST;
        $session = $_SESSION;
        $cookie = $_COOKIE;

        $this->_user = isset($this->_user) ? $this->_user : new User();

        $success = false;
        if ($this->_user->tryAuth($post, $session, $cookie)) {
            if(isset($this->_user->username) && ($this->_user->username != '')) {
                $userInfo = $this->_user->GetUsersInfo($this->_user->username);
                $this->userLang = $userInfo['lang'];
            }

            $success = true;
        }

        $className = get_class($this);
        $page = substr($className, strpos($className, "\\") + 1);

        $L = new Language();
        $L->SetLanguageName($this->userLang);
        $this->userLang = $L->GetLanguageName();
        $this->lang = $L->GetLanguage($page);
        unset($L);

        return $success;
    }
}
