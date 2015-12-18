<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/UserModel.php");

$M = new UserModel($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	$M->GetUserPrivilege();	
	
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
	} else if($M->action == $M->userActions["userChangeLanguage"])
	{
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
	}
	else 
	{
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


