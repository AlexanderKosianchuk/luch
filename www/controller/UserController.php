<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

class UserController
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
		$table .= sprintf("<th name='login'>%s</th>", $this->lang->userLogin);
		$table .= sprintf("<th name='lang'>%s</th>", $this->lang->userLang);
		$table .= sprintf("<th name='company'>%s</th>", $this->lang->userCompany);
		$table .= sprintf("<th name='privilege'>%s</th>", $this->lang->userPrivilege);
		$table .= sprintf("<th name='logo'>%s</th>", $this->lang->userLogo);
	
		$table .= sprintf("</tr></thead><tfoot style='display: none;'><tr>");
	
		for($i = 0; $i < 6; $i++) {
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
					str_replace(",", ", ", $user['privilege']),
					$user['logo']
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
		
	public function BuildCRUuserModal()
	{
		//$this->lang->userModal
		$Usr = new User();
		$privilege = $Usr->allPrivilegeArray;
		$uId = $Usr->GetUserIdByName($this->username);
		$userInfo = $Usr->GetUserInfo($uId);
		$role = $userInfo['role'];
		
		$form = sprintf("<div id='user-cru-modal'><form id='user-cru-form'>");

		$privilegeOptions = "<tr><td>".$this->lang->userPrivilege."</td><td align='center'>";
		$privilegeOptions .= "<select id='privilege' name='privilege' multiple size='10' style='width: 335px'>";
		
		foreach ($privilege as $val)
		{
			$privilegeOptions .= "<option>".$val."</option>";
		}
		$privilegeOptions .= "</select></td></tr>";
		
		$roleOptions = '';
		if($Usr::isAdmin($role)) {
			$roleOptions .= "<tr><td>".$this->lang->userRole."</td><td align='center'>";
			$roleOptions .= "<select name='role' size='3' style='width: 335px'>";
			foreach ($Usr::$role as $val)
			{
				$roleOptions .= "<option>".$val."</option>";
			}
			$roleOptions .= "</select></td></tr>";
		}		
	
		$form .= sprintf("<table align='center'>
			<p class='Label'>%s</p>
			<tr><td>%s</td><td>
				<input id='login' type='text' name='login' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='company' type='text' name='company' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='pwd1' type='password' name='pwd' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='pwd2' type='password' name='pwd2' size='50'>
			</td></tr>
				%s
				%s
			<tr><td>%s</td><td align='center'>
				<input id='file' type='file' name='logo'>
			</td></tr>
			<tr style='visibility:hidden;'><td>
				Nonce:
			</td><td>
				<input id='nonce' type='text' id='nonce' name='nonce' value='%s'>
			</td></tr>
		</table>",
				$this->lang->userCreationForm,
				$this->lang->userName,
				$this->lang->company,
				$this->lang->pass,
				$this->lang->repeatPass,
				$privilegeOptions,
				$roleOptions,
				$this->lang->userLogo,
				ulNonce::Create('login'),
				$this->lang->userCreate);
	
		//==========================================
		//access to flights
		//==========================================
		if(in_array(User::$PRIVILEGE_SHARE_FLIGHTS, $this->privilege))
		{
			$form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForFlights);
				
			$Fl = new Flight();
			$avaliableFlightIds = $Usr->GetAvaliableFlights($this->username);
			$avaliableFlights = $Fl->PrepareFlightsList($avaliableFlightIds);
				
			if(count($avaliableFlights) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableFlights) > 30)
				{
					$form .= sprintf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				$form .= sprintf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				$form .= sprintf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bortNum,
						$this->lang->voyage,
						$this->lang->flightDate,
						$this->lang->bruTypeName,
						$this->lang->author,
						$this->lang->departureAirport,
						$this->lang->arrivalAirport,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableFlights as $fligthInfo)
				{
					if($greyHightLight)
					{
						$form .= sprintf("<tr>");
					}
					else
					{
						$form .= sprintf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
						
					$form .= sprintf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input name='flightsAvaliable' data-flightid='%s' type='checkbox'/>
							</td></tr>",
							$fligthInfo['bort'],
							$fligthInfo['voyage'],
							$fligthInfo['flightDate'],
							$fligthInfo['bruType'],
							$fligthInfo['performer'],
							$fligthInfo['departureAirport'],
							$fligthInfo['arrivalAirport'],
							$fligthInfo['id']);
				}
				$form .= sprintf("</table>");
	
				if(count($avaliableFlights) > 30)
				{
					$form .= sprintf("</div>");
				}
			}
			else
			{
				$form .= sprintf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
						$this->lang->noDataToOpenAccess);
			}
			$form .= sprintf("</div>");
			unset($Fl);
		}
	
		//==========================================
		//access to brutypes
		//==========================================
		if(in_array(User::$PRIVILEGE_SHARE_BRUTYPES, $this->privilege))
		{
			$form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForBruTypes);
	
			$Bru = new Bru();
			$avaliableIds = $Usr->GetAvaliableBruTypes($this->username);
			$avaliableBruTypes = $Bru->GetBruList($avaliableIds);
	
			if(count($avaliableBruTypes) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableBruTypes) > 30)
				{
					$form .= sprintf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				$form .= sprintf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				$form .= sprintf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bruTypesName,
						$this->lang->bruTypesStepLenth,
						$this->lang->bruTypesFrameLength,
						$this->lang->bruTypesWordLength,
						$this->lang->bruTypesAuthor,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableBruTypes as $bruTypeInfo)
				{
					if($greyHightLight)
					{
						$form .= sprintf("<tr>");
					}
					else
					{
						$form .= sprintf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
	
					$form .= sprintf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input name='FDRsAvaliable' data-brutypeid='%s' type='checkbox'/>
							</td></tr>",
							$bruTypeInfo['bruType'],
							$bruTypeInfo['stepLength'],
							$bruTypeInfo['frameLength'],
							$bruTypeInfo['wordLength'],
							$bruTypeInfo['author'],
							$bruTypeInfo['id']);
				}
				$form .= sprintf("</table>");
	
				if(count($avaliableBruTypes) > 30)
				{
					$form .= sprintf("</div>");
				}
			}
			else
			{
				$form .= sprintf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
						$this->lang->noDataToOpenAccess);
			}
			$form .= sprintf("</div>");
			unset($Bru);
		}
	
		//==========================================
		//access to users
		//==========================================
		if(in_array(User::$PRIVILEGE_SHARE_USERS, $this->privilege))
		{
			$form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForUsers);
	
			//$Usr = new User();
			$avaliableIds = $Usr->GetAvaliableUsers($this->username);
			$avaliableUsers = $Usr->GetUsersListByAvaliableIds($avaliableIds);	
			
			if(count($avaliableUsers) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableUsers) > 30)
				{
					$form .= sprintf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				$form .= sprintf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				$form .= sprintf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->userLogin,
						$this->lang->userCompany,
						$this->lang->userAuthor,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableUsers as $userInfo)
				{
					if($greyHightLight)
					{
						$form .= sprintf("<tr>");
					}
					else
					{
						$form .= sprintf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
	
					$form .= sprintf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input name='usersAvaliable' data-userid='%s' type='checkbox'/>
							</td></tr>",
							$userInfo['login'],
							$userInfo['company'],
							$userInfo['author'],
							$userInfo['id']);
				}
				$form .= sprintf("</table>");
	
				if(count($avaliableUsers) > 30)
				{
					$form .= sprintf("</div>");
				}
			}
			else
			{
				$form .= sprintf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
						$this->lang->noDataToOpenAccess);
			}
			$form .= sprintf("</div>");
		}
	
		$form .= '</form></div>';
		unset($Usr);
		
		return $form;
	}
}

?>