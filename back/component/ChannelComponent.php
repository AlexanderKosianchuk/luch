<?php

namespace Component;

class ChannelComponent extends BaseComponent
{
  const NO_COMPRESSION = 'none';
  const AROUND_RANGE_COMPRESSION  = 'aroundRange';
  const GENERAL_COMPRESSION  = 'general';

  public static function getNoCompressionType()
  {
    return self::NO_COMPRESSION;
  }

  public static function getAroundRangeCompressionType()
  {
    return self::AROUND_RANGE_COMPRESSION;
  }

  public static function getGeneralCompressionType()
  {
    return self::GENERAL_COMPRESSION;
  }

  public static function isNoCompression($type)
  {
    return $type === self::NO_COMPRESSION;
  }

  public static function isAroundRangeCompression($type)
  {
    return $type === self::AROUND_RANGE_COMPRESSION;
  }

  public static function isGeneralCompression($type)
  {
    return $type === self::GENERAL_COMPRESSION;
  }

  public static function getCompressionTypes()
  {
    return [
      self::NO_COMPRESSION,
      self::AROUND_RANGE_COMPRESSION,
      self::GENERAL_COMPRESSION
    ];
  }

  public function get(
    $tableName,
    $code,
    $startFrame,
    $endFrame,
    $seriesCount,
    $totalFramesCount,
    $pointsMaxCount,
    $compression
  ) {
    $pointPairList = [];
    $divider = ceil($totalFramesCount * $seriesCount / $pointsMaxCount);

    switch ($compression) {
      case self::getAroundRangeCompressionType():
        $query = "SELECT `time`, `".$code."` FROM `".$tableName."` WHERE
          ((`frameNum` < ".$startFrame.") AND
          ((`frameNum` % ".$divider.") = 0))
          ORDER BY `time` ASC";

        $link = $this->connection()->create('flights');
        $result = $link->query($query);

        while ($row = $result->fetch_array()) {
          $point = array($row['time'], $row[$code]);
          $pointPairList[] = $point;
        }
        $result->free();

        $query = "SELECT `time`, `".$code."` FROM `".$tableName."` WHERE
          ((`frameNum` >= ".$startFrame.") AND
          (`frameNum` <= ".$endFrame."))
          ORDER BY `time` ASC";

        $result = $link->query($query);

        while ($row = $result->fetch_array()) {
          $point = array(intval($row['time']), floatval($row[$code]));
          $pointPairList[] = $point;
        }
        $result->free();

        $query = "SELECT `time`, `".$code."` FROM `".$tableName."` WHERE
          ((`frameNum` > ".$endFrame.") AND
          ((`frameNum` % ".$divider.") = 0))
          ORDER BY `time` ASC";
        $result = $link->query($query);

        while ($row = $result->fetch_array()) {
          $point = array(intval($row['time']), floatval($row[$code]));
          $pointPairList[] = $point;
        }

        $result->free();
        $this->connection()->destroy($link);
        break;

      case self::getGeneralCompressionType():
        $query = "SELECT `time`, `".$code."` FROM `".$tableName."` WHERE"
          . " (`frameNum` % ".$divider." = 0)"
          . " ORDER BY `time` ASC";

        $link = $this->connection()->create('flights');
        $result = $link->query($query);

        while($row = $result->fetch_array()) {
          $point = array(intval($row['time']), floatval($row[$code]));
          $pointPairList[] = $point;
        }

        $result->free();
        $this->connection()->destroy($link);
        break;

      case self::getNoCompressionType():
      default:
        $query = "SELECT `time`, `".$code."` FROM `".$tableName."` WHERE 1 "
          . "ORDER BY `time` ASC";

        $link = $this->connection()->create('flights');
        $result = $link->query($query);

        while ($row = $result->fetch_array()) {
          $point = array(intval($row['time']), floatval($row[$code]));
          $pointPairList[] = $point;
        }

        $result->free();
        $this->connection()->destroy($link);
        break;
    }

    return $pointPairList;
  }

  public function getBinary(
    $tableName,
    $code,
    $stepLength,
    $freq
  ) {
    $stepMicroTime = $stepLength / $freq * 1000;

    $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE " .
      "`code` = ? ORDER BY `time` ASC;";

    $link = $this->connection()->create('flights');

    $stmt = $link->prepare($query);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    $pointPairList = array();
    $pointPairList2 = array();

    //if exists though one row in table
    if ($row = $result->fetch_array()) {
      $point = array('null','null');
      $pointPairList[] = $point;
      $currTime = $row['time'];

      $point = array($currTime, 1);
      $pointPairList[] = $point;
      $previousTime = $currTime;

      $pointPairList2[] = $point;

      //our task is to find first appearence of bp, write it, path to the last
      //appearence, also write it, put null and than search next appearance
      while($row = $result->fetch_array()) {
        $currTime = $row['time'];

        $pointPairList2[] = array($currTime, 1);
        if ($previousTime == $currTime - $stepMicroTime) {
          if (count($pointPairList) > 2) {
            if ($pointPairList[count($pointPairList) - 3][1] == 'null') {
              $point = array($currTime, 1);
              $pointPairList[count($pointPairList) - 1] = $point;
              $previousTime = $currTime;
            } else {
              $point = array($currTime, 1);
              $pointPairList[] = $point;
              $previousTime = $currTime;
            }
          } else {
            $point = array($currTime, 1);
            $pointPairList[] = $point;
            $previousTime = $currTime;
          }
        } else {
          $point = array('null','null');
          $pointPairList[] = $point;
          $point = array($currTime, 1);
          $pointPairList[] = $point;
          $previousTime = $currTime;
        }
      }

      $result->free();
    } else {
      $point = array('null','null');
      $pointPairList[] = $point;
    }


    $this->connection()->destroy($link);
    return $pointPairList;
  }

