<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/PrinterController.php");

$c = new PrinterController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "printBlank") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) && isset($c->data['sections']))
            {
                $flightId = $c->data['flightId'];
                $sections = explode(',', $c->data['sections']);

                $c->ConstructFlightEventsList($flightId, $sections, true);

                $c->RegisterActionExecution($c->action, "executed");
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
    else if($c->action === "monochromePrintBlank")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) && isset($c->data['sections']))
            {
                $flightId = $c->data['flightId'];
                $sections = explode(',', $c->data['sections']);

                $c->ConstructFlightEventsList($flightId, $sections, false);

                $c->RegisterActionExecution($c->action, "executed");
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
    } else {
        $msg = "Undefined action. Data: " . json_encode(isset($_POST['data']) ? $_POST['data'] : '') .
                " . Action: " . json_encode(isset($_POST['action']) ? $_POST['action'] : '') .
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
