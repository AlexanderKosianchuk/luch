<?php

namespace Controller;

use Model\User;
use Model\Language;

class CController
{
    protected $curPage = null;

    public $action;
    public $data;

    public $_user;
    public $lang;
    protected $userLang = 'en';

    protected function setAttributes()
    {
        $post = $_POST;
        $get = $_GET;

        if((isset($post['action']) && ($post['action'] != '')) &&
            (isset($post['data']) && ($post['data'] != ''))
        ) {
            $this->action = $post['action'];
            $this->data = $post['data'];

            return;
        } else if ($get != null) {
            if ($get['action']) {
                $this->action = $get['action'];
            }

            if (isset($post['data'])) {
                if ($this->isJson($post['data'])) {
                    $this->data = json_decode($post['data'], true);

                    return;
                }

                $this->data = $post['data'];

                return;
            }

            $this->data = $get;

            return;
        } else {
            $msg = "Incorect input. Data: " . json_encode(isset($post['data']) ? $post['data'] : '') .
                " . Action: " . json_encode(isset($post['action']) ? $post['action'] : '') .
                " . Page: " . $this->curPage. ".";
            echo($msg);
            error_log($msg);
            return;
        }
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
                $usrInfo = $this->_user->GetUsersInfo($this->_user->username);
                $this->userLang = $usrInfo['lang'];
            }

            $success = true;
        }

        $L = new Language();
        $L->SetLanguageName($this->userLang);
        $this->userLang = $L->GetLanguageName();
        $this->lang = $L->GetLanguage($this->curPage);
        unset($L);

        return $success;
    }

    public function RegisterActionExecution($action, $status,
         $senderId = null, $senderName = null, $targetId = null, $targetName = null)
   {
      $userId = $this->_user->userInfo['id'];
      $this->_user->RegisterUserAction($action, $status, $userId,
            $senderId, $senderName, $targetId, $targetName);
      return;
   }

   public function RegisterActionReject($action, $status,
         $senderId = null, $senderName = null, $targetId = null, $targetName = null)
   {
      $userId = $this->_user->userInfo['id'];
      $this->_user->RegisterUserAction($action, $status, $userId,
            $senderId, $senderName, $targetId, $targetName);

      unset($U);
   }

   private function isJson($string) {
       if (!is_string($string)) {
           return false;
       }

       json_decode($string);
       return (json_last_error() == JSON_ERROR_NONE);
   }
}
