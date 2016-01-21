<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

class UserModel
{
	public $curPage = 'userPage';
	
	private $ulogin;
	private $username;
	
	public $privilege;
	public $lang;

	public $action;
	public $data;

	function __construct($post, $session)
	{
		$L = new Language();
		$this->lang = $L->GetLanguage($this->curPage);
		$this->userActions = (array)$L->GetServiceStrs($this->curPage);
		unset($L);

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		if(isset($session['username']))
		{
			$this->username = $session['username'];
		}
		else
		{
			$this->username = '';
		}
		
		//even if flight was selected if file send this variant will be processed
		if((isset($post['action']) && ($post['action'] != '')) && 
			(isset($post['data']) && ($post['data'] != '')))
		{
			$this->action = $post['action'];
			$this->data = $post['data'];			
		}
		else
		{
			$msg = "Incorect input. Data: " . json_encode($post['data']) . 
				" . Action: " . json_encode($post['action']) . 
				" . Page: " . $this->curPage. ".";
			echo($msg);
			error_log($msg);
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}
	
	public function PutTopMenu()
	{	
		$topMenu = "<div id='topMenuBruType' class='TopMenu'></div>";	
		return $topMenu;
	}
	
	public function Logout()
	{
		$this->ulogin->SetAutologin($this->username, false);
	}
	
	public function ChangeLanguage($lang)
	{
		$L = new Language();
		$L->SetLanguageName($lang);
		return 'ok';
	}
	
	public function RegisterActionExecution($extAction, $extStatus,
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
	
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
	
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
				$senderId, $senderName, $targetId, $targetName);
	
		unset($U);
	}
	
	public function RegisterActionReject($extAction, $extStatus,
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
	
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
				$senderId, $senderName, $targetId, $targetName);
	
		unset($U);
	}
	
	public function GetUserInfo()
	{
		$U = new User();
		$uId = $U->GetUserIdByName($this->username);
		$userInfo = $U->GetUserInfo($uId);
		unset($U);
	
		return $userInfo;
	}
}

?>