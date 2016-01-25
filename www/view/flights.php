<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/FlightsModel.php");

$M = new FlightsModel($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	if($M->action == $M->flightActions["flightGeneralElements"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->action;					
				$topMenu = $M->PutTopMenu();
				$leftMenu = $M->PutLeftMenu();
				$fileUploadBlock = $M->FileUploadBlock();				
				$M->RegisterActionExecution($action, "executed");
	
				$answ = array(
						'status' => 'ok',
						'data' => array(
							'topMenu' => $topMenu,
							'leftMenu' => $leftMenu,
							'fileUploadBlock' => $fileUploadBlock
						)
				);
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["flightLastView"]) 
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{				
				$lastViewType = $M->GetLastViewType();
				$answ = array();
				
				if($lastViewType == null)
				{
						$targetId = 0;
						$targetName = 'root';
						$viewAction = $M->flightActions["flightListTree"];
						$flightsListTileView = $M->BuildFlightsInTree($targetId);			
						$M->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
						
						$answ["status"] = "ok";
						$answ["type"] = $viewAction;
						$answ["lastViewedFolder"] = $targetId;
						$answ["data"] = $flightsListTileView;
				} 
				else 
				{
					$flightsListByPath = "";
					$viewAction = $lastViewType["action"];
					if($viewAction == $M->flightActions["flightTwoColumnsListByPathes"])
					{
						$targetId1 = $lastViewType['senderId'];
						$targetId2 = $lastViewType['targetId'];
						
						$Fd = new Folder();
						$folderInfo1 = $Fd->GetFolderInfo($targetId1);
						$folderInfo2 = $Fd->GetFolderInfo($targetId2);
						unset($Fd);
						
						if(empty($folderInfo1))
						{
							$targetId1 = 0;
						}
							
						if(empty($folderInfo2))
						{
							$targetId2 = 0;
						}
						
						$flightsListByPath = $M->BuildFlightListInTwoColumns($targetId1, $targetId2);
						$M->RegisterActionExecution($viewAction, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
					
						$answ["status"] = "ok";
						$answ["type"] = $viewAction;
						$answ["data"] = $flightsListByPath;
					} 
					else if($viewAction == $M->flightActions["flightListTree"])
					{
						$actionsInfo = $M->GetLastViewedFolder();
						$targetId = 0;
						if($actionsInfo == null)
						{
							$targetName = 'root';
							$flightsListTileView = $M->BuildFlightsInTree($targetId);
							$M->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
						}
						else
						{
							$targetId = $actionsInfo['targetId'];
							$targetName = $actionsInfo['targetName'];
							
							$Fd = new Folder();
							$folderInfo = $Fd->GetFolderInfo($targetId);
							unset($Fd);
							
							if(empty($folderInfo))
							{
								$targetId = 0;
								$targetName = 'root';
							}
							
							$flightsListTileView = $M->BuildFlightsInTree($targetId);
							$M->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
						}
						
						$answ["status"] = "ok";
						$answ["type"] = $viewAction;
						$answ["lastViewedFolder"] = $targetId;
						$answ["data"] = $flightsListTileView;
						
					}
					else if($viewAction == $M->flightActions["flightListTable"])
					{
						$action = $M->flightActions["flightListTable"];
						
						$table = $M->BuildTable();
						$M->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');
						$actionsInfo = $M->GetLastSortTableType();
						
						if(empty($actionsInfo)){
							$actionsInfo['senderId'] = 3; // colunm 3 - start copy time
							$actionsInfo['targetName'] = 'desc';
						}
						
						$answ["status"] = "ok";
						$answ["type"] = $viewAction;
						$answ["data"] = $table;
						$answ["sortCol"] = $actionsInfo['senderId'];
						$answ["sortType"] = $actionsInfo['targetName'];
					}	
				}					
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["flightTwoColumnsListByPathes"])
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$lastViewType = $M->GetLastViewType();
				$action = $M->flightActions["flightTwoColumnsListByPathes"];
				
				if($lastViewType == null)
				{
					$targetId1 = 0; // root path
					$targetId2 = 0;
					$flightsListByPath = $M->BuildFlightListInTwoColumns($targetId1, $targetId2);
					$M->RegisterActionExecution($action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
				}
				else 
				{
					$targetId1 = $lastViewType['senderId'];
					$targetId2 = $lastViewType['targetId'];
					
					$Fd = new Folder();
					$folderInfo1 = $Fd->GetFolderInfo($targetId1);
					$folderInfo2 = $Fd->GetFolderInfo($targetId2);
					unset($Fd);
						
					if(empty($folderInfo1))
					{
						$targetId1 = 0;
					}
					
					if(empty($folderInfo2))
					{
						$targetId2 = 0;
					}
					
					$flightsListByPath = $M->BuildFlightListInTwoColumns($targetId1, $targetId2);
					$M->RegisterActionExecution($action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
				}
				
				$answ["status"] = "ok";
				$answ["data"] = $flightsListByPath;
				
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["flightListTree"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{	
			if(isset($M->data['data']))
			{
				$flightsListTile = "";
				$action = $M->flightActions["flightListTree"];
	
				$actionsInfo = $M->GetLastViewedFolder();
				$targetId = 0;
				if($actionsInfo == null)
				{
					$targetName = 'root';
					$flightsListTileView = $M->BuildFlightsInTree($targetId);
					$M->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
				}
				else 
				{
					$targetId = $actionsInfo['targetId'];
					$targetName = $actionsInfo['targetName'];
					
					$Fd = new Folder();
					$folderInfo = $Fd->GetFolderInfo($targetId);
					unset($Fd);
						
					if(empty($folderInfo))
					{
						$targetId = 0;
						$targetName = 'root';
					}
					
					$flightsListTileView = $M->BuildFlightsInTree($targetId);
					$M->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
				}
	
				$answ["status"] = "ok";
				$answ["lastViewedFolder"] = $targetId;				
				$answ["data"] = $flightsListTileView;
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["receiveTree"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->flightActions["receiveTree"];
	
				$folderid = 0;
				$folderName = $M->lang->root;
	
				$relatedNodes = "";
				$actionsInfo = $M->GetLastViewedFolder();
	
				if($actionsInfo == null)
				{
					$targetId = $folderid;
					$targetName = 'root';
					$relatedNodes = $M->PrepareTree($targetId);
					$M->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
				}
				else
				{
					$targetId = $actionsInfo['targetId'];
					$targetName = $actionsInfo['targetName'];
						
					$Fd = new Folder();
					$folderInfo = $Fd->GetFolderInfo($targetId);
					unset($Fd);
						
					if(empty($folderInfo))
					{
						$targetId = 0;
						$targetName = 'root';
					}
						
					$relatedNodes = $M->PrepareTree($targetId);
					$M->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
				}
	
				$tree[] = array(
						"id" => (string)$folderid,
						"text" => $folderName,
						'type' => 'folder',
						'state' =>  array(
								"opened" => true
						),
						'children' => $relatedNodes
				);
	
				if(($actionsInfo == null) || ($actionsInfo['targetId'] == 0))
				{
					$tree[0]["state"] =  array(
							"opened" => true,
							"selected" => true
					);
				}
	
				echo json_encode($tree);
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
	}
	else if($M->action == $M->flightActions["flightListTable"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$action = $M->flightActions["flightListTable"];
				
				$table = $M->BuildTable();					
				$M->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');
				
				$actionsInfo = $M->GetLastSortTableType();
				
				if(empty($actionsInfo)){
					$actionsInfo['senderId'] = 3; // colunm 3 - start copy time
					$actionsInfo['targetName'] = 'desc'; 
				}
				
				$answ = array(
					'status' => 'ok',
					'data' => $table,
					'sortCol' => $actionsInfo['senderId'],
					'sortType' => $actionsInfo['targetName']
				);
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["segmentTable"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$aoData = $M->data['data'];
				$sEcho = $aoData[sEcho]['value'];
				$iDisplayStart = $aoData[iDisplayStart]['value'];
				$iDisplayLength = $aoData[iDisplayLength]['value'];
				$action = $M->flightActions["segmentTable"];
				
				$sortValue = count($aoData) - 3;
				$sortColumnName = 'id';
				$sortColumnNum = $aoData[$sortValue]['value'];
				$sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);
				
				switch ($sortColumnNum){
					case(1):
					{
						$sortColumnName = 'bort';
						break;
					}
					case(2):
					{
						$sortColumnName = 'voyage';
						break;
					}
					case(3):
					{
						$sortColumnName = 'startCopyTime';
						break;
					}
					case(4):
					{
						$sortColumnName = 'uploadingCopyTime';
						break;
					}
					case(5):
					{
						$sortColumnName = 'bruType';
						break;
					}
					case(6):
					{
						$sortColumnName = 'arrivalAirport';
						break;
					}
					case(7):
					{
						$sortColumnName = 'departureAirport';
						break;
					}
					case(8):
					{
						$sortColumnName = 'performer';
						break;
					}
					case(9):
					{
						$sortColumnName = 'exTableName';
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
	}
	else if($M->action == $M->flightActions["showFolderContent"])
	{
		$U = new User();
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['folderId']))
			{
				$folderid = $M->data['folderId'];
				$action = $M->flightActions["showFolderContent"];
									
				$result = $M->BuildSelectedFolderContent($folderid);
				
				$folderContent = $result['content'];
				$targetId = $folderid;
				$targetName = $result['folderName'];
				$M->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
				
				$answ = array(
					'status' => 'ok',
					'data' => $folderContent
				);
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["flightShowFolder"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['position']) && 
					isset($M->data['fullpath']))
			{
				$position = $M->data['position'];
				$fullpath = $M->data['fullpath'];
				
				$flightsListByPath = "";
				$action = $M->flightActions["flightTwoColumnsListByPathes"];
				
				$actionsInfo = $M->GetLastFlightTwoColumnsListPathes();
				if($position == 'Left')
				{
					$targetId = $actionsInfo['targetId'];
					$flightsListByPath = $M->BuildFlightColumnFromTwoColumns($fullpath, $position);
					$M->RegisterActionExecution($action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
				}
				else if ($position == 'Right')
				{
					$senderId = $actionsInfo['senderId'];
					$flightsListByPath = $M->BuildFlightColumnFromTwoColumns($fullpath, $position);
					$M->RegisterActionExecution($action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
				}
	
				$answ["status"] = "ok";
				$answ["data"] = $flightsListByPath;
	
				echo json_encode($answ);
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
	} 
	else if($M->action == $M->flightActions["flightGoUpper"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['position']) &&
					isset($M->data['fullpath']))
			{
				$position = $M->data['position'];
				$fullpath = $M->data['fullpath'];
	
				$flightsListByPath = "";
				$action = $M->flightActions["flightTwoColumnsListByPathes"];
	
				$Fd = new Folder();
				$folderInfo = $Fd->GetFolderInfo($fullpath);
				$fullpath = $folderInfo['path'];
				
				$actionsInfo = $M->GetLastFlightTwoColumnsListPathes();
				if($position == 'Left')
				{
					$targetId = $actionsInfo['targetId'];
					$flightsListByPath = $M->BuildFlightColumnFromTwoColumns($fullpath, $position);
					$M->RegisterActionExecution($action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
				}
				else if ($position == 'Right')
				{
					$senderId = $actionsInfo['senderId'];
					$flightsListByPath = $M->BuildFlightColumnFromTwoColumns($fullpath, $position);
					$M->RegisterActionExecution($action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
				}
	
				$answ["status"] = "ok";
				$answ["data"] = $flightsListByPath;
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["folderCreateNew"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['folderName']) &&
					isset($M->data['fullpath']))
			{
				$folderName = $M->data['folderName'];
				$fullpath = $M->data['fullpath'];
	
				$res = $M->CreateNewFolder($folderName, $fullpath);
				$action = $M->action;
				$M->RegisterActionExecution($action, "executed", 0, 'folderCreation', $fullpath, $folderName);
				
				$answ["status"] = "ok";
				$folderId = $res['folderId'];
				
				$answ["data"] = $res;
				$answ["data"]['folderId'] = $folderId;
	
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["flightChangePath"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['sender']) &&
					isset($M->data['target']))
			{
				$sender = $M->data['sender'];
				$target = $M->data['target'];
	
				$action = $M->action;
				$result = $M->ChangeFlightPath($sender, $target);
				$M->RegisterActionExecution($action, "executed", $sender, 'flightId', $target, "newPath");
				
				$answ = array();
				if($result)
				{
					$answ['status'] = 'ok';
				}
				else 
				{
					$answ['status'] = 'err';
					$answ['error'] = 'Error during flight change path.';
					$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				}
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["folderChangePath"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['sender']) &&
					isset($M->data['target']))
			{
				$sender = $M->data['sender'];
				$target = $M->data['target'];
	
				$action = $M->action;
				$result = $M->ChangeFolderPath($sender, $target);
				$M->RegisterActionExecution($action, "executed", $sender, 'folderId', $target, "newPath");
	
				$answ = array();
				if($result)
				{
					$answ['status'] = 'ok';
				}
				else
				{
					$answ['status'] = 'err';
					$answ['error'] = 'Error during folder change path.';
					$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				}
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["folderRename"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['folderId']) &&
					isset($M->data['folderName']))
			{
				$folderId = $M->data['folderId'];
				$folderName = $M->data['folderName'];
	
				$action = $M->action;
				$result = $M->RenameFolder($folderId, $folderName);
				$M->RegisterActionExecution($action, "executed", $folderId, 'folderId', $folderName, "newName");
	
				$answ = array();
				if($result)
				{
					$answ['status'] = 'ok';
				}
				else
				{
					$answ['status'] = 'err';
					$answ['error'] = 'Error during folder rename.';
					$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				}
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["itemDelete"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_DEL_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['type']) &&
					isset($M->data['id']))
			{
				$type = $M->data['type'];
				$id = intval($M->data['id']);
				
				if($type == 'folder')
				{
					$result = $M->DeleteFolderWithAllChildren($id);
					
					$answ = array();
					if($result)
					{
						$answ['status'] = 'ok';
						$action = $M->action;
						$M->RegisterActionExecution($action, "executed", $id, "itemId", $type, 'typeDeletedItem');
					}
					else
					{
						$answ['status'] = 'err';
						$answ['data']['error'] = 'Error during folder deleting.';
						$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
					}
					echo json_encode($answ);
				}
				else if($type == 'flight')
				{
					$result = $M->DeleteFlight($id);
					
					$answ = array();
					if($result)
					{
						$answ['status'] = 'ok';
						$action = $M->action;
						$M->RegisterActionExecution($action, "executed", $id, "itemId", $type, 'typeDeletedItem');
					}
					else
					{
						$answ['status'] = 'err';
						$answ['data']['error'] = 'Error during flight deleting.';
						$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
					}
					echo json_encode($answ);
				}
				else 
				{
					$answ["status"] = "err";
					$answ["error"] = "Incorect type. Post: ".
							json_encode($_POST) . ". Page flights.php";
					echo(json_encode($answ));
				}
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
	}
	else if($M->action == $M->flightActions["itemProcess"]) 
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_DEL_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['id']))
			{
				$id = intval($M->data['id']);
				$result = $M->ProcessFlight($id);
						
				$answ = array();
				if($result)
				{
					$answ['status'] = 'ok';
					$action = $M->action;
					$M->RegisterActionExecution($action, "executed", $id, "itemId");
				}
				else
				{
					$answ['status'] = 'err';
					$answ['data']['error'] = 'Error during flight process.';
					$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				}
				echo json_encode($answ);
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
	}
	else if($M->action == $M->flightActions["syncItemsHeaders"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_EDIT_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['ids']))
			{
				$ids = $M->data['ids'];
				$result = $M->SyncFlightsHeaders($ids);
	
				$answ = array();
				if($result)
				{
					$answ['status'] = 'ok';
					$action = $M->action;
					$M->RegisterActionExecution($action, "executed", implode(",", $ids), "itemsId");
				}
				else
				{
					$answ['status'] = 'err';
					$answ['data']['error'] = 'Error during flights headerSync.';
					$M->RegisterActionReject($M->action, "rejected", 0, $answ["error"]);
				}
				echo json_encode($answ);
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


