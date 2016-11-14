<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/ChartController.php");

$c = new ChartController($_POST, $_SESSION);

if ($c->_user && ($c->_user->username !== '')) {
    if($c->action == $c->chartActions["putChartContainer"]) {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $workspace = $c->PutWorkspace();

                $data = array(
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
                    json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["figurePrint"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['fromTime']) &&
                    isset($c->data['toTime']) &&
                    isset($c->data['prms']))
            {
                $flightId = $c->data['flightId'];
                $fromTime = $c->data['fromTime'] / 1000; //to cast js to php timestamps
                $toTime = $c->data['toTime'] / 1000;
                $prms = $c->data['prms'];

                $step = $c->GetTableStep($flightId);

                $globalRawParamArr = $c->GetTableRawData($flightId, $prms, $fromTime, $toTime);
                $totalRecords = count($globalRawParamArr[1]); // 0 is time and may be lager than data

                $exportFileInfo = $c->GetExportFileName($flightId);
                $exportedFileName = $exportFileInfo["name"];
                $exportedFilePath = $exportFileInfo["path"];

                $exportedFileDesc = fopen($exportedFilePath, "w");

                $figPrRow = "time;";
                for($i = 0; $i < count($prms); $i++)
                {
                    $paramInfo = $c->GetParamInfo($flightId, $prms[$i]);
                    $figPrRow .= iconv('utf-8', 'windows-1251', $paramInfo['name']) . ";";
                }

                $figPrRow = substr($figPrRow, 0, -1);
                $figPrRow .= PHP_EOL;

                $figPrRow .= "T;";
                for($i = 0; $i < count($prms); $i++)
                {
                    $paramInfo = $c->GetParamInfo($flightId, $prms[$i]);
                    $figPrRow .= $prms[$i] . ";";
                }

                $figPrRow = substr($figPrRow, 0, -1);
                $figPrRow .= PHP_EOL;
                fwrite ($exportedFileDesc , $figPrRow);

                $curStep = 0;
                for($i = 0; $i < $totalRecords; $i++)
                {
                    $figPrRow = "";
                      for($j = 0; $j < count($globalRawParamArr); $j++)
                      {
                          $figPrRow .= $globalRawParamArr[$j][$i] . ";";
                      }

                      $figPrRow = substr($figPrRow, 0, -1);
                      $figPrRow .= PHP_EOL;

                      if($curStep == 0) {
                          fwrite ($exportedFileDesc , $figPrRow);
                      }

                      $curStep++;

                      if($curStep >= $step) {
                          $curStep = 0;
                      }
                }

                fclose($exportedFileDesc);

                $href = 'http';
                if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on"))
                {
                    $href .= "s";
                }
                $href .= "://";
                if ($_SERVER["SERVER_PORT"] != "80") {
                    $href .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
                }
                else
                {
                    $href .= $_SERVER["SERVER_NAME"];
                }
                $href .= "/fileUploader/files/exported/" . $exportedFileName;

                $answ["status"] = "ok";
                $answ["data"] = $href;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getApParamValue"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['paramApCode']) &&
                    isset($c->data['totalSeriesCount']) &&
                    isset($c->data['startFrame']) &&
                    isset($c->data['endFrame']))
            {

                $flightId = $c->data['flightId'];
                $paramApCode = $c->data['paramApCode'];
                $totalSeriesCount = $c->data['totalSeriesCount'];
                $startFrame = $c->data['startFrame'];
                $endFrame = $c->data['endFrame'];

                $paramData = $c->GetApParamValue($flightId,
                    $startFrame, $endFrame, $totalSeriesCount,
                    $paramApCode);

                echo json_encode($paramData);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getBpParamValue"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                isset($c->data['paramBpCode']))
            {
                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramBpCode'];

                $paramData = [];
                if(!empty($paramCode)) {
                    $paramData = $c->GetBpParamValue($flightId, $paramCode);
                }

                echo json_encode($paramData);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["rcvLegend"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['paramCodes']))
            {
                $flightId = $c->data['flightId'];
                $paramCodes = $c->data['paramCodes'];

                $legend = $c->GetLegend($flightId, $paramCodes);

                echo json_encode($legend);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getParamMinmax"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                isset($c->data['paramCode']) &&
                isset($c->data['tplName']))
            {
                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramCode'];
                $tplName = $c->data['tplName'];

                $minmax = $c->GetParamMinmax($flightId, $paramCode, $tplName);

                echo json_encode($minmax);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["setParamMinmax"])
    {
        if(in_array($c->_user::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                isset($c->data['paramCode']) &&
                isset($c->data['tplName']) &&
                isset($c->data['min']) &&
                isset($c->data['max']))
            {
                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramCode'];
                $tplName = $c->data['tplName'];
                $min = $c->data['min'];
                $max = $c->data['max'];

                $status = $c->SetParamMinmax($flightId, $paramCode, $tplName, $min, $max);

                $answ["status"] = $status;
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getParamColor"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['paramCode']))
            {

                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramCode'];

                $color = $c->GetParamColor($flightId, $paramCode);

                echo json_encode($color);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getParamInfo"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['paramCode']))
            {

                $flightId = $c->data['flightId'];
                $paramCode = $c->data['paramCode'];

                $info = [];
                if(!empty($paramCode)) {
                    $info = $c->GetParamInfo($flightId, $paramCode);
                }

                echo json_encode($info);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    else if($c->action == $c->chartActions["getFlightExceptions"])
    {
        if(in_array($c->_user::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightId']) &&
                    isset($c->data['refParam']))
            {

                $flightId = $c->data['flightId'];
                $paramCode = $c->data['refParam'];

                $exceptions = $c->GetFlightExceptions($flightId, $paramCode);

                echo json_encode($exceptions);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page chart.php";
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
    echo("Authorization error. Page: " . $c->curPage);
    error_log("Authorization error. Page: " . $c->curPage);
}
