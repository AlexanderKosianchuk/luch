<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class ChartController extends BaseController
{
  public function getLegendAction($flightId, $paramCodes)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $infoArray = [];
    foreach ($paramCodes as $code) {
      $param = $this->dic('fdr')->getParamByCode(
        $flight->getFdrId(),
        $code
      );

      if ($param['type'] === $this->dic('fdr')::getApType()) {
        $infoArray[] = $param['name'].', '.$param['dim'];
      } else if ($param['type'] === $this->dic('fdr')::getBpType()) {
        $infoArray[] = $param['name'];
      }
    }

    return json_encode($infoArray);
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
      $param = $this->dic('fdr')
        ->getParamByCode($flight->getFdrId(), $analogParams[$ii]);

      $paramsDescriprion[$analogParams[$ii]] = $param;
      $table = $flight->getGuid().'_'.$this->dic('fdr')->getApType().'_'.$param['prefix'];

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
      $param = $this->dic('fdr')
        ->getParamByCode($flight->getFdrId(), $binaryParams[$ii]);
      $paramsDescriprion[$binaryParams[$ii]] = $param;
      $table = $flight->getGuid().'_'.$this->dic('fdr')->getBpType().'_'.$param['prefix'];

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
        && $this->dic('osInfo')->isWindows()
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
