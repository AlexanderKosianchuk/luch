<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/UserController.php");

$M = new UserController($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	$U = new User();
	
	if($M->action == $M->userActions["userLogout"])
	{
		if(in_array($U::$PRIVILEGE_OPTIONS_USERS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->action;					
				
				unset($_SESSION['uid']);
				unset($_SESSION['username']);
				unset($_SESSION['loggedIn']);
				
				$M->Logout();
	
				$answ = array(
						'status' => 'ok'
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page user.php";
				$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				echo(json_encode($answ));
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["userChangeLanguage"]) {
		if(in_array($U::$PRIVILEGE_OPTIONS_USERS, $M->privilege))
		{
			if(isset($M->data['lang']))
			{
				$action = $M->action;
				$lang = $M->data['lang'];					
				
				$M->ChangeLanguage($lang);
	
				$answ = array(
						'status' => 'ok'
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page user.php";
				$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				echo(json_encode($answ));
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["buildUserTable"]) {
		if(in_array($U::$PRIVILEGE_OPTIONS_USERS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->action;		
				$table = $M->BuildUserTable();
				$M->RegisterActionExecution($action, "executed", 0, 'getUserList', '', '');
				
				$answ = [
					"status" => "ok",
					"data" => $table,
					"sortCol" => 2, // id
					"sortType" => 'desc'
				];
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page user.php";
				$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				echo(json_encode($answ));
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["segmentTable"]) {
		if(in_array($U::$PRIVILEGE_VIEW_USERS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$aoData = $M->data['data'];
				$sEcho = $aoData[sEcho]['value'];
				$iDisplayStart = $aoData[iDisplayStart]['value'];
				$iDisplayLength = $aoData[iDisplayLength]['value'];
				$action = $M->action;
	
				$sortValue = count($aoData) - 3;
				$sortColumnName = 'id';
				$sortColumnNum = $aoData[$sortValue]['value'];
				$sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);
	
				switch ($sortColumnNum){
					case(1):
						{
							$sortColumnName = 'login';
							break;
						}
					case(2):
						{
							$sortColumnName = 'lang';
							break;
						}
					case(3):
						{
							$sortColumnName = 'company';
							break;
						}
				}
	
				$totalRecords = -1;
				$aaData["sEcho"] = $sEcho;
				$aaData["iTotalRecords"] = $totalRecords;
				$aaData["iTotalDisplayRecords"] = $totalRecords;
	
				$M->RegisterActionExecution($action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);
	
				$tableSegment = $M->BuildTableSegment($sortColumnName, $sortColumnType);
				$aaData["aaData"] = $tableSegment;
	
				echo(json_encode($aaData));
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page user.php";
						$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
						echo(json_encode($answ));
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["modal"]) {	
		if(in_array($U::$PRIVILEGE_ADD_USERS, $M->privilege))
		{
			$modal = $M->BuildCreateUserModal();
			$action = $M->action;		
			$M->RegisterActionExecution($action, "executed");
			echo(json_encode($modal));
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["updateUser"]) {
		if(in_array($U::$PRIVILEGE_EDIT_USERS, $M->privilege))
		{
			if(isset($M->data) && isset($M->data['userid']))
			{
				$userid = $M->data['userid'];
				$modal = $M->BuildUpdateUserModal($userid);
				$action = $M->action;
				$M->RegisterActionExecution($action, "executed");
				echo(json_encode($modal));
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page user.php";
						$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
						echo(json_encode($answ));
						exit();
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
		}
	} else if($M->action == $M->userActions["saveUser"]) {
		if(in_array($U::$PRIVILEGE_ADD_USERS, $M->privilege))
		{			
			if(isset($M->data) && 
					isset($_FILES['logo']) && 
					isset($_FILES['logo']['tmp_name']))
			{
				$form = $_POST;
				$file = $_FILES['logo']['tmp_name'];
				$action = $M->action;
				
				$answ = [
					'status' => 'ok'
				];
							
				if(!isset($form['login'])) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->pleaseInputUserLogin
					];
				}
				
				if(!isset($form['company'])) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->pleaseInputUserCompany
					];
				}
				
				if(!isset($form['pwd']) || !isset($form['pwd2'])) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->pleaseInputPass
					];
				}
				
				if($form['pwd'] != $form['pwd2']) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->passwordRepeatingIncorrect
					];
				}	
				
				if($form['pwd'] != $form['pwd2']) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->passwordRepeatingIncorrect
					];
				}
				
				if(!isset($form['privilege'])) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->pleaseChoosePrivilege
					];
				}
				
				if(!isset($form['role'])) {
					$answ = [
						'status' => 'err',
						'error' => $M->lang->pleaseChooseRole
					];
				}
				
				if($answ['status'] == 'ok') {
					$resMsg = $M->CreateUser($form, $file);
					
					if($resMsg != '') {
						$answ = [
								'status' => 'err',
								'error' => $resMsg
						];
					}
				}
										
				$M->RegisterActionExecution($action, "executed");
				echo(json_encode($answ));
				exit();
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page user.php";
				$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				echo(json_encode($answ));
				exit();
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
			exit();
		}
	} else if($M->action == $M->userActions["deleteUser"]) {
		if(in_array($U::$PRIVILEGE_DEL_USERS, $M->privilege))
		{			
			if(isset($M->data) && isset($M->data['userIds']))
			{
				$userIds = $M->data['userIds'];
				$action = $M->action;
				
				$answ = [
					'status' => 'ok'
				];
				
				if(!$M->DeleteUser($userIds)) {
					$answ["status"] = "err";
					$answ["error"] = $M->lang->errorDuringUserDeletion;
				}
				
				$M->RegisterActionExecution($action, "executed");
				echo(json_encode($answ));
				exit();
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page user.php";
				$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				echo(json_encode($answ));
				exit();
			}
		}
		else
		{
	
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			$M->RegisterActionReject($M->action, "rejected", 0, 'notAllowedByPrivilege');
			echo(json_encode($answ));
			exit();
		}
	} else {
		$msg = "Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".";
		$M->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
		error_log($msg);
		echo($msg);
		exit();
	}
}
else 
{
	$msg = "Authorization error. Page: " . $M->currPage;
	$M->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
	error_log($msg);
	echo($msg);
	exit();
}

?>


