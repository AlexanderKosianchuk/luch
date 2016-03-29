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
		
		$this->GetUserPrivilege();
		$usrLang = '';
		if(isset($this->username) && ($this->username != '')) {
			$Usr = new User();
			$usrInfo = $Usr->GetUsersInfo($this->username);
			$usrLang = $usrInfo['lang'];
			unset($Usr);
		}
				
		$L = new Language();
		$L->SetLanguageName($usrLang);
		$this->userLang = $L->GetLanguageName();
		$this->lang = $L->GetLanguage($this->curPage);
		$this->userActions = (array)$L->GetServiceStrs($this->curPage);
		unset($L);
		
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
		$this->username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
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
		unset($L);
		
		$Usr = new User();
		$Usr->SetUserLanguage($this->username, $lang);
		unset($Usr);
		
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
	
	public function GetUserList()
	{
		$userInfo = $this->GetUserInfo();
		$U = new User();
		
		$avalibleUsers = $U->GetUsersList($userInfo['id']);
		
		unset($U);
		
		return $avalibleUsers;
	}
		
	public function BuildUserTable()
	{
		$table = sprintf("<table id='userTable' cellpadding='0' cellspacing='0' border='0'>
				<thead><tr>");
	
		$table .= sprintf("<th name='checkbox' style='width:%s;'>%s</th>", "1%", "<input id='tableCheckAllItems' type='checkbox'/>");
		$table .= sprintf("<th name='login'>%s</th>", '1');
		$table .= sprintf("<th name='lang'>%s</th>", '2');
		$table .= sprintf("<th name='company'>%s</th>", '3');
		$table .= sprintf("<th name='company'>%s</th>", '4');
	
		$table .= sprintf("</tr></thead><tfoot style='display: none;'><tr>");
	
		for($i = 0; $i < 5; $i++) {
			$table .= sprintf("<th></th>");
		}
	
		$table .= sprintf("</tr></tfoot><tbody></tbody></table>");
		return $table;
	}
	
	public function BuildTableSegment($extOrderColumn, $extOrderType)
	{
		$orderColumn = $extOrderColumn;
		$orderType = $extOrderType;
	
		$userList = $this->GetUserList();
	
		$tableSegment = [];
	
		foreach($userList as $user)
		{				
			$tableSegment[] = array(
					"<input class='ItemsCheck' data-type='user' data-iserid='".$user['id']."' type='checkbox'/>",
					$user['login'],
					$user['lang'],
					$user['company'],
					str_replace(",", ", ", $user['privilege'])
			);
		}
	
		return $tableSegment;
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