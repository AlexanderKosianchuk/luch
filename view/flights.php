<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/FlightsController.php");

$c = new FlightsController();

if ($c->_user && ($c->_user->username !== '')) {
    if($c->action == $c->flightActions["flightGeneralElements"]) {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege)) {
            if(isset($c->data['data'])) {
                $action = $c->action;
                $topMenu = $c->PutTopMenu();
                $leftMenu = $c->PutLeftMenu();
                $fileUploadBlock = $c->FileUploadBlock();
                $c->RegisterActionExecution($action, "executed");

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightLastView"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $lastViewType = $c->GetLastViewType();
                $answ = array();

                if($lastViewType == null)
                {
                        $targetId = 0;
                        $targetName = 'root';
                        $viewAction = $c->flightActions["flightListTree"];
                        $flightsListTileView = $c->BuildFlightsInTree($targetId);
                        $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["lastViewedFolder"] = $targetId;
                        $answ["data"] = $flightsListTileView;
                }
                else
                {
                    $flightsListByPath = "";
                    $viewAction = $lastViewType["action"];
                    if($viewAction == $c->flightActions["flightTwoColumnsListByPathes"])
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

                        $flightsListByPath = $c->BuildFlightListInTwoColumns($targetId1, $targetId2);
                        $c->RegisterActionExecution($viewAction, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["data"] = $flightsListByPath;
                    }
                    else if($viewAction == $c->flightActions["flightListTree"])
                    {
                        $actionsInfo = $c->GetLastViewedFolder();
                        $targetId = 0;
                        if($actionsInfo == null)
                        {
                            $targetName = 'root';
                            $flightsListTileView = $c->BuildFlightsInTree($targetId);
                            $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
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

                            $flightsListTileView = $c->BuildFlightsInTree($targetId);
                            $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
                        }

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["lastViewedFolder"] = $targetId;
                        $answ["data"] = $flightsListTileView;

                    }
                    else if($viewAction == $c->flightActions["flightListTable"])
                    {
                        $action = $c->flightActions["flightListTable"];

                        $table = $c->BuildTable();
                        $c->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');
                        $actionsInfo = $c->GetLastSortTableType();

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightTwoColumnsListByPathes"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $lastViewType = $c->GetLastViewType();
                $action = $c->flightActions["flightTwoColumnsListByPathes"];

                if($lastViewType == null)
                {
                    $targetId1 = 0; // root path
                    $targetId2 = 0;
                    $flightsListByPath = $c->BuildFlightListInTwoColumns($targetId1, $targetId2);
                    $c->RegisterActionExecution($action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
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

                    $flightsListByPath = $c->BuildFlightListInTwoColumns($targetId1, $targetId2);
                    $c->RegisterActionExecution($action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightListTree"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $flightsListTile = "";
                $action = $c->flightActions["flightListTree"];

                $actionsInfo = $c->GetLastViewedFolder();
                $targetId = 0;
                if($actionsInfo == null)
                {
                    $targetName = 'root';
                    $flightsListTileView = $c->BuildFlightsInTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
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

                    $flightsListTileView = $c->BuildFlightsInTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["receiveTree"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $action = $c->flightActions["receiveTree"];

                $folderid = 0;
                $folderName = $c->lang->root;

                $relatedNodes = "";
                $actionsInfo = $c->GetLastViewedFolder();

                if($actionsInfo == null)
                {
                    $targetId = $folderid;
                    $targetName = 'root';
                    $relatedNodes = $c->PrepareTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
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

                    $relatedNodes = $c->PrepareTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightListTable"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $action = $c->flightActions["flightListTable"];

                $table = $c->BuildTable();
                $c->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');

                $actionsInfo = $c->GetLastSortTableType();

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["segmentTable"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $aoData = $c->data['data'];
                $sEcho = $aoData[sEcho]['value'];
                $iDisplayStart = $aoData[iDisplayStart]['value'];
                $iDisplayLength = $aoData[iDisplayLength]['value'];
                $action = $c->flightActions["segmentTable"];

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

                $c->RegisterActionExecution($action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);

                $tableSegment = $c->BuildTableSegment($sortColumnName, $sortColumnType);
                $aaData["aaData"] = $tableSegment;

                echo(json_encode($aaData));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["showFolderContent"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderId']))
            {
                $folderid = $c->data['folderId'];
                $action = $c->flightActions["showFolderContent"];

                $result = $c->BuildSelectedFolderContent($folderid);

                $folderContent = $result['content'];
                $targetId = $folderid;
                $targetName = $result['folderName'];
                $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightShowFolder"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['position']) &&
                    isset($c->data['fullpath']))
            {
                $position = $c->data['position'];
                $fullpath = $c->data['fullpath'];

                $flightsListByPath = "";
                $action = $c->flightActions["flightTwoColumnsListByPathes"];

                $actionsInfo = $c->GetLastFlightTwoColumnsListPathes();
                if($position == 'Left')
                {
                    $targetId = $actionsInfo['targetId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
                }
                else if ($position == 'Right')
                {
                    $senderId = $actionsInfo['senderId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightGoUpper"]) //show form for uploading
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['position']) &&
                    isset($c->data['fullpath']))
            {
                $position = $c->data['position'];
                $fullpath = $c->data['fullpath'];

                $flightsListByPath = "";
                $action = $c->flightActions["flightTwoColumnsListByPathes"];

                $Fd = new Folder();
                $folderInfo = $Fd->GetFolderInfo($fullpath);
                $fullpath = $folderInfo['path'];

                $actionsInfo = $c->GetLastFlightTwoColumnsListPathes();
                if($position == 'Left')
                {
                    $targetId = $actionsInfo['targetId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
                }
                else if ($position == 'Right')
                {
                    $senderId = $actionsInfo['senderId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["folderCreateNew"]) //show form for uploading
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderName']) &&
                    isset($c->data['fullpath']))
            {
                $folderName = $c->data['folderName'];
                $fullpath = $c->data['fullpath'];

                $res = $c->CreateNewFolder($folderName, $fullpath);
                $action = $c->action;
                $c->RegisterActionExecution($action, "executed", 0, 'folderCreation', $fullpath, $folderName);

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["flightChangePath"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['sender']) &&
                    isset($c->data['target']))
            {
                $sender = $c->data['sender'];
                $target = $c->data['target'];

                $action = $c->action;
                $result = $c->ChangeFlightPath($sender, $target);
                $c->RegisterActionExecution($action, "executed", $sender, 'flightId', $target, "newPath");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during flight change path.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["folderChangePath"]) //show form for uploading
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['sender']) &&
                    isset($c->data['target']))
            {
                $sender = $c->data['sender'];
                $target = $c->data['target'];

                $action = $c->action;
                $result = $c->ChangeFolderPath($sender, $target);
                $c->RegisterActionExecution($action, "executed", $sender, 'folderId', $target, "newPath");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during folder change path.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["folderRename"]) //show form for uploading
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderId']) &&
                    isset($c->data['folderName']))
            {
                $folderId = $c->data['folderId'];
                $folderName = $c->data['folderName'];

                $action = $c->action;
                $result = $c->RenameFolder($folderId, $folderName);
                $c->RegisterActionExecution($action, "executed", $folderId, 'folderId', $folderName, "newName");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during folder rename.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["itemDelete"]) //show form for uploading
    {
        if(in_array($c->_user::$PRIVILEGE_DEL_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['type']) &&
                    isset($c->data['id']))
            {
                $type = $c->data['type'];
                $id = intval($c->data['id']);

                if($type == 'folder')
                {
                    $result = $c->DeleteFolderWithAllChildren($id);

                    $answ = array();
                    if($result)
                    {
                        $answ['status'] = 'ok';
                        $action = $c->action;
                        $c->RegisterActionExecution($action, "executed", $id, "itemId", $type, 'typeDeletedItem');
                    }
                    else
                    {
                        $answ['status'] = 'err';
                        $answ['data']['error'] = 'Error during folder deleting.';
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                    }
                    echo json_encode($answ);
                }
                else if($type == 'flight')
                {
                    $result = $c->DeleteFlight($id);

                    $answ = array();
                    if($result)
                    {
                        $answ['status'] = 'ok';
                        $action = $c->action;
                        $c->RegisterActionExecution($action, "executed", $id, "itemId", $type, 'typeDeletedItem');
                    }
                    else
                    {
                        $answ['status'] = 'err';
                        $answ['data']['error'] = 'Error during flight deleting.';
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
    }
    else if($c->action == $c->flightActions["itemProcess"])
    {
        if(in_array($c->_user::$PRIVILEGE_DEL_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['id']))
            {
                $id = intval($c->data['id']);
                $result = $c->ProcessFlight($id);

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                    $action = $c->action;
                    $c->RegisterActionExecution($action, "executed", $id, "itemId");
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['data']['error'] = 'Error during flight process.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["itemExport"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightIds']) || isset($c->data['folderDest']))
            {
                $flightIds = [];
                $folderDest = [];
                if(isset($c->data['flightIds']) &&
                        is_array($c->data['flightIds'])) {
                    $flightIds = array_merge($flightIds, $c->data['flightIds']);
                }

                $folderDest = [];
                if(isset($c->data['folderDest']) &&
                    is_array($c->data['folderDest'])) {
                        $folderDest = array_merge($folderDest, $c->data['folderDest']);
                }

                $zipUrl = $c->ExportFlightsAndFolders($flightIds, $folderDest);

                $answ = array();
                if($zipUrl)
                {
                    $answ = [
                        'status' => 'ok',
                        'zipUrl' => $zipUrl
                    ];

                    $action = $c->action;
                    $c->RegisterActionExecution($action, "executed", json_encode(array_merge($flightIds, $flightIds)), "itemId");
                }
                else
                {
                    $answ = [
                        'status' => 'empty',
                        'info' => 'No flights to export'
                    ];
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == $c->flightActions["syncItemsHeaders"])
    {
        if(in_array($c->_user::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['ids']))
            {
                $ids = $c->data['ids'];
                $result = $c->SyncFlightsHeaders($ids);

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                    $action = $c->action;
                    $c->RegisterActionExecution($action, "executed", implode(",", $ids), "itemsId");
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['data']['error'] = 'Error during flights headerSync.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else
    {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
        error_log($msg);
        echo($msg);
    }
}
else
{
    $msg = "Authorization error. Page: " . $c->curPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
}
