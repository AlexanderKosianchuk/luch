<?php

namespace Controller;

use Model\Flight;
use Model\Fdr;
use Model\Language;
use Model\FlightTemplate;
use Model\UserOptions;
use Model\Frame;
use Model\Channel;
use Model\FlightException;

use Component\OSdetectionComponent;

class ChartController extends CController
{
    private $title = 'Title';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        unset($L);
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
                $fdrId = intval($flightInfo['id_fdr']);
                unset($Fl);

                $fdr = new Fdr;
                $fdrInfo = $fdr->getFdrInfo($fdrId);
                $PSTListTableName = $fdrInfo['paramSetTemplateListTableName'];
                $apCycloTable = $fdrInfo['gradiApTableName'];
                $bpCycloTable = $fdrInfo['gradiBpTableName'];
                $Tpl = new FlightTemplate;
                $params = $Tpl->GetPSTParams($PSTListTableName, $tplName, $this->_user->username);
                unset($Tpl);

                $apParams = array();
                $bpParams = array();
                foreach ($params as $item) {
                    $type = $fdr->GetParamType($item, $apCycloTable, $bpCycloTable);
                    if ($type == PARAM_TYPE_AP) {
                        $apParams[] = $item;
                    } else if($type == PARAM_TYPE_BP) {
                        $bpParams[] = $item;
                    }
                }

