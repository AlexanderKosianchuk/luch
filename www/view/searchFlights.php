<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/SearchFlightsController.php");

$c = new SearchFlightController($_POST, $_SESSION);

if ($c->_user && ($c->_user->username !== '')) {
    if($c->action == $c->controllerActions["showSearchForm"]) {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $action = $c->action;
                $html = $c->ShowSearchForm();
                $c->RegisterActionExecution($action, "executed");

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
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
                exit();
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else if($c->action == $c->controllerActions["getFilters"]) {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['fdrId']))
            {
                $action = $c->action;
                $fdrId = $c->data['fdrId'];
                $html = $c->BuildSearchFlightAlgorithmesList($fdrId);
                $c->RegisterActionExecution($action, "executed");

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
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                        echo(json_encode($answ));
                        exit();
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else if($c->action == $c->controllerActions["applyFilter"]) {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['algId']) &&
                    isset($c->data['form']))
            {
                $action = $c->action;
                $algId = $c->data['algId'];
                parse_str($c->data['form'], $form);

                $flightIds = $c->GetFlightsByCriteria($form);
                $idsArr = $c->SearchByAlgorithm($algId, $flightIds);
                $html = $c->BuildFlightList($idsArr);
                $c->RegisterActionExecution($action, "executed");

                if(empty($html)) {
                    $html = $c->lang->searchBroughtNoResult;
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
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                        echo(json_encode($answ));
                        exit();
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
        error_log($msg);
        echo($msg);
        exit();
    }
}
else
{
    $msg = "Authorization error. Page: " . $c->currPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
    exit();
}
