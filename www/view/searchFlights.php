<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/SearchFlightsController.php");

$M = new SearchFlightController($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	if($M->action == $M->controllerActions["showSearchForm"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->action;					
				$html = $M->ShowSearchForm();				
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => $html

				);
	
				echo json_encode($answ);
				exit();
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page search.php";
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
	
		unset($U);	
	} else if($M->action == $M->controllerActions["getFilters"]) {
		$U = new User();
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['fdrId']))
			{
				$action = $M->action;
				$fdrId = $M->data['fdrId'];
				$html = $M->BuildSearchFlightAlgorithmesList($fdrId);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => $html
				);
	
				echo json_encode($answ);
				exit();
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page search.php";
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
	
		unset($U);
	} else if($M->action == $M->controllerActions["applyFilter"]) {
		$U = new User();
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['algId']) &&
					isset($M->data['form']))
			{
				$action = $M->action;
				$algId = $M->data['algId'];
				parse_str($M->data['form'], $form);
				
				$flightIds = $M->GetFlightsByCriteria($form);
				$idsArr = $M->SearchByAlgorithm($algId, $flightIds);
				$html = $M->BuildFlightList($idsArr);
				$M->RegisterActionExecution($action, "executed");
	
				if(empty($html)) {
					$html = $M->lang->searchBroughtNoResult;
				}
				
				$answ = array(
						'status' => 'ok',
						'data' => $html
				);
	
				echo json_encode($answ);
				exit();
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page search.php";
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
	
		unset($U);
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


