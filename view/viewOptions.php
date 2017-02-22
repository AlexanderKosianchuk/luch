<?php

require_once "../bootstrap.php";

use Model\User;
use Model\Folder;
use Controller\ViewOptionsController;

$c = new ViewOptionsController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "putViewOptionsContainer") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $topMenu = $c->PutTopMenu();
                $leftMenu = $c->PutLeftMenu();
                $workspace = $c->PutWorkspace();

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getFlightDuration")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = $c->data['flightId'];
                $flightTiming = $c->GetFlightTiming($flightId);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getParamCodesByTemplate")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) && isset($c->data['tplName']))
            {
                $flightId = $c->data['flightId'];
                $tplName = $c->data['tplName'];

                $params = $c->GetTplParamCodes($flightId, $tplName);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getDefaultTemplateParamCodes")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = $c->data['flightId'];

                $params = $c->GetDefaultTplParams($flightId);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getBruTypeId")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = $c->data['flightId'];
                $bruTypeId = $c->GetBruTypeId($flightId);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getBruTemplates")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = $c->data['flightId'];
                $bruTypeTpls = $c->ShowTempltList($flightId);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getParamListGivenQuantity")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = $c->data['flightId'];

                if(isset($c->data['pageNum']))
                {
                    $pageNum = $c->data['pageNum'];

                    $paramsCount = $c->GetParamCount($flightId);
                    $bruTypeParams = $c->ShowParamListWithPaging($flightId, $pageNum, PARAMS_PAGING);

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
                    $paramsCount = $c->GetParamCount($flightId);

                    if((count($paramsCount['apCount']) > PARAMS_PAGING) || (count($paramsCount['bpCount']) > PARAMS_PAGING))
                    {
                        $pageNum = 0;
                        $bruTypeParams = $c->ShowParamListWithPaging($flightId, $pageNum, PARAMS_PAGING);

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
                        $bruTypeParams = $c->ShowParamList($flightId);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getSearchedParams")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if((isset($c->data['flightId'])) && (isset($c->data['request'])))
            {
                $flightId = $c->data['flightId'];
                $request = $c->data['request'];

                $data = $c->ShowSearchedParams($flightId, $request);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "getEventsList")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']))
            {
                $flightId = intval($c->data['flightId']);
                $eventsListHeader = $c->GetEventsListHeader($flightId);
                $eventsList = $c->ShowEventsList($flightId);

                $data = array(
                        'eventsList' => $eventsList,
                        'eventsListHeader' => $eventsListHeader
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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "setEventReliability")
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if((isset($c->data['flightId'])) &&
                (isset($c->data['excId'])) &&
                (isset($c->data['state'])))
            {
                $flightId = $c->data['flightId'];
                $excId = $c->data['excId'];
                $state = $c->data['state'];
                $c->SetExcReliability($flightId, $excId, $state);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action === "createTpl")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                isset($c->data['tplName']) &&
                isset($c->data['params']))
            {
                $flightId = $c->data['flightId'];
                $tplName = $c->data['tplName'];
                $params = $c->data['params'];

                $c->CreateTemplate($flightId, $params, $tplName);
                $params = $c->GetTplParamCodes($flightId, $tplName);

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else if($c->action == "changeParamColor")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['paramCode']) &&
                    isset($c->data['color']))
            {
                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramCode'];
                $color = $c->data['color'];

                $c->UpdateParamColor($flightId, $paramCode, $color);
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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    } else if($c->action == 'updateComment') {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['excId']) &&
                    isset($c->data['text']))
            {
                $flightId = $c->data['flightId'];
                $excid = $c->data['excId'];
                $text = $c->data['text'];

                $c->UpdateExceptionComment($flightId, $excid, $text);
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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    } else if($c->action == 'saveFlightComment') {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            $params = [];
            parse_str($c->data, $params);

            if(isset($params['flight-id'])) {
                $flightId = intval($params['flight-id']);

                $Fd = new Folder;
                $folder = $Fd->GetFlightFolder($flightId, $c->_user->userInfo['id']);
                unset($Fd);

                $answ = [];
                $answ["status"] = "not allowed";
                if (!empty($folder)) {
                    $c->UpdateFlightComment($flightId, $params);
                    $answ["status"] = "ok";
                }

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
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            echo(json_encode($answ));
        }
    }
    else
    {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        echo($msg);
        error_log($msg);
    }
}
else
{
    $msg = "Authorization error. Page: " . $c->curPage;
    echo($msg);
    error_log($msg);
}
