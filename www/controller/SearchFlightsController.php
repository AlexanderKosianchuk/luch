<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 


class SearchFlightController
{
	public $curPage = 'searchFlightPage';
	
	private $ulogin;
	private $username;
	
	public $privilege;
	public $lang;
	public $controllerActions;

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
		$this->controllerActions = (array)$L->GetServiceStrs($this->curPage);
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
	
	public function ShowSearchForm()
	{
		$form = '';
		$form .= sprintf("<div class='search-flight-filter'>");
		$form .= sprintf("<form id='search-form' enctype='multipart/form-data'>");
		
		$Usr = new User();
		$avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->username);
		unset($Usr);
		
		$Bru = new Bru();
		$bruList = $Bru->GetBruList($avalibleBruTypes);
		unset($Bru);
		
		$optionString = "";
		
		$selectedFdr = '';
		foreach($bruList as $bruInfo)
		{
			if($selectedFdr == '') {
				$selectedFdr = $bruInfo['id'];
				$optionString .="<option selected='selected' value='".$bruInfo['id']."'>".$bruInfo['bruType']."</option>";
			} else {
				$optionString .="<option value='".$bruInfo['id']."'>".$bruInfo['bruType']."</option>";
			}
		}
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->bruType);
		$form .= sprintf("<select id='fdrForFilter' name='fdr' class='search-form-inputs'>%s</select>", $optionString);
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->bort);
		$form .= sprintf("<input name='bort' type='text' class='search-form-inputs' value=''/>");

		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->voyage);
		$form .= sprintf("<input name='voyage' type='text' class='search-form-inputs' value=''/>");
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->departureAirport);
		$form .= sprintf("<input type='text' name='departureAirport' class='search-form-inputs' value=''/>");
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->arrivalAirport);
		$form .= sprintf("<input type='text' name='arrivalAirport' class='search-form-inputs' value=''/>");
				
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->flightDateFrom);
		$form .= sprintf("<input type='time' name='flightTimeFrom' class='search-form-inputs'><br>");
		$form .= sprintf("<input type='date' name='flightDateFrom' class='search-form-inputs'>");
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->flightDateTo);
		$form .= sprintf("<input type='time' name='flightTimeTo' class='search-form-inputs'/><br>");
		$form .= sprintf("<input type='date' name='flightDateTo' class='search-form-inputs'/>");
		
		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->performer);
		$form .= sprintf("<input name='performer' type='text' class='search-form-inputs' value=''/>");

		$form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->aditionalInfo);
		$form .= sprintf("<input name='aditionalInfo' type='text' class='search-form-inputs' value=''/>");
		
		$form .= "</form>";
		$form .= "</div>";
		
		$alg = $this->BuildSearchFlightAlgorithmesList($selectedFdr);
		
		$form .= sprintf("<div class='search-form-alg'><form id='search-form-alg-list'>%s</form></div>", $alg);
		$form .= sprintf("<div id='search-form-flights' class='search-form-flights'>&nbsp;</div>");
		$form .= "<div class='search-form-clear'></div>";
		return $form;
	}
	
	public function BuildSearchFlightAlgorithmesList($fdrId) 
	{
		$SF = new SearchFlights();
		$alg = $SF->GetSearchAlgorithmes($fdrId);
		
		$form = '';
		foreach ($alg as $item) {
			$form .= '<p><label class="search-form-alg-item-rows"><input name="alg" type="radio" class="search-form-alg-items" value="'.$item['id'].'"><span> '.$item['name'].'</span></label></p>';
		}
		
		return $form;
	}
	
	public function GetUserInfo()
	{
		$U = new User();
		$uId = $U->GetUserIdByName($this->username);
		$userInfo = $U->GetUserInfo($uId);
		unset($U);
	
		return $userInfo;
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
}