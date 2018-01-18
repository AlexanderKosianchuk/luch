<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

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
      $flight->getFdrId(),
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

  public function getBpParamDataAction($flightId, $code)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $param = $this->dic()->get('fdr')->getParamByCode(
      $flight->getFdrId(),
      $code
    );

    $table = $this->dic()->get('fdr')->getBinaryTable(
      $flight->getGuid(),
      $param['prefix']
    );

    return json_encode($this->dic()->get('channel')->getBinary(
      $table,
      $code,
      $flight->getFdr()->getStepLength(),
      $param['frequency']
    ));
  }

  public function getParamInfoAction($flightId, $code)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $paramInfo = $this->dic()->get('fdr')
      ->getParamByCode($flight->getFdrId(), $code);

    echo json_encode($paramInfo);
  }

  public function getParamMinMaxAction($flightId, $code, $tplName)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    return json_encode($this->dic()->get('fdrTemplate')
      ->GetParamMinMax(
        $flight->getFdr()->getCode(),
        $tplName,
        $code
      )
    );
  }

  public function getFlightExceptionsAction($flightId, $refParam)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $events = $this->dic()->get('event')
      ->getFlightEventsByRefParam(
        $flight,
        $refParam
      );

    $chartEventBoxes = [];

    foreach ($events as $event) {
      $param = $this->dic()->get('fdr')->getParamByCode(
        $flight->getFdrId(),
        $event['refParam']
      );

      $val = 1;
      if ($param['type'] === $this->dic()->get('fdr')::getApType()) {
        $val = $this->dic()->get('channel')->getParamValue(
          $flight->getGuid().'_'.$param['type'].'_'.$param['prefix'],
          $event['refParam'],
          $event['frameNum']
        );
      }

      //Because of cyrillic string
      $unicodeConv = function($key, $param) {
        if (($param[$key] != "") && ($param[$key] != " ") && ($param[$key] != null)) {
          $str = is_array($param[$key]) ? implode('; ', $param[$key]) : $param[$key];
          // The four \\\\ in the pattern here are necessary to match \u in the original string
          $replacedString = preg_replace("/\\\\u(\w{4})/", "&#$1;", $str);
          $unicodeString = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
          return $unicodeString . "; ";
        }

        return '';
      };

      $comment = '';
      foreach (['text', 'status', 'algText', 'userComment', 'excAditionalInfo'] as $key) {
        $comment .= $unicodeConv($key, $event);
      }

      $chartEventBoxes[] = [
        $event['startTime'],
        $event['endTime'],
        $event['code'],
        $val,
        $comment,
        $event['visualization'],
        $event['refParam']
      ];
    }

    return json_encode($chartEventBoxes);
  }

  public function getLegendAction($flightId, $paramCodes)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $infoArray = [];
    foreach ($paramCodes as $code) {
      $param = $this->dic()->get('fdr')->getParamByCode(
        $flight->getFdrId(),
        $code
      );

      if ($param['type'] === $this->dic()->get('fdr')::getApType()) {
        $infoArray[] = $param['name'].', '.$param['dim'];
      } else if ($param['type'] === $this->dic()->get('fdr')::getBpType()) {
        $infoArray[] = $param['name'];
      }
    }

    return json_encode($infoArray);
  }

  public function setParamMinMaxAction(
    $flightId,
    $paramCode,
    $tplName,
    $min,
    $max
  ) {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $this->dic()->get('fdrTemplate')
      ->setParamMinMax(
        $flight->getFdrCode(),
        $tplName,
        $paramCode,
        (object)['min' => $min, 'max' => $max]
      );

    return json_encode('ok');
  }

  public function figurePrintAction(
    $flightId,
    $startFrame,
    $endFrame,
    $analogParams,
    $binaryParams = []
  ) {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $step = $this->dic()
      ->get('userSettings')
      ->getSettingValue('printTableStep');

    if ($step === null) {
      $step = 0;
    } else {
      $step = $step * $flight->getFdr()->getStepDivider();
    }

    $startCopyTime = $flight->getStartCopyTime();
    $stepLength = $flight->getFdr()->getStepLength();
    $stepDivider = $flight->getFdr()->getStepDivider();

    if ($startFrame < 0) {
      $fromTime = 0;
    }

    $framesCount = $endFrame - $startFrame;

    $normParam = $this->dic()
      ->get('channel')
      ->normalizeTime(
        $stepDivider,
        $stepLength,
        $framesCount,
        $startCopyTime,
        $startFrame,
        $endFrame
      );

    $paramsDescriprion = [];
    $globalRawParamArr = [];
    array_push($globalRawParamArr, $normParam);

    for ($ii = 0; $ii < count($analogParams); $ii++) {
      $param = $this->dic()->get('fdr')
        ->getParamByCode($flight->getFdrId(), $analogParams[$ii]);

      $paramsDescriprion[$analogParams[$ii]] = $param;
      $table = $flight->getGuid().'_'.$this->dic()->get('fdr')->getApType().'_'.$param['prefix'];

      $normParam = $this->dic()
        ->get('channel')
        ->getNormalizedApParam(
          $table,
          $stepDivider,
          $param['code'],
          $param['frequency'],
          $startFrame,
          $endFrame
        );

      array_push($globalRawParamArr, $normParam);
    }

    for ($ii = 0; $ii < count($binaryParams); $ii++) {
      $param = $this->dic()->get('fdr')
        ->getParamByCode($flight->getFdrId(), $binaryParams[$ii]);
      $paramsDescriprion[$binaryParams[$ii]] = $param;
      $table = $flight->getGuid().'_'.$this->dic()->get('fdr')->getBpType().'_'.$param['prefix'];

      $normParam = $this->dic()
        ->get('channel')
        ->getNormalizedBpParam(
          $table,
          $stepDivider,
          $param['code'],
          $param['frequency'],
          $startFrame,
          $endFrame
      );

      array_push($globalRawParamArr, $normParam);
    }

    $totalRecords = count($globalRawParamArr[1]); // 0 is time and may be lager than data

    $prms = array_merge($analogParams, $binaryParams);

    $figPrRow = "time;";
    for ($i = 0; $i < count($prms); $i++) {
      $paramInfo = $paramsDescriprion[$prms[$i]];

      $paramName = str_replace(["\n","\r\n","\r", ";", PHP_EOL], '', $paramInfo['name']);

      if (($this->user()->getLang() === 'ru')
        && $this->dic()->get('osInfo')->isWindows()
      ) {
        $figPrRow .= iconv('utf-8', 'windows-1251', $paramName) . ";";
      } else {
        $figPrRow .= $paramName . ";";
      }
    }

    $figPrRow = substr($figPrRow, 0, -1);
    $figPrRow .= PHP_EOL;

    $figPrRow .= 'T;';
    for ($i = 0; $i < count($prms); $i++) {
      $paramInfo = $paramsDescriprion[$prms[$i]];
      $figPrRow .= $paramInfo['code'] . ';';
    }

    $fileGuid = uniqid();

    $fileName = $flight->getBort().'_'
      .date('Y-m-d', $flight->getStartCopyTime()).'_'
      .$flight->getVoyage().'_'
      .$fileGuid.'_'
      .$this->user()->getLogin().'.csv';

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
