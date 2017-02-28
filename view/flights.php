<?php

require_once "../bootstrap.php";

use Model\User;

use Controller\FlightsController;

$c = new FlightsController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === 'coordinates') {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if (!isset($c->data['id'])) {
                echo 'error';
            }

            header("Content-Type: text/comma-separated-values; charset=utf-8");
            header("Content-Disposition: attachment; filename=coordinates.kml");  //File name extension was wrong
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);

            $id = $c->data['id'];
            $list = $c->GetCoordinates($id);

            $figPrRow = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
             .'<kml xmlns="http://www.opengis.net/kml/2.2"' . PHP_EOL
             .' xmlns:gx="http://www.google.com/kml/ext/2.2"> <!-- required when using gx-prefixed elements -->' . PHP_EOL
            .'<Placemark>' . PHP_EOL
              .'<name>gx:altitudeMode Example</name>' . PHP_EOL
              .'<LineString>' . PHP_EOL
                .'<extrude>1</extrude>' . PHP_EOL
                .'<gx:altitudeMode>absolute </gx:altitudeMode>' . PHP_EOL
                .'<coordinates>' . PHP_EOL;

            foreach ($list as $fields) {
                for($i = 0; $i < count($fields); $i++) {
                    $figPrRow .= $fields[$i] . ",";
                }

                $figPrRow = substr($figPrRow, 0, -1);
                $figPrRow .= PHP_EOL;
            }

            $figPrRow .= '</coordinates>' . PHP_EOL
                .'</LineString>' . PHP_EOL
                .'</Placemark>' . PHP_EOL
                .'</kml>';

            echo $figPrRow;
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }

        unset($U);
    } else {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
        error_log($msg);
        echo($msg);
    }
} else {
    $msg = "Authorization error. Page: " . $c->curPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
}
