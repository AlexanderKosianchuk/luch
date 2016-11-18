<?php

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
        } else if ($get != null) {
            $this->data = $get;

            if ($get['action']) {
                $this->action = $get['action'];
            }
        } else {
            $msg = "Incorect input. Data: " . json_encode($post['data'] ?? '') .
                " . Action: " . json_encode($post['action'] ?? '') .
                " . Page: " . $this->curPage. ".";
            echo($msg);
            error_log($msg);
        }
    }

    public function IsAppLoggedIn()
    {
        $post = $_POST;
        $session = $_SESSION;
        $cookie = $_COOKIE;

        $this->_user = $this->_user ?? new User();

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
        $loginMsg = $this->_user->loginMsg ?? '';
        printf("<div align='center'><p class='Label'>%s</p>
            <label style='color:darkred;'>%s</label></br></br>
            <form action='index.php' method='POST'>
            <table>
                <tr><td>%s</td><td>
                    <input type='text' name='user'>
                </td></tr>
                <tr><td>%s</td><td>
                    <input type='password' name='pwd'>
                </td></tr>
                <tr><td>%s</td><td align='center'>
                    <input type='checkbox' name='autologin' value='1'>
                </td></tr>
                <tr style='visibility:hidden;'><td>
                    Nonce:
                </td></tr>
            </table>

            <input class='Button' type='submit' value='%s'>
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
}
