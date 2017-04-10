<?php

namespace Controller;

use Model\Flight;
use Model\Fdr;
use Model\Language;
use Model\PSTempl;
use Model\UserOptions;
use Model\Frame;
use Model\Channel;
use Model\FlightException;

use Component\OSdetectionComponent;

class ChartController extends CController
{
    public $curPage = 'chartPage';
    private $title = 'Title';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        unset($L);
    }

    public function PutCharset()
    {
        printf("<!DOCTYPE html>
            <html lang='%s'>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>",
                $this->userLang);
    }

    public function PutTitle()
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($this->data['flightId']);
        unset($Fl);

        $bort = $flightInfo['bort'];
        $voyage = $flightInfo['voyage'];
        $copyDate = date('H:i:s d-m-Y', $flightInfo['startCopyTime']);
        $departureAirport = $flightInfo['departureAirport'];
        $arrivalAirport = $flightInfo['arrivalAirport'];

        printf("<title>%s: %s. %s: %s. %s: %s. %s - %s</title>",
        $this->lang->bort, $bort,
        $this->lang->flightDate, $copyDate,
        $this->lang->voyage, $voyage,
        $departureAirport, $arrivalAirport);
    }

    public function PutStyleSheets()
    {
        printf("<link href='/front/stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />");
    }

    public function PutHeader()
    {
        printf("</head><body data-isprintpage='true'>");
    }

    public function EventHandler()
    {
        printf("<div id='eventHandler'></div>");
    }

    public function PutScripts()
    {
        $files = scandir ('public/');
        $scriptName = '';
        foreach ($files as $item) {
            $fileParts = pathinfo($item);
            if ((strpos($item, 'chart') !== false)
                && ($fileParts['extension'] === 'js')
            ) {
                $scriptName = $item;
            }
        }
        printf("<script type='text/javascript' src='public/".$scriptName."'></script>");
    }

    public function PutFooter()
    {
        printf("</body></html>");
    }

    public function PrintInfoFromRequest()
    {

        foreach ($this->data as $key => $val)
        {
            if(($key == 'tplName') && isset($this->data['flightId']))
            {
                $tplName = $val;
                $flightId = $this->data['flightId'];

                $Fl = new Flight();
                $flightInfo = $Fl->GetFlightInfo($flightId);
                unset($Fl);
                $bruType = $flightInfo['bruType'];
                $Bru = new Fdr;
                $fdrInfo = $Bru->GetBruInfo($bruType);
                $PSTListTableName = $fdrInfo['paramSetTemplateListTableName'];
                $apCycloTable = $fdrInfo['gradiApTableName'];
                $bpCycloTable = $fdrInfo['gradiBpTableName'];
                $Tpl = new PSTempl();
                $params = $Tpl->GetPSTParams($PSTListTableName, $tplName, $this->_user->username);
                unset($Tpl);

                $apParams = array();
                $bpParams = array();
                foreach ($params as $item)
                {
                    $type = $Bru->GetParamType($item, $apCycloTable, $bpCycloTable);
                    if($type == PARAM_TYPE_AP)
                    {
                        $apParams[] = $item;
                    }
                    else if($type == PARAM_TYPE_BP)
                    {
                        $bpParams[] = $item;
                    }
                }

                unset($Bru);
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'apParams', implode(",", $apParams));
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'bpParams', implode(",", $bpParams));
            }

            printf("<div id='%s' class='InfoFromRequest'>%s</div>", $key, $val);
        }
    }

    public function PrintWorkspace()
    {
        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions();
        $mainChartColor = $O->GetOptionValue($userId, 'mainChartColor');
        $lineWidth = $O->GetOptionValue($userId, 'lineWidth');
        unset($O);

        printf("<div id='chartWorkspace' class='WorkSpace'>".
                "<div id='graphContainer' class='GraphContainer'>" .
                "<div id='placeholder' data-bgcolor='".$mainChartColor."' data-linewidth='".$lineWidth."'></div>" .
                "<div id='legend'></div>" .
                "</div>" .
                "<div id='loadingBox' class='LoadingBox'>" .
                "<img src='/front/stylesheets/basicImg/loading.gif'/>" .
                "</div>".
                "</div>");
    }

    public function PutWorkspace()
    {
        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions();
        $mainChartColor = $O->GetOptionValue($userId, 'mainChartColor');
        $lineWidth = $O->GetOptionValue($userId, 'lineWidth');
        unset($O);

        $workspace = "<div id='chartWorkspace' class='WorkSpace'>".
                        "<div id='graphContainer' class='GraphContainer'>" .
                        "<div id='placeholder' data-bgcolor='".$mainChartColor."' data-linewidth='".$lineWidth."'></div>" .
                        "<div id='legend'></div>" .
                            "</div>" .
                        "<div id='loadingBox' class='LoadingBox'>" .
                            "<img src='/front/stylesheets/basicImg/loading.gif'/>" .
                        "</div>".
                    "</div>";
        return $workspace;
    }

    public function GetApParamValue($flightId,
        $startFrame, $endFrame, $seriesCount,
        $code, $isPrintPage)
    {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $Bru = new Fdr;
        $bruType = $flightInfo['bruType'];
        $fdrId = $flightInfo['id_fdr'];
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $prefixArr = $Bru->GetBruApCycloPrefixes($fdrId);
        $cycloApTableName = $fdrInfo["gradiApTableName"];
        $cycloBpTableName = $fdrInfo["gradiBpTableName"];

        $Frame = new Frame();
        $framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
        unset($Frame);

        if($startFrame == null) {
            $startFrame = 0;
        }

        if($startFrame == null) {
            $endFrame = $framesCount;
        }

        if($endFrame > $framesCount) {
            $endFrame = $framesCount;
        }

        if($seriesCount == null) {
            $seriesCount = 1;
        }

        $Ch = new Channel();

        $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName,
            $code, PARAM_TYPE_AP);

        $prefix = $paramInfo["prefix"];
        $freq = $paramInfo["freq"];

        $compression = Channel::$compressionTypes['none'];
        if (!$isPrintPage && (($startFrame !== 0) || ($endFrame !== $framesCount))) {
            $compression = Channel::$compressionTypes['aroundRange'];
        } else if (!$isPrintPage && ($framesCount * $seriesCount > POINT_MAX_COUNT)) {
            $compression = Channel::$compressionTypes['general'];
        }

        $syncParam = $Ch->GetChannel($apTableName,
            $code,
            $prefix,
            $startFrame,
            $endFrame,
            $seriesCount,
            $framesCount,
            $compression
        );

        return $syncParam;
    }

    public static function getBoolean($value)
    {
       if ($value === 'true') {
          return true;
       } else {
          return false;
       }
    }

    public function GetBpParamValue($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $code = $extParamCode;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        unset($Fl);

        $Bru = new Fdr;
        $bruType = $flightInfo['bruType'];
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $cycloApTableName = $fdrInfo["gradiApTableName"];
        $cycloBpTableName = $fdrInfo["gradiBpTableName"];
        $stepLength = $fdrInfo["stepLength"];

        $Ch = new Channel();
        $paramValuesArr = array();

        $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code, PARAM_TYPE_BP);
        $bpTableName = $bpTableName . "_" . $paramInfo['prefix'];
        $freq = $paramInfo['freq'];

        $syncParam = $Ch->GetBinaryParam($bpTableName, $code, $stepLength, $freq);

        return $syncParam;
    }

    public function GetParamColor($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $paramCode = $extParamCode;

        $color = 'ffffff';

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $gradiApTableName = $fdrInfo['gradiApTableName'];
        $gradiBpTableName = $fdrInfo['gradiBpTableName'];

        $paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if($paramInfo["paramType"] == PARAM_TYPE_AP)
        {
            $color = $Bru->GetParamColor($gradiApTableName, $paramCode);
        }
        else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
        {
            $color = $Bru->GetParamColor($gradiBpTableName, $paramCode);
        }

        unset($Bru);

        return $color;
    }

    public function GetParamInfo($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $paramCode = $extParamCode;

        $color = 'ffffff';

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $gradiApTableName = $fdrInfo['gradiApTableName'];
        $gradiBpTableName = $fdrInfo['gradiBpTableName'];

        $paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if($paramInfo["paramType"] == PARAM_TYPE_AP)
        {
            $color = $Bru->GetParamColor($gradiApTableName, $paramCode);
        }
        else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
        {
            $color = $Bru->GetParamColor($gradiBpTableName, $paramCode);
        }

        $paramInfo['color'] = $color;

        unset($Bru);

        return $paramInfo;
    }

    public function GetLegend($extFlightId, $extCodes)
    {
        $flightId = $extFlightId;
        $paramCodeArray = $extCodes;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        for($i = 0; $i < count($paramCodeArray); $i++)
        {
            $paramCode = $paramCodeArray[$i];
            if(!empty($paramCode)) {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);

                if($paramInfo["paramType"] == PARAM_TYPE_AP)
                {
                    $infoArray[] = $paramInfo['name'].", ".
                    $paramInfo['dim'];
                }
                else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
                {
                    $infoArray[] = $paramInfo['name'];
                }
            }
        }
        unset($Bru);

        return $infoArray;
    }

    public function GetParamMinmax($exFlightId, $extParamCode, $extTplName)
    {
        $flightId = $exFlightId;
        $paramCode = $extParamCode;
        $tplName = $extTplName;
        $user = $this->_user->username;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        unset($Fl);

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($flightInfo['bruType']);
        $PSTTableName = $fdrInfo['paramSetTemplateListTableName'];
        unset($Bru);

        $PSTempl = new PSTempl;
        $minMax = $PSTempl->GetParamMinMax($PSTTableName, $tplName,
                $paramCode, $user);
        unset($PSTempl);

        if($minMax == '')
        {
            $minMax = array(
                    'min' => -1,
                    'max' => 1);
        }

        return $minMax;
    }

    public function SetParamMinmax($exFlightId, $extParamCode, $extTplName, $extMin, $extMax)
    {
        $flightId = $exFlightId;
        $paramCode = $extParamCode;
        $tplName = $extTplName;
        $min = $extMin;
        $max = $extMax;
        $user = $this->_user->username;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        unset($Fl);

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($flightInfo['bruType']);
        $PSTTableName = $fdrInfo['paramSetTemplateListTableName'];
        unset($Bru);

        $PSTempl = new PSTempl;
        $PSTempl->UpdateParamMinMax($PSTTableName, $tplName, $paramCode, $min, $max, $user);
        unset($PSTempl);

        return "ok";
    }

    public function GetFlightExceptions($extFlightId, $extRefParam)
    {
        $flightId = $extFlightId;
        $refParam = $extRefParam;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $excTableName = $flightInfo['exTableName'];

        if($excTableName != '')
        {
            $bruType = $flightInfo['bruType'];
            $startCopyTime = $flightInfo['startCopyTime'];
            $apTableName = $flightInfo['apTableName'];

            $Bru = new Fdr;
            $fdrInfo = $Bru->GetBruInfo($bruType);
            $stepLength = $fdrInfo['stepLength'];
            $cycloApTableName = $fdrInfo['gradiApTableName'];
            $cycloBpTableName = $fdrInfo['gradiBpTableName'];
            $excListTableName = $fdrInfo['excListTableName'];
            $paramType = $Bru->GetParamType($refParam,
                    $cycloApTableName,$cycloBpTableName);
            $excList = array();
            if($paramType == PARAM_TYPE_AP)
            {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName,
                        $cycloBpTableName, $refParam, PARAM_TYPE_AP);

                $prefix = $paramInfo["prefix"];
                $apTableName = $apTableName . "_" . $prefix;

                $FEx = new FlightException;
                $excList = (array)$FEx->GetExcApByCode($excTableName,
                        $refParam, $apTableName, $excListTableName);
                unset($FEx);
            }
            else if($paramType == PARAM_TYPE_BP)
            {
                $FEx = new FlightException;
                $excList = (array)$FEx->GetExcBpByCode($excTableName, $refParam,
                        $stepLength, $startCopyTime, $excListTableName);
                unset($FEx);
            }
            unset($Bru);
            return $excList;
        }
        else
        {
            return 'null';
        }

    }

    public function GetTableRawData($extFlightId, $extParams, $extFromTime, $extToTime)
    {
        $flightId = $extFlightId;
        $paramCodeArr = $extParams;
        $fromTime = $extFromTime;
        $toTime = $extToTime;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfo($bruType);
        $stepLength = $fdrInfo['stepLength'];
        $stepDivider = $fdrInfo['stepDivider'];
        $startCopyTime = $flightInfo['startCopyTime'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        if($fromTime < $startCopyTime)
        {
            $fromTime = $startCopyTime;
        }

        $startFrame = floor(($fromTime - $startCopyTime) / $stepLength);
        $endFrame = ceil(($toTime - $startCopyTime) / $stepLength);
        $framesCount = $endFrame - $startFrame;

        $Ch = new Channel();
        $normParam = $Ch->NormalizeTime($stepDivider, $stepLength,
            $framesCount, $startCopyTime, $startFrame, $endFrame);
        $globalRawParamArr = array();
        array_push($globalRawParamArr, $normParam);

        for($i = 0; $i < count($paramCodeArr); $i++)
        {
            $paramType = $Bru->GetParamType($paramCodeArr[$i],
                $cycloApTableName, $cycloBpTableName);

            if($paramType == PARAM_TYPE_AP)
            {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, '',
                        $paramCodeArr[$i], PARAM_TYPE_AP);

                $normParam = $Ch->GetNormalizedApParam($apTableName,
                    $stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
                    $startFrame, $endFrame);

                array_push($globalRawParamArr, $normParam);
            }
            else if($paramType == PARAM_TYPE_BP)
            {
                $paramInfo = $Bru->GetParamInfoByCode('', $cycloBpTableName,
                        $paramCodeArr[$i], PARAM_TYPE_BP);
                $normParam = $Ch->GetNormalizedBpParam($bpTableName,
                        $stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
                        $startFrame, $endFrame);
                array_push($globalRawParamArr, $normParam);

            }
        }

        unset($Ch);
        unset($Bru);

        return $globalRawParamArr;
    }

    public function GetExportFileName($extFlightId)
    {
        $flightId = $extFlightId;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $fileGuid = uniqid();

        $exportedFileDir = $_SERVER['DOCUMENT_ROOT'] . "/fileUploader/files/exported/";

        if (!file_exists($exportedFileDir)) {
            mkdir($exportedFileDir, 0755);
        }

        $exportedFileName = $flightInfo['bort'] . "_" .
                date("Y-m-d", $flightInfo['startCopyTime'])  . "_" .
                $flightInfo['voyage'] . "_" . $fileGuid  . "_" . $this->_user->username . ".csv";

        return array(
            'name' => $exportedFileName,
            'path' => $exportedFileDir . $exportedFileName
        );
    }

    public function GetTableStep($flightId)
    {
        $F = new Flight;
        $flightInfo = $F->GetFlightInfo($flightId);
        unset($F);

        $fdr = new Fdr;
        $fdrInfo = $fdr->GetBruInfo($flightInfo['bruType']);
        unset($fdr);

        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions;
        $step = $O->GetOptionValue($userId, 'printTableStep');
        unset($O);

        if($step === null) {
            $step = 0;
        } else {
            $step = $step * $fdrInfo['stepDivider'];
        }

        return $step;
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */

    public function putChartContainer($data)
    {
        $workspace = $this->PutWorkspace();

        $data = array(
            'workspace' => $workspace
        );

        $answ["status"] = "ok";
        $answ["data"] = $data;

        echo json_encode($answ);
    }

    public function getApParamValueAction($data)
    {
        if(isset($data['flightId']) &&
                isset($data['paramApCode']) &&
                isset($data['totalSeriesCount']) &&
                isset($data['startFrame']) &&
                isset($data['endFrame']))
        {
            $isPrintPage = ChartController::getBoolean($data['isPrintPage']);

            $flightId = $data['flightId'];
            $paramApCode = $data['paramApCode'];
            $totalSeriesCount = intval($data['totalSeriesCount']);
            $startFrame = intval($data['startFrame']);
            $endFrame = intval($data['endFrame']);

            $paramData = $this->GetApParamValue($flightId,
                $startFrame, $endFrame, $totalSeriesCount,
                $paramApCode, $isPrintPage);

            echo json_encode($paramData);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function getBpParamValueAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['paramBpCode']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['paramBpCode'];

            $paramData = [];
            if(!empty($paramCode)) {
                $paramData = $this->GetBpParamValue($flightId, $paramCode);
            }

            echo json_encode($paramData);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function rcvLegend($data)
    {
        if(isset($data['flightId']) &&
                isset($data['paramCodes']))
        {
            $flightId = $data['flightId'];
            $paramCodes = $data['paramCodes'];

            $legend = $this->GetLegend($flightId, $paramCodes);

            echo json_encode($legend);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function getParamMinmaxAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['paramCode']) &&
            isset($data['tplName']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['paramCode'];
            $tplName = $data['tplName'];

            $minmax = $this->GetParamMinmax($flightId, $paramCode, $tplName);

            echo json_encode($minmax);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function setParamMinmaxAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['paramCode']) &&
            isset($data['tplName']) &&
            isset($data['min']) &&
            isset($data['max']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['paramCode'];
            $tplName = $data['tplName'];
            $min = $data['min'];
            $max = $data['max'];

            $status = $this->SetParamMinmax($flightId, $paramCode, $tplName, $min, $max);

            $answ["status"] = $status;
            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function getParamColorAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['paramCode']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['paramCode'];

            $color = $this->GetParamColor($flightId, $paramCode);

            echo json_encode($color);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function getParamInfoAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['paramCode']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['paramCode'];

            $info = [];
            if(!empty($paramCode)) {
                $info = $this->GetParamInfo($flightId, $paramCode);
            }

            echo json_encode($info);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function getFlightExceptionsAction($data)
    {
        if(isset($data['flightId']) &&
            isset($data['refParam']))
        {
            $flightId = $data['flightId'];
            $paramCode = $data['refParam'];

            $exceptions = $this->GetFlightExceptions($flightId, $paramCode);

            echo json_encode($exceptions);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }

    public function figurePrint($data)
    {
        if(isset($data['flightId']) &&
            isset($data['fromTime']) &&
            isset($data['toTime']) &&
            isset($data['prms']))
        {
            $flightId = $data['flightId'];
            $fromTime = $data['fromTime'] / 1000; //to cast js to php timestamps
            $toTime = $data['toTime'] / 1000;
            $prms = $data['prms'];

            $step = $this->GetTableStep($flightId);

            $globalRawParamArr = $this->GetTableRawData($flightId, $prms, $fromTime, $toTime);
            $totalRecords = count($globalRawParamArr[1]); // 0 is time and may be lager than data

            $exportFileInfo = $this->GetExportFileName($flightId);
            $exportedFileName = $exportFileInfo["name"];
            $exportedFilePath = $exportFileInfo["path"];

            $exportedFileDesc = fopen($exportedFilePath, "w");

            $figPrRow = "time;";
            for($i = 0; $i < count($prms); $i++) {
                $paramInfo = $this->GetParamInfo($flightId, $prms[$i]);

                $paramName = str_replace(["\n","\r\n","\r", ";", PHP_EOL], '', $paramInfo['name']);

                if (($this->_user->userInfo['lang'] === 'ru')
                    && OSdetectionComponent::isWindows()
                ) {
                    $figPrRow .= iconv('utf-8', 'windows-1251', $paramName) . ";";
                } else {
                    $figPrRow .= $paramName . ";";
                }
            }

            $figPrRow = substr($figPrRow, 0, -1);
            $figPrRow .= PHP_EOL;

            $figPrRow .= "T;";
            for($i = 0; $i < count($prms); $i++)
            {
                $paramInfo = $this->GetParamInfo($flightId, $prms[$i]);
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
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }
    }
}
