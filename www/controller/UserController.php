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
			$img = '';
			if(!empty($user['logo'])) {
				$img = '<div id="userlist-img-container">
			        <div class="userlist-img-image-container">
			            <img src="data:image/jpeg;base64,' . base64_encode($user['logo']) . '">
			        </div>
			    </div>​';
			}
			
			$tableSegment[] = array(
					"<input class='ItemsCheck' data-type='user' data-userid='".$user['id']."' type='checkbox'/>",
					$user['login'],
					$user['lang'],
					$user['company'],
					str_replace(",", ", ", $user['privilege']),
					$img
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
	
	private function printTableAvaliability($cellNames, $avaliableRows, $rowKeys, $dataKey, $avaliable = []) {
		$form = '';
		//if more than 30 rows make table scrollable
		if(count($avaliableRows) > 30)
		{
			$form .= sprintf("<div class='items-avaliability-table-wrap'>");
		}
			
		$form .= sprintf("<table class='items-avaliability-table'>");
		$form .= sprintf("<tr class='items-avaliability-table-header'>");
		for($ii = 0; $ii < count($cellNames) - 1; $ii++) {
			$form .= sprintf("<td class='items-avaliability-table-cell'>%s</td>", $cellNames[$ii]);
		}
		$form .= sprintf("<td class='items-avaliability-table-cell' width='50px'>%s</td>", $cellNames[count($cellNames) - 1]);
		$form .= sprintf("</tr>");
	
		foreach ($avaliableRows as $rowInfo) {
			$form .= sprintf("<tr class='table-stripe'>");
			for($ii = 1; $ii < count($rowKeys); $ii++) {
				$form .= sprintf("<td class='items-avaliability-table-cell'>%s</td>", $rowInfo[$rowKeys[$ii]]);
			}
			
			$checked = '';
			if(in_array($rowInfo[$rowKeys[0]], $avaliable)) {
				$checked = " checked='checked' ";
			}
			
			$form .= sprintf("<td class='items-avaliability-table-cell' align='center'>
							<input name='".$dataKey."Avaliable[]' value='%s' type='checkbox' ".$checked."/>
						</td>", $rowInfo[$rowKeys[0]]); // always id should be
			$form .= sprintf("</tr>");
		}
		$form .= sprintf("</table>");
			
		if(count($avaliableRows) > 30) {
			$form .= sprintf("</div>");
		}
			
		return $form;
	}
		
	public function BuildCreateUserModal()
	{
		//$this->lang->userModal
		$Usr = new User();
		$privilege = $Usr->allPrivilegeArray;
		$uId = $Usr->GetUserIdByName($this->username);
		$authorInfo = $Usr->GetUserInfo($uId);
		$role = $authorInfo['role'];
		
		$form = sprintf("<div id='user-cru-modal'><form id='user-cru-form' enctype='multipart/form-data'>");

		$privilegeOptions = "<tr><td>".$this->lang->userPrivilege."</td><td align='center'>";
		$privilegeOptions .= "<select id='privilege' name='privilege[]' multiple size='10' style='width: 335px'>";
		
		foreach ($authorInfo['privilege'] as $val)
		{
			$selected = '';
			if(in_array($val, $privilege)) {
				$selected = " selected='selected' ";
			}
			$privilegeOptions .= "<option ".$selected.">".$val."</option>";
		}
		
		$roleOptions = '';
		if($Usr::isAdmin($role)) {
			$roleOptions .= "<tr><td>".$this->lang->userRole."</td><td align='center'>";
			$roleOptions .= "<select name='role[]' size='3' style='width: 335px'>";
			foreach ($Usr::$role as $val)
			{
				$roleOptions .= "<option selected='selected'>".$val."</option>";
			}
			$roleOptions .= "</select></td></tr>";
		} else {
			$roleOptions .= "<input type='hidden' name='role' size='50' value='user'>";
		}		
	
		$form .= sprintf("<table align='center'>
			<p class='Label'>%s</p>
			<div class='user-creation-info'><p>%s</p></div>
			<tr><td>%s</td><td>
				<input type='text' name='login' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input type='text' name='company' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input class='user-pwd' type='password' name='pwd' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input class='user-pwd' type='password' name='pwd2' size='50'>
			</td></tr>
				%s
				%s
			<tr><td>%s</td><td align='center'>
				<input type='file' name='logo'>
			</td></tr>
		</table>",
				$this->lang->userCreationForm,
				'',
				$this->lang->userName,
				$this->lang->company,
				$this->lang->pass,
				$this->lang->repeatPass,
				$privilegeOptions,
				$roleOptions,
				$this->lang->userLogo);
		
		$form .= sprintf("<input type='text' name='nonce' value='%s' style='visibility:hidden;'/>", ulNonce::Create('login'));
		$form .= sprintf("<input type='text' name='action' value='%s' style='visibility:hidden;'/>", $this->userActions["createUser"]);
		$form .= sprintf("<input type='text' name='data' value='dummy' style='visibility:hidden;'/>");
		
		//==========================================
		//access to flights
		//==========================================
		if(in_array(User::$PRIVILEGE_SHARE_FLIGHTS, $this->privilege)) {
			$form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForFlights);
				
			$Fl = new Flight();
			$avaliableFlightIds = $Usr->GetAvaliableFlights($this->username);
			$avaliableFlights = $Fl->PrepareFlightsList($avaliableFlightIds);
				
			if(count($avaliableFlights) > 0) {
				$headerLables = [
					$this->lang->bortNum,
					$this->lang->voyage,
					$this->lang->flightDate,
					$this->lang->bruTypeName,
					$this->lang->author,
					$this->lang->departureAirport,
					$this->lang->arrivalAirport,
					$this->lang->access
				];
				
				$rowsInfoKeys = [
					'id',
					'bort',
					'voyage',
					'flightDate',
					'bruType',
					'performer',
					'departureAirport',
					'arrivalAirport'
				];
				
				$form .= $this->printTableAvaliability($headerLables, $avaliableFlights, $rowsInfoKeys, 'flights');
			} else {
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
	
			if(count($avaliableBruTypes) > 0) {
				$headerLables = [
					$this->lang->bruTypesName,
					$this->lang->bruTypesStepLenth,
					$this->lang->bruTypesFrameLength,
					$this->lang->bruTypesWordLength,
					$this->lang->bruTypesAuthor,
					$this->lang->access
				];
				
				$rowsInfoKeys = [
					'id',
					'bruType',
					'stepLength',
					'frameLength',
					'wordLength',
					'author'
				];
				
				$form .= $this->printTableAvaliability($headerLables, $avaliableBruTypes, $rowsInfoKeys, 'FDRs');
			} else {
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
				$headerLables = [
					$this->lang->userLogin,
					$this->lang->userCompany,
					$this->lang->userAuthor,
					$this->lang->access
				];
				
				$rowsInfoKeys = [
						'id',
						'login',
						'company',
						'author',
				];
				
				$form .= $this->printTableAvaliability($headerLables, $avaliableUsers, $rowsInfoKeys, 'users');
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
	
	public function BuildUpdateUserModal($updatedUsersId)
	{
		//$this->lang->userModal
		$Usr = new User();
		$privilege = $Usr->allPrivilegeArray;
		$authorId = $Usr->GetUserIdByName($this->username);
		$authorInfo = $Usr->GetUserInfo($authorId);
		$userInfo = $Usr->GetUserInfo($updatedUsersId);
		$role = $userInfo['role'];
		$privilege = explode(",", $userInfo['privilege']);
	
		$form = sprintf("<div id='user-cru-modal'><form id='user-cru-form' enctype='multipart/form-data'>");
	
		$privilegeOptions = "<tr><td>".$this->lang->userPrivilege."</td><td align='center'>";
		$privilegeOptions .= "<select id='privilege' name='privilege[]' multiple size='10' style='width: 335px'>";
	
		$authorPrivilege = explode(',', $authorInfo['privilege']);
		foreach ($authorPrivilege as $val)
		{
			$selected = '';
			if(in_array($val, $privilege)) {
				$selected = " selected='selected' ";
			}
			$privilegeOptions .= "<option ".$selected.">".$val."</option>";
		}
		$privilegeOptions .= "</select></td></tr>";
	
		$roleOptions = '';
		if($Usr::isAdmin($role)) {
			$roleOptions .= "<tr><td>".$this->lang->userRole."</td><td align='center'>";
			$roleOptions .= "<select name='role[]' size='3' style='width: 335px'>";
			foreach ($Usr::$role as $val)
			{
				$selected = '';
				if($val == $role) {
					$selected = " selected='selected' ";
				}
				$roleOptions .= "<option ".$selected.">".$val."</option>";
			}
			$roleOptions .= "</select></td></tr>";
		} else {
			$roleOptions .= "<input type='hidden' name='role' size='50' value='user'>";
		}
	
		$form .= sprintf("<table align='center'>
			<p class='Label'>%s</p>
			<div class='user-creation-info'><p>%s</p></div>
			<tr><td>%s</td><td>
				<input type='text' name='login' size='50' value='%s' disabled='disabled'>
			</td></tr>
			<tr><td>%s</td><td>
				<input type='text' name='company' size='50' value='%s'>
			</td></tr>
			<tr><td>%s</td><td>
				<input class='user-pwd' type='password' name='pwd' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input class='user-pwd' type='password' name='pwd2' size='50'>
			</td></tr>
				%s
				%s
			<tr><td>%s</td><td align='center'>
				<input type='file' name='logo'>
			</td></tr>
		</table>",
				$this->lang->userCreationForm,
				'',
				$this->lang->userName,
				$userInfo['login'],
				$this->lang->company,
				$userInfo['company'],
				$this->lang->pass,
				$this->lang->repeatPass,
				$privilegeOptions,
				$roleOptions,
				$this->lang->userLogo);
	
		$form .= sprintf("<input type='text' name='nonce' value='%s' style='visibility:hidden;'/>", ulNonce::Create('login'));
		$form .= sprintf("<input type='text' name='action' value='%s' style='visibility:hidden;'/>", $this->userActions["updateUser"]);
		$form .= sprintf("<input type='text' name='data' value='dummy' style='visibility:hidden;'/>");
		$form .= sprintf("<input type='text' name='useridtoupdate' value='%s' style='visibility:hidden;'/>", $updatedUsersId);
		
		//==========================================
		//access to flights
		//==========================================
		if(in_array(User::$PRIVILEGE_SHARE_FLIGHTS, $this->privilege)) {
			$form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForFlights);
	
			$Fl = new Flight();
			$avaliableFlightIds = $Usr->GetAvaliableFlights($this->username);
			$avaliableFlights = $Fl->PrepareFlightsList($avaliableFlightIds);
			$attachedFlightIds = $Usr->GetAvaliableFlights($userInfo['login']);
	
			if(count($avaliableFlights) > 0) {
				$headerLables = [
					$this->lang->bortNum,
					$this->lang->voyage,
					$this->lang->flightDate,
					$this->lang->bruTypeName,
					$this->lang->author,
					$this->lang->departureAirport,
					$this->lang->arrivalAirport,
					$this->lang->access
				];
	
				$rowsInfoKeys = [
					'id',
					'bort',
					'voyage',
					'flightDate',
					'bruType',
					'performer',
					'departureAirport',
					'arrivalAirport'
				];
	
				$form .= $this->printTableAvaliability(
					$headerLables, 
					$avaliableFlights,
					$rowsInfoKeys, 
					'flights', 
					$attachedFlightIds
				);
			} else {
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
			$attachedFDRIds = $Usr->GetAvaliableBruTypes($userInfo['login']);
	
			if(count($avaliableBruTypes) > 0) {
				$headerLables = [
					$this->lang->bruTypesName,
					$this->lang->bruTypesStepLenth,
					$this->lang->bruTypesFrameLength,
					$this->lang->bruTypesWordLength,
					$this->lang->bruTypesAuthor,
					$this->lang->access
				];
	
				$rowsInfoKeys = [
					'id',
					'bruType',
					'stepLength',
					'frameLength',
					'wordLength',
					'author'
				];
	
				$form .= $this->printTableAvaliability(
					$headerLables, 
					$avaliableBruTypes, 
					$rowsInfoKeys, 
					'FDRs', 
					$attachedFDRIds
				);
			} else {
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
			$attachedUserIds = $Usr->GetAvaliableUsers($userInfo['login']);
				
			if(count($avaliableUsers) > 0)
			{
				$headerLables = [
					$this->lang->userLogin,
					$this->lang->userCompany,
					$this->lang->userAuthor,
					$this->lang->access
				];
	
				$rowsInfoKeys = [
					'id',
					'login',
					'company',
					'author',
				];
	
				$form .= $this->printTableAvaliability(
					$headerLables, 
					$avaliableUsers, 
					$rowsInfoKeys, 
					'users',
					$attachedUserIds
				);
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
	
	public function CreateUser($form, $file) 
	{
		$login = $form['login'];
		$company = $form['company'];
		$pwd = $form['pwd'];
		$privilege = $form['privilege'];
		$role = $form['role'];
		if(is_array($role)) {
			$role = $role[count($role) - 1];
		}
		$author = $this->username;
		$permittedFlights = isset($form['flightsAvaliable']) ? $form['flightsAvaliable'] : [];
		$permittedBruTypes = isset($form['FDRsAvaliable']) ? $form['FDRsAvaliable'] : [];
		$permittedUsers = isset($form['usersAvaliable']) ? $form['usersAvaliable'] : [];
		$file = str_replace("\\", "/", $file);
		
		$U = new User();
		$msg = '';
		
		if (!$U->CheckUserPersonalExist($login)) {			
			$ulogin = new uLogin();
			
			if ($ulogin->CreateUser($login, $pwd)) {
				$U->CreateUserPersonal($login, $privilege, $author, $company, $role, $file);				
				$createdUserId = $U->GetIdByUsername($login);
				$authorId = $U->GetUserIdByName($this->username);
				$U->SetUsersAvaliable($author, $createdUserId, $authorId);
					
				foreach($permittedFlights as $id) {
					$U->SetFlightAvaliable($author, $id, $createdUserId);
				}
				
				foreach($permittedBruTypes as $id) {
					$U->SetBruTypeAvaliable($author, $id, $createdUserId);
				}
				
				foreach($permittedUsers as $id) {
					$U->SetUsersAvaliable($author, $id, $createdUserId);
				}
			} else {
				$msg = $this->lang->userAlreadyExistOrImpossible;
			}
		} else {
			$msg = $this->lang->userAlreadyExist;
		}
		
		return $msg;
	}
	
	public function UpdateUser($userIdToUpdate, $form, $file)
	{
		$avaliableForUpdate = false;
		$U = new User();
		$author = $this->username;
		$authorId = $U->GetUserIdByName($author);
		$authorInfo = $U->GetUserInfo($authorId);
		$userInfo = $U->GetUserInfo($userIdToUpdate);
		if($U::isAdmin($authorInfo['role'])) {
			$avaliableForUpdate = true;
		} else {
			$avaliableIds = $U->GetAvaliableUsers($author);
			if(in_array($userIdToUpdate, $avaliableIds)) {
				$avaliableForUpdate = true;
			}
		}
		
		if(isset($form['pwd'])) {
			$ulogin = new uLogin();
			$uloginUid = $ulogin->Uid($userInfo['login']);
			$ulogin->SetPassword($uloginUid, $form['pwd']);
		}

		$prsonalDataToUpdata = [];
		if(isset($form['company'])) {
			$prsonalDataToUpdata['company'] = $form['company'];
		}
		
		if(isset($form['privilege'])) {
			$prsonalDataToUpdata['privilege'] = implode(",", $form['privilege']);
		}
		
		if(isset($form['role'])) {
			$prsonalDataToUpdata['role'] = $form['role'];
		}
		
		if($file !== null) {
			$prsonalDataToUpdata['logo'] = str_replace("\\", "/", $file);
		}
		
		$U->UpdateUserPersonal($userIdToUpdate, $prsonalDataToUpdata);

		$permittedFlights = isset($form['flightsAvaliable']) ? $form['flightsAvaliable'] : [];
		$permittedBruTypes = isset($form['FDRsAvaliable']) ? $form['FDRsAvaliable'] : [];
		$permittedUsers = isset($form['usersAvaliable']) ? $form['usersAvaliable'] : [];
		
		$msg = '';
		$U->DeleteUserAvaliableData($userIdToUpdate);
		foreach($permittedFlights as $id) {
			$U->SetFlightAvaliable($author, $id, $userIdToUpdate);
		}

		foreach($permittedBruTypes as $id) {
			$U->SetBruTypeAvaliable($author, $id, $userIdToUpdate);
		}

		foreach($permittedUsers as $id) {
			$U->SetUsersAvaliable($author, $id, $userIdToUpdate);
		}
	
		return $msg;
	}
	
	public function DeleteUser($userIds) 
	{
		$username = $this->username;
		$ulogin = new uLogin();
		$U = new User();
		
		foreach ($userIds as $userDeleteId) {
			if(is_int(intval($userDeleteId))) {
				$userInfo = $U->GetUserInfo($userDeleteId);
				$login = $userInfo['login'];
			
				$uloginUid = $ulogin->Uid($login);
				$ulogin->DeleteUser($uloginUid);
			
				$U->DeleteUserPersonal($login);
				$U->DeleteUserAvaliableData($userDeleteId);
				$U->DeleteUserAvaliabilityForUsers($userDeleteId);
			}
		}
		
		return true;
	}
}
