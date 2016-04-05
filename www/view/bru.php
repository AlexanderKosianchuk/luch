<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/BruController.php");

$M = new BruController($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	if($M->action == $M->bruActions["putBruTypeContainer"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_VIEW_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->action;					
				$topMenu = $M->PutTopMenu();
				$leftMenu = $M->PutLeftMenu();			
				$workspace = $M->PutWorkspace();
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array(
							'topMenu' => $topMenu,
							'leftMenu' => $leftMenu,
							'workspace' => $workspace,
						)
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["editingBruTypeTemplatesReceiveTplsList"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$action = $M->action;
				$tplsList = $M->GetTplsList($bruTypeId);
				$M->RegisterActionExecution($action, "executed");

				$answ = array(
						'status' => 'ok',
						'data' => array(
								'bruTypeTpls' => $tplsList
						)
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["editingBruTypeTemplatesReceiveParamsList"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$action = $M->action;
				$paramsList = $M->ShowParamList($bruTypeId);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array(
								'bruTypeParams' => $paramsList
						)
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["createTpl"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']) && 
						isset($M->data['name']) &&
						isset($M->data['params']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$name = $M->data['name'];
				$params = $M->data['params'];
				
				$action = $M->action;
				$M->CreateTemplate($bruTypeId, $name, $params);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array()
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["deleteTpl"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']) &&
					isset($M->data['name']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$name = $M->data['name'];
	
				$action = $M->action;
				$M->DeleteTemplate($bruTypeId, $name);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array()
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["defaultTpl"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']) &&
					isset($M->data['name']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$name = $M->data['name'];
	
				$action = $M->action;
				$M->SetDefaultTemplate($bruTypeId, $name);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array()
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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
	else if($M->action == $M->bruActions["updateTpl"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_EDIT_BRUTYPES, $M->privilege))
		{
			if(isset($M->data['bruTypeId']) &&
					isset($M->data['name']) &&
					isset($M->data['tplOldName']) &&
					isset($M->data['params']))
			{
				$bruTypeId = $M->data['bruTypeId'];
				$name = $M->data['name'];
				$tplOldName = $M->data['tplOldName'];
				$params = $M->data['params'];
	
				$action = $M->action;
				$M->DeleteTemplate($bruTypeId, $tplOldName);
				$M->CreateTemplate($bruTypeId, $name, $params);
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array()
				);
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page bru.php";
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


