<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/ViewOptionsModel.php");

$M = new ViewOptionsModel($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	$M->GetUserPrivilege();	
	
	if($M->action == $M->viewOptionsActions["putViewOptionsContainer"]) //show form for uploading
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$topMenu = $M->PutTopMenu();
				$leftMenu = $M->PutLeftMenu();
				$workspace = $M->PutWorkspace();
				
				$data = array(
					'topMenu' => $topMenu,
					'leftMenu' => $leftMenu,
					'workspace' => $workspace
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
				
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page viewOptions.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
		
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getFlightDuration"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
				$flightTiming = $M->GetFlightTiming($flightId);
	
				$data = array(
						'duration' => $flightTiming['duration'],
						'startCopyTime' => $flightTiming['startCopyTime'],
						'stepLength' => $flightTiming['stepLength']
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getParamCodesByTemplate"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) && isset($M->data['tplName']))
			{
				$flightId = $M->data['flightId'];
				$tplName = $M->data['tplName'];
				
				$params = $M->GetTplParamCodes($flightId, $tplName);
	
				$data = array(
						'ap' => $params['ap'],
						'bp' => $params['bp']
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getDefaultTemplateParamCodes"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
	
				$params = $M->GetDefaultTplParams($flightId);
	
				$data = array(
						'ap' => $params['ap'],
						'bp' => $params['bp']
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getBruTypeId"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
				$bruTypeId = $M->GetBruTypeId($flightId);
	
				$data = array(
						'bruTypeId' => $bruTypeId
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getBruTemplates"]) //show form for uploading
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
				$bruTypeTpls = $M->ShowTempltList($flightId);
				
				$data = array(
					'bruTypeTpls' => $bruTypeTpls
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
				
				echo json_encode($answ);
			}
			else 
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
		
		unset($U);
	}
// 	else if($M->action == $M->viewOptionsActions["getParamList"]) //show form for uploading
// 	{
// 		$U = new User();
	
// 		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
// 		{
// 			if(isset($M->data['flightId']))
// 			{
// 				$flightId = $M->data['flightId'];
// 				$bruTypeParams = $M->ShowParamList($flightId);
	
// 				$data = array(
// 						'bruTypeParams' => $bruTypeParams
// 				);
// 				$answ["status"] = "ok";
// 				$answ["data"] = $data;
	
// 				echo json_encode($answ);
// 			}
// 			else
// 			{
// 				$answ["status"] = "err";
// 				$answ["error"] = "Not all nessesary params sent. Post: ".
// 						json_encode($_POST) . ". Page fileUploader.php";
// 				echo(json_encode($answ));
// 			}
// 		}
// 		else
// 		{
// 			$answ["status"] = "err";
// 			$answ["error"] = $M->lang->notAllowedByPrivilege;
// 			echo(json_encode($answ));
// 		}
	
// 		unset($U);
// 	}
	else if($M->action == $M->viewOptionsActions["getParamListGivenQuantity"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
				
				if(isset($M->data['pageNum']))
				{
					$pageNum = $M->data['pageNum'];
					
					$paramsCount = $M->GetParamCount($flightId);
					$bruTypeParams = $M->ShowParamListWithPaging($flightId, $pageNum, PARAMS_PAGING);
					
					$totalPages = intval(ceil(count($paramsCount['bpCount'])/PARAMS_PAGING)) - 1;
					if(count($paramsCount['apCount']) > count($paramsCount['bpCount']))
					{
						$totalPages = intval(ceil(count($paramsCount['apCount'])/PARAMS_PAGING)) - 1;
					}
		
					$data = array(
							'bruTypeParams' => $bruTypeParams,
							'pagination' => true, 
							'pageNum' => $pageNum,
							'totalPages' => $totalPages
					);
					
					$answ["status"] = "ok";
					$answ["data"] = $data;
		
					echo json_encode($answ);
				}
				else 
				{
					$paramsCount = $M->GetParamCount($flightId);
						
					if((count($paramsCount['apCount']) > PARAMS_PAGING) || (count($paramsCount['bpCount']) > PARAMS_PAGING))
					{
						$pageNum = 0;
						$bruTypeParams = $M->ShowParamListWithPaging($flightId, $pageNum, PARAMS_PAGING);
						
						$totalPages = intval(ceil(count($paramsCount['bpCount'])/PARAMS_PAGING));
						if(count($paramsCount['apCount']) > count($paramsCount['bpCount']))
						{
							$totalPages = intval(ceil(count($paramsCount['apCount'])/PARAMS_PAGING));
						}
							
						$data = array(
								'bruTypeParams' => $bruTypeParams,
								'pagination' => true,
								'pageNum' => $pageNum,
								'totalPages' => $totalPages
						);
					
						$answ["status"] = "ok";
						$answ["data"] = $data;
							
						echo json_encode($answ);
					}
					else
					{
						$bruTypeParams = $M->ShowParamList($flightId);
							
						$data = array(
								'bruTypeParams' => $bruTypeParams,
								'pagination' => false
						);
					
						$answ["status"] = "ok";
						$answ["data"] = $data;
							
						echo json_encode($answ);
					}
				}
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getSearchedParams"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if((isset($M->data['flightId'])) && (isset($M->data['request'])))
			{
				$flightId = $M->data['flightId'];
				$request = $M->data['request'];

				$searchedParams = $M->ShowSearchedParams($flightId, $request);

				$data = array(
						'searchedParams' => $searchedParams
				);
					
				$answ["status"] = "ok";
				$answ["data"] = $data;

				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["getEventsList"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']))
			{
				$flightId = $M->data['flightId'];
				$eventsList = $M->ShowEventsList($flightId);
	
				$data = array(
						'eventsList' => $eventsList
				);
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["setEventReliability"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_EDIT_FLIGHTS, $M->privilege))
		{
			if((isset($M->data['flightId'])) &&
				(isset($M->data['excId'])) && 
				(isset($M->data['state'])))
			{
				$flightId = $M->data['flightId'];
				$excId = $M->data['excId'];
				$state = $M->data['state'];
				$M->SetExcReliability($flightId, $excId, $state);
	
				$answ["status"] = "ok";
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["createTpl"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
				isset($M->data['tplName']) &&
				isset($M->data['params']))
			{
				$flightId = $M->data['flightId'];
				$tplName = $M->data['tplName'];
				$params = $M->data['params'];
				
				$M->CreateTemplate($flightId, $params, $tplName);
				$params = $M->GetTplParamCodes($flightId, $tplName);
	
				$data = array(
						'ap' => $params['ap'],
						'bp' => $params['bp']
				);
				
				$answ["status"] = "ok";
				$answ["data"] = $data;
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->viewOptionsActions["changeParamColor"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['paramCode']) &&
					isset($M->data['color']))
			{
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramCode'];
				$color = $M->data['color'];
	
				$M->UpdateParamColor($flightId, $paramCode, $color);
				$answ["status"] = "ok";
	
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else 
	{
		$msg = "Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".";
		echo($msg);		
		error_log($msg);
	}
}
else 
{
	$msg = "Authorization error. Page: " . $M->currPage;
	echo($msg);
	error_log($msg);
}

?>


