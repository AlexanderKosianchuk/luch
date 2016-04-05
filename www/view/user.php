<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/UserController.php");

$M = new UserController($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	if($M->action == $M->userActions["userLogout"])
	{
		$U = new User();
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
	
		unset($U);
	} else if($M->action == $M->userActions["userChangeLanguage"]) {
		$U = new User();
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
	
		unset($U);
	} else if($M->action == $M->userActions["buildUserTable"]) {
		$U = new User();
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
	
		unset($U);
	} else if($M->action == $M->userActions["segmentTable"]) {
		$U = new User();
	
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
						json_encode($_POST) . ". Page flights.php";
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
	
		unset($U);
	} else {
		$msg = "Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".";
		$M->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
		error_log($msg);
		echo($msg);
	}
}
else 
{
	$msg = "Authorization error. Page: " . $M->currPage;
	$M->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
	error_log($msg);
	echo($msg);
}

?>


