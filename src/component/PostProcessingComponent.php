<?php

namespace Component;

use Exception;

class PostProcessingComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Component\FlightComponent
   */
  private $flightComponent;

  /**
   * @Inject
   * @var Component\FdrComponent
   */
  private $fdrComponent;

  /**
   * @Inject
   * @var Component\ChannelComponent
   */
  private $channelComponent;

  /**
   * @Inject
   * @var Component\RuntimeManager
   */
  private $runtimeManager;


  public function secondPassProcess(\Entity\Flight $flight) {
    $userId = $this->user()->getId();

    $params = $this
      ->fdrComponent
      ->getParams($flight->getFdrId());

    $secondPassParams = [];
    $secondPassParamsByPrefixes = [];

    foreach ($params as $item) {
      if ($item->getRawChannel() === '-1') {
        $secondPassParams[] = $item;

        if (!isset($secondPassParamsByPrefixes[$item->getPrefix()])) {
          $secondPassParamsByPrefixes[$item->getPrefix()] = [];
        }

        $secondPassParamsByPrefixes[$item->getPrefix()][] = $item;
      }
    }

    if (count($secondPassParams) === 0) {
      return;
    }

    $data = [];

    foreach ($secondPassParams as $param) {
      $method = 'calc'.$param->getCode();
      if (!method_exists($this, $method)) {
        continue;
      }

      $data = $this->$method($flight, $param);
    }

    $tables = $this->createPostParamTables($flight->getGuid(), $secondPassParamsByPrefixes);

    $this->writeToTableFile($data, $flight->getGuid(), $param->getPrefix());

    foreach ($tables as $tableName) {
      $this->loadParamFilesToTables($tableName);
    }
  }

  private function calcLAT($flight, $param)
  {
    $COURSE_PARAM_CODE = 'KK';
    $HEIGHT_PARAM_CODE = 'HG150';

    if (empty($flight->getDepartureAirport())
      || empty($flight->getArrivalAirport())
      || ($flight->getDepartureAirport() === 'x')
      || ($flight->getArrivalAirport() === 'x')
    ) {
      return;
    }

    $courseParam = $this->fdrComponent->findByCode($flight->getFdrId(), $COURSE_PARAM_CODE);
    $heightParam = $this->fdrComponent->findByCode($flight->getFdrId(), $HEIGHT_PARAM_CODE);

    if (count($courseParam) < 1) { return; }
    if (count($heightParam) < 1) { return; }

    $courseParam = $courseParam[0];
    $heightParam = $heightParam[0];

    $table = $this->fdrComponent->getAnalogTable($flight->getGuid(), $courseParam->getPrefix());

    $courceValues = $this->channelComponent->getAllByCode(
      $table,
      $courseParam->getCode()
    );

    $table = $this->fdrComponent->getAnalogTable($flight->getGuid(), $heightParam->getPrefix());

    $heightValues = $this->channelComponent->getAllByCode(
      $table,
      $heightParam->getCode()
    );

    $takeOffFrameNum = 0;
    $landingFrameNum = 0;

    for ($ii = 0; $ii < count($heightValues); $ii++) {
      if ($heightValues[$ii][$HEIGHT_PARAM_CODE] > 0) {
        $takeOffFrameNum = $heightValues[$ii]['frameNum'];
        break;
      }
    }

    for ($ii = count($heightValues) - 1; $ii > 0; $ii--) {
      if ($heightValues[$ii][$HEIGHT_PARAM_CODE] > 0) {
        $landingFrameNum = $heightValues[$ii]['frameNum'];
        break;
      }
    }

    if (($takeOffFrameNum === 0) || ($landingFrameNum === 0)) {
      return;
    }

    $courceAtTakeOff = 0;
    $courceAtLanding = 0;

    for ($ii = 0; $ii < count($courceValues); $ii++) {
      if ($courceValues[$ii]['frameNum'] > $takeOffFrameNum) {
        $courceAtTakeOff = $courceValues[$ii][$COURSE_PARAM_CODE];
        break;
      }
    }

    for ($ii = count($courceValues) - 1; $ii > 0; $ii--) {
      if ($courceValues[$ii]['frameNum'] > $landingFrameNum) {
        $courceAtLanding = $courceValues[$ii][$COURSE_PARAM_CODE];
        break;
      }
    }

    $takeOffAirport = $this->em()
      ->getRepository('Entity\Airport')
      ->createQueryBuilder('airport')
      ->where('(airport.iata = :name OR airport.icao = :name) AND airport.course > :minCource AND airport.course < :maxCource')
      ->setParameters([
        ':name' => $flight->getDepartureAirport(),
        ':minCource' => $courceAtTakeOff - 10,
        ':maxCource' => $courceAtTakeOff + 10
      ])
      ->getQuery()
      ->getArrayResult();

    $takeOffAirport = $takeOffAirport[0];

    $landingAirport = $this->em()
      ->getRepository('Entity\Airport')
      ->createQueryBuilder('airport')
      ->where('(airport.iata = :name OR airport.icao = :name) AND airport.course > :minCource AND airport.course < :maxCource')
      ->setParameters([
        ':name' => $flight->getArrivalAirport(),
        ':minCource' => $courceAtLanding - 10,
        ':maxCource' => $courceAtLanding + 10
      ])
      ->getQuery()
      ->getArrayResult();

    $landingAirport = $landingAirport[0];

    $turnsFrameNums = $this->getTurns($courceValues, $COURSE_PARAM_CODE, $takeOffFrameNum, $landingFrameNum);

    return $turnsFrameNums;
  }

  private function getTurns($values, $code, $start, $end)
  {
    $dx = [[
      'frameNum' => $values[0]['frameNum'],
      'time' => $values[0]['time'],
      'value' => 0
    ]];

    for ($ii = 1; $ii < count($values); $ii++) {
      $currentVal = $values[$ii];
      if (($currentVal['frameNum'] < $start)
        || ($currentVal['frameNum'] > $end)
      ) {
        $dx[] = [
          'frameNum' => $currentVal['frameNum'],
          'time' => $currentVal['time'],
          'value' => 0
        ];
      }

      $dx[] = [
        'frameNum' => $currentVal['frameNum'],
        'time' => $currentVal['time'],
        'value' => $currentVal[$code] - $values[$ii][$code]
      ];
    }

    $dxNoZero = [];
    for ($ii = 0; $ii < count($dx); $ii++) {
      if ($dx[$ii]['value'] !== 0) {
        $dxNoZero[] = $dx[$ii];
      }
    }

    $dxByRanges = [[]];
    $rangeNum = 0;
    for ($ii = 1; $ii < count($dxNoZero); $ii++) {
      if (($dxNoZero[$ii - 1]['frameNum'] + 1) != $dxNoZero[$ii]['frameNum']) {
        $rangeNum++;
        $dxByRanges[$rangeNum] = [];
      }

      $dxByRanges[$rangeNum] = $dxNoZero;
    }

$dx2 = [];
    for ($ii = 0; $ii < count($dx); $ii++) {
      $dx2[] = [
        'frameNum' => $dx[$ii]['frameNum'],
        'time' => $dx[$ii]['time'],
        'LAT' => $dx[$ii]['value']
      ];
    }

    return $dx2;
  }

  public function writeToTableFile($data, $flightUid, $prefix)
  {
    foreach ($data as $frame) {
      $this->runtimeManager->writeToRuntimeTemporaryFile(
        $this->params()->folders->uploadingFlightsTables,
        $this->fdrComponent->getAnalogTable($flightUid, $prefix),
        $frame,
        'csv'
      );
    }
  }

  public function createPostParamTables($flightUid, $paramCyclo)
  {
    $tables = [];
    $link = $this->connection()->create('flights');

    foreach ($paramCyclo as $prefix => $cyclo) {
      $table = $this->fdrComponent->getAnalogTable($flightUid, $prefix);
      $tables[] = $table;

      $query = "CREATE TABLE `".$table."` (`frameNum` MEDIUMINT, `time` BIGINT";

      foreach ($cyclo as $param) {
        $query .= ", `".$param->getCode()."` " . $param->getDataType();
      }

      $query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
          "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";

      $stmt = $link->prepare($query);
      $stmt->execute();
      $stmt->close();
    }

    $this->connection()->destroy($link);

    return $tables;
  }

  public function loadParamFilesToTables($tableName, $filePath = null)
  {
    if ($filePath === null) {
      $file = $this->runtimeManager->getTemporaryFileDesc(
        $this->params()->folders->uploadingFlightsTables,
        $tableName,
        'close'
      );
      $filePath = $file->path;
    }

    $this->connection()->loadFile($tableName, $filePath);

    if (file_exists($filePath)) {
      unlink($filePath);
    }
  }

}
