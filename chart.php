<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/ChartController.php");

$c = new ChartController($_POST, $_SESSION, $_GET, $_COOKIE);

if ($c->_user && ($c->_user->username !== null)) {
    if($c->action == $c->chartActions["putChartInNewWindow"]) {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege)) {
            if(isset($c->data) && ($c->data != null) && (is_array($c->data))) {
                $c->PutCharset();
                $c->PutTitle();
                $c->PutStyleSheets();
                $c->PutHeader();
                $c->PrintInfoFromRequest();
                $c->PrintWorkspace();
                $c->PutScripts();
                $c->PutFooter();

            } else {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Request: ".
                    json_encode($_GET) . ". Page chart.php";
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
    echo("Authorization error. Page: " . $c->currPage);
    error_log("Authorization error. Page: " . $c->currPage);
}
