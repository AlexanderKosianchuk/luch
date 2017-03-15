<?php

require_once "bootstrap.php";

use Controller\IndexController;

use Model\User;
use Model\Flight;
use Model\Fdr;
use Model\DataBaseConnector;

use Entity\FlightEvent;
use Entity\FlightSettlement;

$c = new IndexController($_POST, $_SESSION, $_COOKIE);

// $Bru = new Fdr;
// $bruType = 'Tester-U3_Su-24';
// $fdrInfo = $Bru->GetBruInfo($bruType);
// $prefixApArr = $Bru->GetBruApCycloPrefixes($bruType);
// $prefixBpArr = $Bru->GetBruBpCycloPrefixes($bruType);
//
// $prefixNums = array_merge($prefixApArr, $prefixBpArr);
// $prefixes = [];
// foreach ($prefixNums as $num) {
//     $prefixes[] = '_'.$num;
// }
// $prefixes[] = FlightSettlement::getPrefix();
// $prefixes[] = FlightEvent::getPrefix();
//
// $F = new Flight;
// $flightInfo = $F->GetFlightInfo($flightId);
// $file = $flightInfo['fileName'];
// $guid = $flightInfo['guid'];
//
// $c = new DataBaseConnector;
// $link = $c->Connect();
//
// foreach($prefixes as $item => $prefix)
// {
//     $tableName =  $guid . $prefix;
//     $query = "SHOW TABLES LIKE '". $tableName ."';";
//     $res = $link->query($query);
//     if (count($res->fetch_array()))
//     {
//         $query = "DROP TABLE `". $tableName."`;";
//         $result['query'][] = $query;
//         $stmt = $link->prepare($query);
//         $result['status'][] = $stmt->execute();
//         $stmt->close();
//     }
// }
// exit;
if ($c->_user && ($c->_user->username !== null)) {
    $c->PutCharset();
    $c->PutTitle();
    $c->PutStyleSheets();

    $c->PutHeader();
    $c->EventHandler();

    $c->PutMessageBox();
    $c->PutHelpDialog();
    $c->PutOptionsDialog();
    $c->PutExportLink();

    $c->PutScripts();
    $c->PutFooter();
} else {
    $c->PutCharset();
    $c->PutTitle();
    $c->PutStyleSheets();

    $c->PutHeader();

    $c->ShowLoginForm();

    $c->PutFooter();
}