                unset($fdr);
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'apParams', implode(",", $apParams));
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'bpParams', implode(",", $bpParams));
            }

            printf("<div id='%s' class='InfoFromRequest'>%s</div>", $key, $val);
        }
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

    public function GetApParamValue(
        $flightId,
        $startFrame,
        $endFrame,
        $seriesCount,
        $code,
        $isPrintPage
    ) {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $prefixArr = $fdr->GetBruApCycloPrefixes($fdrId);
        $cycloApTableName = $fdrInfo["gradiApTableName"];
        $cycloBpTableName = $fdrInfo["gradiBpTableName"];

        $Frame = new Frame();
        $framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
        unset($Frame);

        if ($startFrame == null) {
            $startFrame = 0;
        }

        if ($startFrame == null) {
            $endFrame = $framesCount;
        }

        if ($endFrame > $framesCount) {
            $endFrame = $framesCount;
        }

        if ($seriesCount == null) {
            $seriesCount = 1;
        }

        $Ch = new Channel();

        $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName,
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
        $fdrId = intval($flightInfo['id_fdr']);
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo["gradiApTableName"];
        $cycloBpTableName = $fdrInfo["gradiBpTableName"];
        $stepLength = $fdrInfo["stepLength"];

        $Ch = new Channel();
        $paramValuesArr = array();

        $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code, PARAM_TYPE_BP);
        $bpTableName = $bpTableName . "_" . $paramInfo['prefix'];
        $freq = $paramInfo['freq'];
        unset($fdr);

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
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $gradiApTableName = $fdrInfo['gradiApTableName'];
        $gradiBpTableName = $fdrInfo['gradiBpTableName'];

        $paramInfo = $fdr->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
            $color = $fdr->GetParamColor($gradiApTableName, $paramCode);
        } else if ($paramInfo["paramType"] == PARAM_TYPE_BP) {
            $color = $fdr->GetParamColor($gradiBpTableName, $paramCode);
        }

        unset($fdr);

        return $color;
    }

    public function GetParamInfo($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $paramCode = $extParamCode;

        $color = 'ffffff';

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $gradiApTableName = $fdrInfo['gradiApTableName'];
        $gradiBpTableName = $fdrInfo['gradiBpTableName'];

        $paramInfo = $fdr->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
            $color = $fdr->GetParamColor($gradiApTableName, $paramCode);
        } else if ($paramInfo["paramType"] == PARAM_TYPE_BP) {
            $color = $fdr->GetParamColor($gradiBpTableName, $paramCode);
        }

        $paramInfo['color'] = $color;

        unset($fdr);

        return $paramInfo;
    }

    public function GetLegend($flightId, $paramCodeArray)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        for ($i = 0; $i < count($paramCodeArray); $i++) {
            $paramCode = $paramCodeArray[$i];
            if (!empty($paramCode)) {
                $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);

                if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
                    $infoArray[] = $paramInfo['name'].", ".
                    $paramInfo['dim'];
                } else if ($paramInfo["paramType"] == PARAM_TYPE_BP) {
                    $infoArray[] = $paramInfo['name'];
                }
            }
        }
        unset($fdr);

        return $infoArray;
    }

    public function GetParamMinmax($flightId, $paramCode, $tplName)
    {
        $user = $this->_user->username;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $PSTTableName = $fdrInfo['paramSetTemplateListTableName'];
        unset($fdr);

        $flightTemplate = new FlightTemplate;
        $minMax = $flightTemplate->GetParamMinMax($PSTTableName, $tplName,
                $paramCode, $user);
        unset($flightTemplate);

        if ($minMax == '') {
            $minMax = array(
                'min' => -1,
                'max' => 1
            );
        }

        return $minMax;
    }

    public function SetParamMinmax(
        $flightId,
        $paramCode,
        $tplName,
        $min,
        $max
    ) {
        $user = $this->_user->username;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $PSTTableName = $fdrInfo['paramSetTemplateListTableName'];
        unset($fdr);

        $flightTemplate = new FlightTemplate;
        $flightTemplate->UpdateParamMinMax($PSTTableName, $tplName, $paramCode, $min, $max, $user);
        unset($flightTemplate);

        return "ok";
    }

    public function GetFlightExceptions($flightId, $refParam)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $excTableName = $flightInfo['exTableName'];

        if ($excTableName != '') {
            $startCopyTime = $flightInfo['startCopyTime'];
            $apTableName = $flightInfo['apTableName'];

            $fdr = new Fdr;
            $fdrInfo = $fdr->getFdrInfo($fdrId);
            $stepLength = $fdrInfo['stepLength'];
            $cycloApTableName = $fdrInfo['gradiApTableName'];
            $cycloBpTableName = $fdrInfo['gradiBpTableName'];
            $excListTableName = $fdrInfo['excListTableName'];
            $paramType = $fdr->GetParamType($refParam,
                    $cycloApTableName,$cycloBpTableName);
            $excList = array();
            if ($paramType == PARAM_TYPE_AP) {
                $paramInfo = $fdr->GetParamInfoByCode(
                    $cycloApTableName,
                    $cycloBpTableName,
                    $refParam,
                    PARAM_TYPE_AP
                );

                $prefix = $paramInfo["prefix"];
                $apTableName = $apTableName . "_" . $prefix;

                $FEx = new FlightException;
                $excList = (array)$FEx->GetExcApByCode($excTableName,
                        $refParam, $apTableName, $excListTableName);
                unset($FEx);
            } else if($paramType == PARAM_TYPE_BP) {
                $FEx = new FlightException;
                $excList = (array)$FEx->GetExcBpByCode($excTableName, $refParam,
                        $stepLength, $startCopyTime, $excListTableName);
                unset($FEx);
            }
            unset($fdr);
            return $excList;
        } else {
            return 'null';
        }

    }

    public function GetTableRawData(
        $flightId,
        $analogParams,
        $binaryParams,
        $startFrame,
        $endFrame
    ) {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $stepLength = $fdrInfo['stepLength'];
        $stepDivider = $fdrInfo['stepDivider'];
        $startCopyTime = $flightInfo['startCopyTime'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        if ($startFrame < 0) {
            $fromTime = 0;
        }

        $framesCount = $endFrame - $startFrame;

        $Ch = new Channel();
        $normParam = $Ch->NormalizeTime($stepDivider, $stepLength,
            $framesCount, $startCopyTime, $startFrame, $endFrame);
        $globalRawParamArr = array();
        array_push($globalRawParamArr, $normParam);

        for ($i = 0; $i < count($analogParams); $i++) {
            $paramInfo = $fdr->GetParamInfoByCode(
                $cycloApTableName, '',
                $analogParams[$i],
                PARAM_TYPE_AP
            );

            $normParam = $Ch->GetNormalizedApParam(
                $apTableName,
                $stepDivider,
                $paramInfo["code"],
                $paramInfo["freq"],
                $paramInfo["prefix"],
                $startFrame,
                $endFrame
            );

            array_push($globalRawParamArr, $normParam);
        }

        for ($i = 0; $i < count($binaryParams); $i++) {
            $paramInfo = $fdr->GetParamInfoByCode('', $cycloBpTableName,
                    $binaryParams[$i], PARAM_TYPE_BP);
            $normParam = $Ch->GetNormalizedBpParam($bpTableName,
                    $stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
                    $startFrame, $endFrame);
            array_push($globalRawParamArr, $normParam);
        }

        unset($Ch);
        unset($fdr);

        return $globalRawParamArr;
    }

    public function GetTableStep($flightId)
    {
        $F = new Flight;
        $flightInfo = $F->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($F);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        unset($fdr);

        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions;
        $step = $O->GetOptionValue($userId, 'printTableStep');
        unset($O);

        if ($step === null) {
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
        if (!isset($data['flightId'])
            || !isset($data['startFrame'])
            || !isset($data['endFrame'])
            || !isset($data['analogParams'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page ChartController.php";
            echo(json_encode($answ));
        }

        $flightId = $data['flightId'];
        $startFrame = $data['startFrame'];
        $endFrame = $data['endFrame'];
        $analogParams = $data['analogParams'];
        $binaryParams = isset($data['binaryParams']) ? $data['binaryParams'] : [];

        $step = $this->GetTableStep($flightId);

        $globalRawParamArr = $this->GetTableRawData(
            $flightId,
            $analogParams,
            $binaryParams,
            $startFrame,
            $endFrame
        );
        $totalRecords = count($globalRawParamArr[1]); // 0 is time and may be lager than data

        $prms = array_merge($analogParams, $binaryParams);

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

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $fileGuid = uniqid();

        $fileName = $flightInfo['bort'] . "_" .
            date("Y-m-d", $flightInfo['startCopyTime'])  . "_" .
            $flightInfo['voyage'] . "_" . $fileGuid  . "_" . $this->_user->username . ".csv";

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Pragma: no-cache');

        $figPrRow = substr($figPrRow, 0, -1);
        $figPrRow .= PHP_EOL;
        echo $figPrRow;

        $curStep = 0;
        for($i = 0; $i < $totalRecords; $i++) {
            $figPrRow = "";
              for ($j = 0; $j < count($globalRawParamArr); $j++) {
                  $figPrRow .= $globalRawParamArr[$j][$i] . ";";
              }

              $figPrRow = substr($figPrRow, 0, -1);
              $figPrRow .= PHP_EOL;

              if ($curStep == 0) {
                  echo $figPrRow;
              }

              $curStep++;

              if ($curStep >= $step) {
                  $curStep = 0;
              }
        }

        exit;
    }
}
