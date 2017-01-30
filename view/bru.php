<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/BruController.php");

$c = new BruController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "putBruTypeContainer") {
        if(in_array(User::$PRIVILEGE_VIEW_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $topMenu = $c->PutTopMenu();
                $leftMenu = $c->PutLeftMenu();
                $workspace = $c->PutWorkspace();
                $c->RegisterActionExecution($c->action, "executed");

                $answ = [
                    'status' => 'ok',
                    'data' => [
                        'topMenu' => $topMenu,
                        'leftMenu' => $leftMenu,
                        'workspace' => $workspace,
                    ]
                ];

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page bru.php";
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
    else if($c->action === "editingBruTypeTemplatesReceiveTplsList")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $tplsList = $c->GetTplsList($bruTypeId);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action === "editingBruTypeTemplatesReceiveParamsList")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $paramsList = $c->ShowParamList($bruTypeId);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action === "createTpl")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']) &&
                        isset($c->data['name']) &&
                        isset($c->data['params']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $name = $c->data['name'];
                $params = $c->data['params'];

                $c->CreateTemplate($bruTypeId, $name, $params);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action === "deleteTpl")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']) &&
                    isset($c->data['name']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $name = $c->data['name'];

                $c->DeleteTemplate($bruTypeId, $name);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action === "defaultTpl")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']) &&
                    isset($c->data['name']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $name = $c->data['name'];

                $c->SetDefaultTemplate($bruTypeId, $name);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action === "updateTpl")
    {
        if(in_array(User::$PRIVILEGE_EDIT_BRUTYPES, $c->_user->privilege))
        {
            if(isset($c->data['bruTypeId']) &&
                    isset($c->data['name']) &&
                    isset($c->data['tplOldName']) &&
                    isset($c->data['params']))
            {
                $bruTypeId = $c->data['bruTypeId'];
                $name = $c->data['name'];
                $tplOldName = $c->data['tplOldName'];
                $params = $c->data['params'];

                $c->DeleteTemplate($bruTypeId, $tplOldName);
                $c->CreateTemplate($bruTypeId, $name, $params);
                $c->RegisterActionExecution($c->action, "executed");

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
    else if($c->action == 'copyTemplate')
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['tplName']))
            {
                $flightId = $c->data['flightId'];
                $tplName = $c->data['tplName'];

                $answ = $c->copyTemplate($flightId, $tplName);

                $c->RegisterActionExecution($c->action, "executed");
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page bru.php";
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
