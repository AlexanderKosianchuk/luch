<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Component\OSdetectionComponent;

class ChartController extends BaseController
{
    public function putChartContainerAction()
    {
        $mainChartColor = $this->dic()->get('userSettings')->getSettingValue('mainChartColor');
        $lineWidth = $this->dic()->get('userSettings')->getSettingValue('lineWidth');

        $workspace = "<div id='chartWorkspace' class='WorkSpace'>".
            "<div id='graphContainer' class='GraphContainer'>" .
                "<div id='placeholder' data-bgcolor='".$mainChartColor."' data-linewidth='".$lineWidth."'></div>" .
                    "<div id='legend'></div>" .
                "</div>" .
             "<div id='loadingBox' class='LoadingBox'>" .
                "<img src='/front/style/images/loading.gif'/>" .
             "</div>".
        "</div>";

        return json_encode([
            'status' => 'ok',
            'data' => [
                'workspace' => $workspace
            ]
        ]);
    }

    public function getApParamDataAction(
        $flightId,
        $paramApCode,
        $startFrame,
        $endFrame,
        $totalSeriesCount,
        $isPrintPage
    ) {
        $flight = $this->em()->find('Entity\Flight', $flightId);

        if (!$flight) {
            throw new NotFoundException("flightId: ".$flightId);
        }

        $startCopyTime = $flight->getStartCopyTime();
        $timing = $this->dic()->get('flight')->getFlightTiming($flight->getId());
        $framesCount = $timing['framesCount'];

        if ($startFrame == null) {
            $startFrame = 0;
        }

        if ($startFrame == null) {
            $endFrame = $framesCount;
        }

        if ($endFrame > $framesCount) {
            $endFrame = $framesCount;
        }

        if ($totalSeriesCount == null) {
            $totalSeriesCount = 1;
        }

        $param = $this->dic()->get('fdr')->getParamByCode(
            $flight->getFdr()->getId(),
            $paramApCode
        );

        $pointsMaxCount = $this->dic()->get('userSettings')->getSettingValue('pointsMaxCount');

        $compression = $this->dic()->get('channel')::getNoCompressionType();
        if (!$isPrintPage && (($startFrame !== 0) || ($endFrame !== $framesCount))) {
            $compression = $this->dic()->get('channel')::getAroundRangeCompressionType();
        } else if (!$isPrintPage && ($framesCount * $totalSeriesCount > $pointsMaxCount)) {
            $compression = $this->dic()->get('channel')::getGeneralCompressionType();
        }

        $table = $this->dic()->get('fdr')->getAnalogTable($flight->getGuid(), $param['prefix']);

        $syncParam = $this->dic()->get('channel')->get(
            $table,
            $paramApCode,
            $startFrame,
            $endFrame,
            $totalSeriesCount,
            $framesCount,
            $pointsMaxCount,
            $compression
        );

        return json_encode($syncParam);
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

    public function rcvLegend($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['paramCodes']))
        {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $paramCodes = $data['paramCodes'];

        $legend = $this->GetLegend($flightId, $paramCodes);

        return json_encode($legend);
    }

    public function getParamMinmaxAction($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['paramCode'])
            || !isset($data['tplName'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $paramCode = $data['paramCode'];
        $tplName = $data['tplName'];

        $minmax = $this->GetParamMinmax($flightId, $paramCode, $tplName);

        return json_encode($minmax);
    }

    public function setParamMinmaxAction($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['paramCode'])
            || !isset($data['tplName'])
            || !isset($data['min'])
            || !isset($data['max'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $paramCode = $data['paramCode'];
        $tplName = $data['tplName'];
        $min = $data['min'];
        $max = $data['max'];

        $status = $this->SetParamMinmax($flightId, $paramCode, $tplName, $min, $max);

        $answ["status"] = $status;
        return json_encode($answ);
    }

    public function getParamColorAction($data)
    {
        if(!isset($data['flightId'])
            || !isset($data['paramCode'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = $data['flightId'];
        $paramCode = $data['paramCode'];

        $color = $this->GetParamColor($flightId, $paramCode);

        return json_encode($color);
    }

    public function getParamInfoAction($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['paramCode']))
        {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $paramCode = $data['paramCode'];

        $info = [];
        if (!empty($paramCode)) {
            $info = $this->GetParamInfo($flightId, $paramCode);
        }

        echo json_encode($info);
    }

    public function getFlightExceptionsAction($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['refParam']))
        {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $paramCode = $data['refParam'];

        $exceptions = $this->GetFlightExceptions($flightId, $paramCode);

        return json_encode($exceptions);
    }

    public function figurePrint($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['startFrame'])
            || !isset($data['endFrame'])
            || !isset($data['analogParams'])
        ) {
            throw new BadRequestException(json_encode($data));
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
