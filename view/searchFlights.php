<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/SearchFlightsController.php");

$c = new SearchFlightController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "showSearchForm") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $html = $c->ShowSearchForm();
                $c->RegisterActionExecution($c->action, "executed");

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
    } else if($c->action === "getFilters") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['fdrId']))
            {
                $fdrId = $c->data['fdrId'];
                $html = $c->BuildSearchFlightAlgorithmesList($fdrId);
                $c->RegisterActionExecution($c->action, "executed");

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
    } else if($c->action === "applyFilter") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['algId']) &&
                    isset($c->data['form']))
            {
                $algId = $c->data['algId'];
                parse_str($c->data['form'], $form);

                $flightIds = $c->GetFlightsByCriteria($form);
                $idsArr = $c->SearchByAlgorithm($algId, $flightIds);
                $html = $c->BuildFlightList($idsArr);
                $c->RegisterActionExecution($c->action, "executed");

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
    $msg = "Authorization error. Page: " . $c->curPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
    exit();
}
