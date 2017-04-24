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

    public function ShowLoginForm()
    {
        $loginMsg = isset($this->_user->loginMsg) ? $this->_user->loginMsg : '';

        $files = scandir ('public/');
        $scriptName = '';
        foreach ($files as $item) {
            $fileParts = pathinfo($item);
            if ((strpos($item, 'login') !== false)
                && ($fileParts['extension'] === 'js')
            ) {
                $scriptName = $item;
            }
        }
        printf("<script type='text/javascript' src='public/".$scriptName."'></script>");
        printf("<div align='center' class='login-form'><p class='login-form_header'>%s</p>
            <img src='/front/stylesheets/basicImg/login-logo.png' alt='luch logo'/></br>
            <p><label class='login-form_label login-form_label--alert'>%s</label></p>
            <form action='index.php' method='POST'>
            <table>
                <tr><td><label class='login-form_label'>%s</label></td><td>
                    <input type='text' name='user' class='login-form_input'>
                </td></tr>
                <tr><td><label class='login-form_label'>%s</label></td><td>
                    <input type='password' name='pwd' class='login-form_input'>
                </td></tr>
                <tr><td><label class='login-form_label'>%s</label></td><td align='center'>
                    <input type='checkbox' name='autologin' value='1' class='login-form_checkbox'>
                </td></tr>
            </table>

            <input class='login-form_button' type='submit' value='%s'>
        </form></div>", $this->lang->loginForm,
        $loginMsg,
        $this->lang->userName,
        $this->lang->pass,
        $this->lang->rememberMe,

        $this->lang->login);
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