  public function getParamValue(
    $tableName,
    $code,
    $frameNum
  ) {
    $query = 'SELECT `'.$code.'`, `time` FROM `'.$tableName.'` WHERE ' .
      '`frameNum` = ?;';

    $link = $this->connection()->create('flights');

    $stmt = $link->prepare($query);
    $stmt->bind_param('i', $frameNum);
    $stmt->execute();
    $result = $stmt->get_result();

    $val = null;
    if ($row = $result->fetch_array())
      $val = $row[$code];

    $this->connection()->destroy($link);
    return $val;
  }

  public function GetBinaryParam($bpTableName, $code, $stepLength, $freq)
  {
    $pointPairList = array();

    $pointPairList = $this->getBinary($bpTableName, $code, $stepLength, $freq);

    $tempString = json_encode($pointPairList);
    //in bin params point equal to null we had put ["null","null"]
    $searchSubstr = '["null","null"]';
    $transmitStr = str_replace($searchSubstr, 'null', $tempString);

    return json_decode($transmitStr);
  }

  public function GetNormalizedApParam(
    $tableName,
    $stepDivider,
    $code,
    $steps,
    $startFrame,
    $endFrame
  ) {
    $duplication = $stepDivider / $steps;

    $query = 'SELECT `'.$code.'` FROM `'.$tableName.'` WHERE '
      .'`frameNum` >= ? AND `frameNum` < ? ORDER BY `frameNum` ASC;';

    $link = $this->connection()->create('flights');
    $stmt = $link->prepare($query);
    $stmt->bind_param('ii', $startFrame, $endFrame);
    $stmt->execute();
    $result = $stmt->get_result();

    $normArr = [];
    while ($row = $result->fetch_array()) {
      array_push($normArr, $row[$code]);
      for ($i = 1; $i < $duplication; $i++) {
        array_push($normArr, $row[$code]);
      }
    }

    $result->free();
    $this->connection()->destroy($link);

    return $normArr;
  }

  public function getNormalizedBpParam(
    $tableName,
    $stepDivider,
    $code,
    $steps,
    $startFrame,
    $endFrame
  ) {
    $duplication = $stepDivider / $steps;
    $totalRows = ($endFrame - $startFrame) * $stepDivider;


    $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE `code` = ? ".
      "AND `frameNum` >= ? AND `frameNum` < ? ORDER BY `time` ASC;";

    $link = $this->connection()->create('flights');
    $stmt = $link->prepare($query);
    $stmt->bind_param('sii', $code, $startFrame, $endFrame);
    $stmt->execute();
    $result = $stmt->get_result();

    $normArr = array();
    for ($i = 0; $i < $totalRows; $i++) {
      $normArr[$i] = 0;
    }

    while ($row = $result->fetch_array()) {
      $position = ($row['frameNum'] - $startFrame) * $stepDivider;
      $normArr[$position] = 1;
      for ($i = 1; $i < $stepDivider; $i++) {
        $position = ($row['frameNum'] - $startFrame) * $stepDivider + $i;
        $normArr[$position] = 1;
      }
    }

    $result->free();
    $this->connection()->destroy($link);

    return $normArr;
  }

  public function normalizeTime(
    $stepDivider,
    $stepLength,
    $totalFrameNum,
    $startCopyTime,
    $startFrame,
    $endFrame
  ) {
    $stepMicroTime = round($stepLength * 1000 / $stepDivider, 0);

    $normTime = [];
    $currTime = $startCopyTime * 1000;
    for ($ii = $startFrame; $ii < ($endFrame * $stepDivider); $ii++) {
      array_push($normTime, date("H:i:s", $currTime / 1000). "." . substr($currTime, -3));
      $currTime += $stepMicroTime;
    }
    return $normTime;
  }

  public function getParamMinMax($table, $paramCode)
  {
    $minMax = [
      'min' => 0,
      'max' => 1
    ];

    $query = "SELECT MIN(`".$paramCode."`), MAX(`".$paramCode."`) FROM `".$table."` WHERE 1;";
    $link = $this->connection()->create('flights');
    $result = $link->query($query);

    if ($row = $result->fetch_array()) {
      $minMax = [
        'min' => $row["MIN(`".$paramCode."`)"],
        'max' => $row["MAX(`".$paramCode."`)"]
      ];
    }

    $result->free();
    $this->connection()->destroy($link);

    return $minMax;
  }
}
