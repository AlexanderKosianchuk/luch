<?php

namespace Component;

use Exception;

class RuntimeDatabaseComponent extends BaseComponent
{
  public function getDataTableName($uploadingUid)
  {
    return $uploadingUid.'_rtcd';
  }

  public function getEventTableName($uploadingUid)
  {
    return $uploadingUid.'_rtce';
  }

  public function putRealtimeCalibrationData(
    $uploadingUid,
    $frameNum,
    $currentTime,
    $stepLength,
    $fullFrame,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getDataTableName($uploadingUid);

    $this->createDataTable($tableName, $fullFrame, $internalLink);
    $maxFreq = count($fullFrame[0]['values']);
    $subStep = 1000 * $stepLength / $maxFreq;

    for ($ii = 0; $ii < $maxFreq; $ii++) {
      $query = 'INSERT INTO `'.$tableName.'` (`frame_num`, `time` ';

      $codes = [];
      foreach ($fullFrame as $item) {
        $code = $item['param']['code'];
        if (isset($codes[$code])) {
          $code = $item['param']['code'].$item['param']['type'];
        }
        $codes[$code] = true;
        $code = $code;
        $query .= ', `'.$code.'`';
      }

      $query .= ') VALUES ('.$frameNum.','.($currentTime+$subStep*$ii);

      foreach ($fullFrame as $item) {
        $value = $item['values'][$ii];
        $query .= ', '.$value;
      }

      $query .= ');';

      $stmt = $internalLink->prepare($query);
      if ($stmt->execute() === false) {
        error_log(mysqli_error($internalLink));
      }
      $stmt->close();
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }
  }

  private function createDataTable(
    $tableName, $fullFrame, $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    if ($this->connection()->isExist($tableName, 'runtime', $internalLink)) {
      return;
    }

    $query = 'CREATE TABLE `'.$tableName.'` (`frame_num` MEDIUMINT, `time` BIGINT';

    $codes = [];
    foreach ($fullFrame as $item) {
      $code = $item['param']['code'];

      if (isset($codes[$code])) {
        $code = $item['param']['code'].$item['param']['type'];
      }

      $codes[$code] = true;

      $dataType = 'FLOAT(1,0)'; // for binaries
      if (isset($item['param']['dataType'])) {
        $dataType = $item['param']['dataType'];
      }

      $query .= ', `'.$code.'` ' . $dataType;
    }

    $query .= ', PRIMARY KEY (`frame_num`, `time`)) ' .
        'DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MEMORY;';

    $stmt = $internalLink->prepare($query);
    $stmt->execute();
    $stmt->close();

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }
  }

  private function createEventsTable(
    $tableName, $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    if ($this->connection()->isExist($tableName, 'runtime', $internalLink)) {
      return;
    }

    $query = 'CREATE TABLE `'.$tableName.'` '
      .'(`id` INT NOT NULL AUTO_INCREMENT, '
      .'`frame_num` MEDIUMINT NOT NULL, '
      .'`id_event` INT NOT NULL, '
      .'`value` VARCHAR(255) NOT NULL, '
      .'PRIMARY KEY (`id`, `frame_num`)) '
      .'DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MEMORY;';

    $stmt = $internalLink->prepare($query);
    $stmt->execute();
    $stmt->close();

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }
  }

  public function getProcessResults(
    $uploadingUid,
    $frameNum,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getEventTableName($uploadingUid);

    $this->createEventsTable($tableName, $internalLink);

    $query = 'SELECT * FROM `'.$tableName.'` WHERE `frame_num` = '.$frameNum.';';
    $result = $link->query($query);

    $prevEvents = [];
    while ($row = $result->fetch_array()) {
      if (!isset($row[0])) {
        continue;
      }

      $prevEvents[] = [
        'frameNum' => intval($row['frame_num']),
        'eventId' => intval($row['id_event']),
        'value' => $row['value']
      ];
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }

    return $prevEvents;
  }

  public function putRealtimeCalibrationEvents(
    $uploadingUid,
    $eventResults,
    $frameNum,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getEventTableName($uploadingUid);

    $this->createEventsTable($tableName, $internalLink);

    for ($ii = 0; $ii < count($eventResults); $ii++) {
      $query = 'INSERT INTO `'.$tableName.'` ( '
        .'`frame_num`, '
        .'`id_event`, '
        .'`value`) VALUES ('
        . $frameNum . ', ' . $eventResults[$ii]['eventId'] . ', '
        . $eventResults[$ii]['value']
        . ');';

      $stmt = $internalLink->prepare($query);
      $stmt->execute();
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }
  }

  public function cleanUpRealtimeCalibrationData(
    $uploadingUid,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tables = [
      $this->getEventTableName($uploadingUid),
      $this->getDataTableName($uploadingUid)
    ];

    foreach ($tables as $tableName) {
      if ($this->connection()->isExist($tableName, 'runtime', $internalLink)) {
        $query = 'DROP TABLE `'.$tableName.'`;';

        $stmt = $internalLink->prepare($query);
        $stmt->execute();
      }
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }
  }

  public function getRealtimeCalibrationFrameNum(
    $uploadingUid,
    $timestamp,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getDataTableName($uploadingUid);

    $query = 'SELECT * FROM `'.$tableName.'` '
      .'WHERE `time` = '.$timestamp.' LIMIT 1;';
    $result = $link->query($query);

    $frameNum = 0;
    if ($row = $result->fetch_assoc()) {
      $frameNum = $row['frame_num'];
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }

    return $frameNum;
  }

  public function getRealtimeCalibrationData(
    $uploadingUid,
    $timestamp,
    $limit = 100,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getDataTableName($uploadingUid);

    if (!$this->connection()->isExist($tableName, 'runtime', $internalLink)) {
      return [];
    }

    $toFrameNum = $this->getRealtimeCalibrationFrameNum(
      $uploadingUid,
      $timestamp,
      $link
    );

    $fromFrameNum = $toFrameNum - $limit;

    $query = 'SELECT * FROM `'.$tableName.'` '
      .'WHERE `frame_num` >= '.$fromFrameNum.' AND '
      .'`frame_num` < '.$toFrameNum.';';
    $result = $link->query($query);

    $data = [];
    $frames = [];
    while ($row = $result->fetch_assoc()) {
      if (!isset($frames[$row['frame_num']])) {
        $data[] = $row;
        $frames[$row['frame_num']] = 1;
      }
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }

    return $data;
  }

  public function getRealtimeCalibrationEvents(
    $uploadingUid,
    $timestamp,
    $limit = 100,
    $link = null
  ) {
    $internalLink = $link;
    if ($link === null) {
      $internalLink = $this->connection()->create('runtime');
    }

    $tableName = $this->getEventTableName($uploadingUid);

    if (!$this->connection()->isExist($tableName, 'runtime', $internalLink)) {
      return [];
    }

    $toFrameNum = $this->getRealtimeCalibrationFrameNum(
      $uploadingUid,
      $timestamp,
      $link
    );

    $fromFrameNum = $toFrameNum - $limit;

    $query = 'SELECT * FROM `'.$tableName.'` '
      .'WHERE `frame_num` >= '.$fromFrameNum.' AND '
      .'`frame_num` < '.$toFrameNum.';';
    $result = $link->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    if ($link === null) {
      $this->connection()->destroy($internalLink);
    }

    return $data;
  }
}
