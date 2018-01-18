<?php

namespace Component;

use Exception;

class FrameComponent extends BaseComponent
{
  public function convertFrameToPhisics(
    $frame,
    $startTime,
    $stepLength,
    $frameNum,
    $cycloAp,
    & $algHeap,
    $channelFreq = 1)
  {
    $phisicsFrame = [];

    for ($ind1 = 0; $ind1 < count($cycloAp); $ind1++) {
      $paramCyclo = $cycloAp[$ind1];
      $channels = $paramCyclo['channel'];

      $paramType = $paramCyclo['type'];
      $paramTypeArr = array();
      $paramTypeArg = array();

      if (strpos("/", $paramType) > -1) {
        $paramTypeArr = explode("/", $paramType);

        $paramTypeArg = $paramTypeArr[0];
        $paramType = $paramTypeArr[1];

        if (strpos("i", $paramTypeArg) > -1) {
          $codeValue = ~$codeValue;
        }

        if(strpos("r", $paramTypeArg) > -1) {
          $newCodeVal = '';
          for($rotInd = strlen($codeValue) - 2; $rotInd >= 0; $rotInd-=2)
          {
            $newCodeVal .= substr ($codeValue, $rotInd, 2);
          }
          $codeValue = $newCodeVal;
        }
      }

      $interview = array();
      for ($ind2 = 0; $ind2 < count($channels); $ind2++) {
        $codeValue = $frame[$channels[$ind2]];

        //get phisics analog param from code
        if ($paramType == 1) {//type 1 uses for graduated params
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $gradi = $paramCyclo['xy'];

          for ($j = 0; $j < count($gradi); $j++) {
            if($apCode <= $gradi[$j]['y']) {
              break;
            }
          }

          //faling extrapolation
          if($j == 0) {
            //exact match
            if($apCode == $gradi[$j]['y']) {
              $phisics = $gradi[$j]['x'];
            } else {
              $p = $apCode;
              $p0 = $gradi[0];
              $p1 = $gradi[1];

              if ($p1['y'] - $p0['y'] == 0) {
                $phisics = 0;
              } else {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }
          else if($j >= (count($gradi) - 1)) {//rising extrapolation
            //exact match
            if ($apCode == $gradi[count($gradi) - 1]['y']) {
              $phisics = $gradi[count($gradi) - 1]['x'];
            } else {
              $p = $apCode;
              $p0 = $gradi[count($gradi) - 2];
              $p1 = $gradi[count($gradi) - 1];

              if ($p1['y'] - $p0['y'] == 0) {
                $phisics = 0;
              } else {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          } else {//interpolation
            //exact match
            if ($apCode == $gradi[$j]['y']) {
              $phisics = $gradi[$j]['x'];
            } else {
              $p = $apCode;
              $p0 = $gradi[$j - 1];
              $p1 = $gradi[$j];

              if ($p1['y'] - $p0['y'] == 0) {
                $phisics = 0;
              } else {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }

          array_push($interview, $phisics);
        } else if($paramType == 2) {//calc param
          $alg = $paramCyclo['alg'];
          $alg = str_replace("[p]", "'" . $codeValue . "'", $alg);
          $alg = str_replace("[k]", $paramCyclo['k'], $alg);
          $alg = str_replace("[mask]", $paramCyclo['mask'], $alg);
          $alg = str_replace("[shift]", $paramCyclo['shift'], $alg);
          $alg = str_replace("[minus]", $paramCyclo['minus'], $alg);
          $alg = str_replace("[xy]", json_encode($paramCyclo['xy']), $alg);

          error_reporting(0);
          try {
            eval($alg);//$phisics must be assigned in alg
          } catch (Exception $e) {
            error_log('Eval exc. '.$paramCyclo['code'].'. '.$e);
            $phisics = 0;
          }
          error_reporting(E_ALL);

          array_push($interview, $phisics);

        } else if($paramType == 3) {//left bit as sign
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift']);
          $minus = (hexdec($codeValue) & $paramCyclo['minus']);
          if($minus > 0)
          {
            $apCode = $apCode * -1;
          }

          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        } else if($paramType == 4) { //unsigned params with coef

          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        } else if($paramType == 41) {//unsigned params with coef and invers by 255
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $phisics = 255 - ($apCode * $paramCyclo['k']);

          array_push($interview, $phisics);
        } else if($paramType == 42) {// simple HEX
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $apCode = dechex($apCode);
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        } else if($paramType == 5) {//signed params with coef
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift'] + 1);
          $minus = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift']);
          if($minus > $paramCyclo['mask'] / 2)
          {
            $apCode = $apCode - $paramCyclo['mask'];
          }
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 6)//unsigned params with coef with rotation bytes in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;

          $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 62)//HEX rotation
        {
          $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;

          $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $apCode = dechex($apCode);
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 7)//using field minus to find negative values with rotation bytes in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          if($apCode >= $paramCyclo['minus'])
          {
            $apCode -= $paramCyclo['minus'] * 2;
          }
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 8)//signed params with coef with negative values
        {
           $apCode = (hexdec($codeValue));
           if($apCode > 32768)
           {
             $apCode -= 65535;
           }
           $phisics = $apCode * $paramCyclo['k'];

           array_push($interview, $phisics);
        }
        else if($paramType == 9)//signed params with coef with gradual rotation
        {
          $apCode = (hexdec($codeValue));
          if($apCode > 32768)
          {
            $apCode -= 65535;
          }
          $phisics = $apCode * $paramCyclo['k'];
          if($phisics < 0)
          {
            $phisics += 360;
          }

          array_push($interview, $phisics);
        }
        else if($paramType == 10)//using field minus to find negative values
        {
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          if($apCode >= $paramCyclo['minus'])
          {
            $apCode -= $paramCyclo['minus'] * 2;
          }
          $phisics = $apCode * $paramCyclo['k'];
          array_push($interview, $phisics);
        } else if($paramType == 30) {
          if($paramCyclo['SUB'] ==  0)
          {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
            if($apCode >= $paramCyclo['minus']) {
              $apCode -= $paramCyclo['minus'] * 2;
            }
            $phisics = $apCode * $paramCyclo['k'];

          } else {
            $phisics = 211;
          }
          array_push($interview, $phisics);
        }
        else if($paramType == 21)// graduated with invertion
        {
          $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $apCode = 255 - $apCode;
          $gradi = $paramCyclo['xy'];

          for($j = 0; $j < count($gradi); $j++)
          {
            if($apCode <= $gradi[$j]['y'])
            {
              break;
            }
          }

          //faling extrapolation
          if($j == 0)
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[0];
              $p1 = $gradi[1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }
          //rising extrapolation
          else if($j >= (count($gradi) - 1))
          {
            //exact match
            if($apCode == $gradi[count($gradi) - 1]['y'])
            {
              $phisics = $gradi[count($gradi) - 1]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[count($gradi) - 2];
              $p1 = $gradi[count($gradi) - 1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }

          }
          //interpolation
          else
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[$j - 1];
              $p1 = $gradi[$j];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }

          array_push($interview, $phisics);
        }
        else if($paramType == 22)// graduated with rotation
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $gradi = $paramCyclo['xy'];

          for($j = 0; $j < count($gradi); $j++)
          {
            if($apCode <= $gradi[$j]['y'])
            {
              break;
            }
          }

          //faling extrapolation
          if($j == 0)
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[0];
              $p1 = $gradi[1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }
          //rising extrapolation
          else if($j >= (count($gradi) - 1))
          {
            //exact match
            if($apCode == $gradi[count($gradi) - 1]['y'])
            {
              $phisics = $gradi[count($gradi) - 1]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[count($gradi) - 2];
              $p1 = $gradi[count($gradi) - 1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }

          }
          //interpolation
          else
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[$j - 1];
              $p1 = $gradi[$j];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }

          array_push($interview, $phisics);
        }
        else if($paramType == 23)//unsigned params with coef with rotation bytes and inversion in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;

          $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 24)//signed params with coef with rotation bytes and inversion in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;

          $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
            if($apCode > $paramCyclo['minus'])
              {$apCode = $apCode - $paramCyclo['mask'];}
          $phisics = $apCode * $paramCyclo['k'];

          array_push($interview, $phisics);
        }
        else if($paramType == 25)// graduated with rotation and inversion
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
          $gradi = $paramCyclo['xy'];

          for($j = 0; $j < count($gradi); $j++)
          {
            if($apCode <= $gradi[$j]['y'])
            {
              break;
            }
          }

          //faling extrapolation
          if($j == 0)
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[0];
              $p1 = $gradi[1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }
          //rising extrapolation
          else if($j >= (count($gradi) - 1))
          {
            //exact match
            if($apCode == $gradi[count($gradi) - 1]['y'])
            {
              $phisics = $gradi[count($gradi) - 1]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[count($gradi) - 2];
              $p1 = $gradi[count($gradi) - 1];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }

          }
          //interpolation
          else
          {
            //exact match
            if($apCode == $gradi[$j]['y'])
            {
              $phisics = $gradi[$j]['x'];
            }
            else
            {
              $p = $apCode;
              $p0 = $gradi[$j - 1];
              $p1 = $gradi[$j];

              if($p1['y'] - $p0['y'] == 0)
              {
                $phisics = 0;
              }
              else
              {
                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                    ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
              }
            }
          }

          array_push($interview, $phisics);
        }
      }
      array_push($phisicsFrame, $interview);
    }

    $phisicsFrame = $this->rotatePhisicsFrame($phisicsFrame,
      $startTime,
      $stepLength,
      $channelFreq,
      $frameNum
    );

    return $phisicsFrame;
  }

  private function rotatePhisicsFrame(
    $phisicsFrame,
    $startTime,
    $stepLength,
    $channelFreq,
    $frameNum
  ) {
    $phisicsFrameCopy = $phisicsFrame;
    $phisicsFrame = array();

    for ($i = 0; $i < $channelFreq; $i++) {
      $line = array();
      array_push($line, $frameNum);
      array_push($line, ($startTime + ($frameNum * $stepLength) + ($stepLength / $channelFreq * $i)) * 1000);

      for ($j = 0; $j < count($phisicsFrameCopy); $j++) {
        array_push($line, $phisicsFrameCopy[$j][$i]);
      }
      array_push($phisicsFrame, $line);
    }

    return $phisicsFrame;
  }

  public function searchSyncroWord (
    $frameSyncroCode,
    $offset,
    $fileDesc,
    $fileSize
  ) {
    $fileSize = $fileSize - $offset;
    fseek($fileDesc, $offset, SEEK_SET);

    $syncroWordSeek = $offset;
    $frameSyncroCode = strtolower($frameSyncroCode);
    if (($frameSyncroCode !== '') && ($frameSyncroCode !== null)) {
      if (substr($frameSyncroCode, -1) == '*') {
        $updatedSyncroCode = substr($frameSyncroCode, 0, -1);
        $syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte

        $word = stream_get_contents($fileDesc, $syncroCodeLength);
        $word = unpack("H*", $word);
        $preparedWord = $word[1];

        $syncroWordSeek += $syncroCodeLength;
        do {
          $byte = unpack("H*", stream_get_contents($fileDesc, 1));
          $byte = $byte[1];
          $preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
          $syncroWordSeek++;

          $proccesedSyncroCode = $updatedSyncroCode;
          $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
          $proccessedPreparedWordArr = str_split($preparedWord, 1);

          while(in_array('x', $proccesedSyncroCodeArr)) {
            $xPos = array_search('x', $proccesedSyncroCodeArr);
            $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
          }

          $proccesedSyncroCode = implode($proccesedSyncroCodeArr);
        }
        while(($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));
      } else {
        $updatedSyncroCode = $frameSyncroCode;
        $syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte

        $word = stream_get_contents($fileDesc, $syncroCodeLength);
        $word = unpack("H*", $word);
        $preparedWord = $word[1];

        $syncroWordSeek += $syncroCodeLength;
        do {
          $byte = unpack("H*", fread($fileDesc, 1));
          $byte = $byte[1];
          $preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
          $syncroWordSeek++;

          $proccesedSyncroCode = $updatedSyncroCode;
          $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
          $proccessedPreparedWordArr = str_split($preparedWord, 1);

          while (in_array('x', $proccesedSyncroCodeArr)) {
            $xPos = array_search('x', $proccesedSyncroCodeArr);
            $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
          }

          $proccesedSyncroCode = implode($proccesedSyncroCodeArr);
        } while (($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));

        $syncroWordSeek -= $syncroCodeLength;
      }
    }

    return $syncroWordSeek;
  }

  public function checkSyncroWord($frameSyncroCode, $unpackedFrame)
  {
    $syncroWordFound = false;
    if ($frameSyncroCode != '') {
      $frameSyncroCode = strtolower($frameSyncroCode);

      if (substr($frameSyncroCode, -1) == '*') {
        $updatedSyncroCode = substr($frameSyncroCode, 0, -1);
        $syncroCodeLength = strlen($updatedSyncroCode);
        $suggestedSyncroWord = substr($unpackedFrame, strlen($unpackedFrame) - $syncroCodeLength, $syncroCodeLength);

        $proccesedSyncroCode = $updatedSyncroCode;
        $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
        $proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

        while( in_array('x', $proccesedSyncroCodeArr)) {
          $xPos = array_search('x', $proccesedSyncroCodeArr);
          $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
        }

        $proccesedSyncroCode = implode($proccesedSyncroCodeArr);

        if ($suggestedSyncroWord == $proccesedSyncroCode) {
          $syncroWordFound = true;
        }
      } else {
        $updatedSyncroCode = $frameSyncroCode;
        $syncroCodeLength = strlen($updatedSyncroCode);
        $suggestedSyncroWord = substr($unpackedFrame, 0, $syncroCodeLength);

        $proccesedSyncroCode = $updatedSyncroCode;
        $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
        $proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

        while(in_array('x', $proccesedSyncroCodeArr)) {
          $xPos = array_search('x', $proccesedSyncroCodeArr);
          $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
        }

        $proccesedSyncroCode = implode($proccesedSyncroCodeArr);

        if($suggestedSyncroWord == $proccesedSyncroCode) {
          $syncroWordFound = true;
        }
      }
    } else {
      $syncroWordFound = true;
    }

    return $syncroWordFound;
  }

  public function convertFrameToBinaryParams(
    $frame,
    $frameNum,
    $startTime,
    $stepLength,
    $channelFreq,
    $cycloBp,
    $apPhisicsByPrefixes,
    &$algHeap
  ) {
    $phisicsBinaryParamsFrame = [];

    //get binary param from this code
    foreach($cycloBp as $binParam) {
      $channels = $binParam["channel"];

      $binParamType = $binParam['type'];
      if (strpos("/", $binParamType) > -1) {
        $binParamTypeArr = explode("/", $binParam['type']);

        $binParamTypeArg = $binParamTypeArr[0];
        $binParamType = $binParamTypeArr[1];

        if (strpos("i", $binParamTypeArg) > -1) {
          $codeValue = ~$codeValue;
        }

        if (strpos("r", $binParamTypeArg) > -1) {
          $newCodeVal = '';
          for($rotInd = strlen($codeValue) - 2; $rotInd >= 0; $rotInd-=2) {
            $newCodeVal .= substr ($codeValue, $rotInd, 2);
          }
          $codeValue = $newCodeVal;
        }
      }

      for ($chInd = 0; $chInd < count($channels); $chInd++) {
        $codeValue = $frame[$channels[$chInd]];

        if($binParamType == 1) {
          $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
          if($bpCode > 0)
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 2)//rotation bytes in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
          if($bpCode > 0)
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 41)//rotation bytes in word and > MASK
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = hexdec($rotatedStr);
          if($bpCode >= $binParam['mask'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 42)//rotation bytes in word and < MASK
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = hexdec($rotatedStr);
          if($bpCode < $binParam['mask'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }

        //virtual bp
        else if($binParamType == 3)
        {
          $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
          if(!($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        //virtual bp
        else if($binParamType == 4)//rotation bytes in word
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
          if(!($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 5)
        {
          $bpCode = (hexdec($codeValue) & $binParam['basis']);//decbin
          if($bpCode == $binParam['mask'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 6)
        {
          $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
          if(!($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 7)
        {
          $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin ( virtual type  6)
          if(($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 8)//rotation bytes in word and inversion from 65535
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = ((65535 - hexdec($rotatedStr)) & $binParam['mask']);//decbin
          if(($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 88)//virtual 8
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = ((65535 - hexdec($rotatedStr)) & $binParam['mask']);//decbin
          if(!($bpCode > 0))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 80)//rotation bytes in word and inversion from 65535 and code = MASK
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = 65535 - hexdec($rotatedStr);
          if(($bpCode == $binParam['mask']))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 81)//rotation bytes in word and inversion from 65535 and code >= MASK
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = 65535 - hexdec($rotatedStr);
          if(($bpCode >= $binParam['mask']))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 82)//invers 81 - rotation bytes in word and inversion from 65535 and code < MASK
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = 65535 - hexdec($rotatedStr);
          if(($bpCode < $binParam['mask']))
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 83)//rotation bytes in word and inversion from 65535
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = 65535 - hexdec($rotatedStr);
          $binAlgHeap['PARAM_NAME'] =  $bpCode;
          }
        else if($binParamType == 71)
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $bpCode = hexdec($tempStr1);
          if
          (
          (($bpCode <= 2) & ($bpCode >= 0))
         ||  (($bpCode <= 32) & ($bpCode >= 28))
         || (($bpCode <= 64) & ($bpCode >= 60))
         || (($bpCode <= 96) & ($bpCode >= 92))
         || (($bpCode <= 128) & ($bpCode >= 124))
         || (($bpCode <= 160) & ($bpCode >= 156))
         || (($bpCode <= 192) & ($bpCode >= 188))
         || (($bpCode <= 224) & ($bpCode >= 220))
          )
              {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
        }
        else if($binParamType == 72)
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $bpCode = hexdec($tempStr1);
          if
          (
          (($bpCode <= 2) & ($bpCode >= 0))
         ||  (($bpCode <= 16) & ($bpCode >= 12))
         || (($bpCode <= 64) & ($bpCode >= 60))
         || (($bpCode <= 80) & ($bpCode >= 76))
         || (($bpCode <= 128) & ($bpCode >= 124))
         || (($bpCode <= 144) & ($bpCode >= 140))
         || (($bpCode <= 192) & ($bpCode >= 188))
         || (($bpCode <= 208) & ($bpCode >= 204))
          )
              {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
        }

        else if($binParamType == 73)
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $bpCode = hexdec($tempStr1);
          if
          (
          (($bpCode <= 2) & ($bpCode >= 0))
         ||  (($bpCode <= 16) & ($bpCode >= 12))
         || (($bpCode <= 32) & ($bpCode >= 28))
         || (($bpCode <= 48) & ($bpCode >= 44))
         || (($bpCode <= 128) & ($bpCode >= 124))
         || (($bpCode <= 144) & ($bpCode >= 140))
         || (($bpCode <= 160) & ($bpCode >= 156))
         || (($bpCode <= 176) & ($bpCode >= 172))
          )
              {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
        }

        else if($binParamType == 74)
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $bpCode = hexdec($tempStr1);
          if
          (
          (($bpCode <= 2) & ($bpCode >= 0))
         ||  (($bpCode <= 16) & ($bpCode >= 12))
         || (($bpCode <= 32) & ($bpCode >= 28))
         || (($bpCode <= 48) & ($bpCode >= 44))
         || (($bpCode <= 64) & ($bpCode >= 60))
         || (($bpCode <= 80) & ($bpCode >= 76))
         || (($bpCode <= 96) & ($bpCode >= 92))
         || (($bpCode <= 112) & ($bpCode >= 108))
          )
              {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
        }
        else if($binParamType == 21)//rotation bytes in word and for TCAS (with Basis)
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
          if($bpCode == $binParam['basis'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 22)//rotation bytes in word and for TCAS (with Basis)  (mask 63)  4,5,6 Bits
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 3;//decbin
          if($bpCode == $binParam['basis'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 23)//rotation bytes in word and for TCAS (with Basis)  (mask 511) 7,8,9 Bits
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 6;//decbin
          if($bpCode == $binParam['basis'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 24)//rotation bytes in word and for TCAS (with Basis)  (mask 4095) 10,11,12 Bits
        {
          $tempStr1 = substr ($codeValue, 0, 2);
          $tempStr2 = substr ($codeValue, 2, 2);
          $rotatedStr = $tempStr2 . $tempStr1;
          $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 9;//decbin
          if($bpCode == $binParam['basis'])
          {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
          }
        }
        else if($binParamType == 121)//rotation bytes any Bits and = Basis  (Frame = 0)
        {
          if(($apPhisicsByPrefixes['1'][0][2] == 0))
          {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if($bpCode == $binParam['basis'])
            {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
            }
            else
            {
              $algHeap[$binParam['code']] = 0;
            }
          }
          else
            {
              if(isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1))
              {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
            }
        }
        else if($binParamType == 122)//rotation bytes in word and for TCAS (with Basis)  (mask 63)  4,5,6 Bits (Frame = 0)
        {
          if(($apPhisicsByPrefixes['1'][0][2] == 0))
          {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 3;//decbin
            if($bpCode == $binParam['basis'])
            {
            $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
            array_push($phisicsBinaryParamsFrame, $param);
            }
            else
            {
              $algHeap[$binParam['code']] = 0;
            }
          }
          else
            {
              if(isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1))
              {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              }
            }
        } else if($binParamType == 123) {//rotation bytes in word and for TCAS (with Basis)  (mask 511) 7,8,9 Bits (Frame = 0)
          if (($apPhisicsByPrefixes['1'][0][2] == 0)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 6;//decbin
            if ($bpCode == $binParam['basis']) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
              if (isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1))
              {
                $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
                array_push($phisicsBinaryParamsFrame, $param);
              }
            }
        } else if($binParamType == 124) {//rotation bytes in word and for TCAS (with Basis)  (mask 4095) 10,11,12 Bits (Frame = 0)
          if (($apPhisicsByPrefixes['1'][0][2] == 0)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 9;//decbin
            if ($bpCode == $binParam['basis']) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
              if (isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1))
              {
                $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
                array_push($phisicsBinaryParamsFrame, $param);
              }
            }
        } else if($binParamType == 125) {//rotation bytes any Bits and = Basis  (Frame = 1)
          if (($apPhisicsByPrefixes['1'][0][2] == 1)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode == $binParam['basis']) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 126) {//rotation bytes any Bits and = Basis  (Frame = 2)
          if (($apPhisicsByPrefixes['1'][0][2] == 2)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode == $binParam['basis']) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if(isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 127) {//rotation bytes any Bits and = Basis  (Frame = 3)
          if (($apPhisicsByPrefixes['1'][0][2] == 3)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode == $binParam['basis']) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
                array_push($phisicsBinaryParamsFrame, $param);
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 27) {// frame = 0
          if (($apPhisicsByPrefixes['1'][0][2] == 0)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 271) {//rev 27
          if (($apPhisicsByPrefixes['1'][0][2] == 0)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if (!($bpCode > 0)) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 28)  {// frame = 1
          if (($apPhisicsByPrefixes['1'][0][2] == 1)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if(isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1))
            {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 281) { // inv 28
           if (($apPhisicsByPrefixes['1'][0][2] == 1)) {
               $tempStr1 = substr ($codeValue, 0, 2);
               $tempStr2 = substr ($codeValue, 2, 2);
               $rotatedStr = $tempStr2 . $tempStr1;
               $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
               if (!($bpCode > 0))  {
                 $param = array("frameNum" => $frameNum,
                     "time" => ($startTime + ($frameNum * $stepLength) +
                         ($stepLength / $channelFreq * $chInd)) * 1000,
                     "code" => $binParam['code']);
                 array_push($phisicsBinaryParamsFrame, $param);
                 $algHeap[$binParam['code']] = 1;
               } else {
                 $algHeap[$binParam['code']] = 0;
               }
             } else  {
               if (isset($algHeap[$binParam['code']]) &&
                 ($algHeap[$binParam['code']] == 1)
               ) {
                 $param = array("frameNum" => $frameNum,
                     "time" => ($startTime + ($frameNum * $stepLength) +
                         ($stepLength / $channelFreq * $chInd)) * 1000,
                     "code" => $binParam['code']);
                 array_push($phisicsBinaryParamsFrame, $param);
               }
             }
         } else if ($binParamType == 29)  {// frame = 2
          if (($apPhisicsByPrefixes['1'][0][2] == 2)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 291)  {// inv 29
          if (($apPhisicsByPrefixes['1'][0][2] == 2)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if (!($bpCode > 0)) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                    ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 30) { // frame = 3
          if (($apPhisicsByPrefixes['1'][0][2] == 3)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 31) { // frame = 1 or frame = 3
          if (($apPhisicsByPrefixes['1'][0][2] == 1) || ($apPhisicsByPrefixes['1'][0][2] == 3)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
                ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 301) { // inv 30
          if (($apPhisicsByPrefixes['1'][0][2] == 3)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if (!($bpCode > 0)) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if(isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 32) {// frame = 0 or frame = 2
          if (($apPhisicsByPrefixes['1'][0][2] == 0) || ($apPhisicsByPrefixes['1'][0][2] == 2)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if ($bpCode > 0) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 311) {//inv 31
          if (($apPhisicsByPrefixes['1'][0][2] == 1) || ($apPhisicsByPrefixes['1'][0][2] == 3)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if (!($bpCode > 0)) {
              $param = array("frameNum" => $frameNum,
                "time" => ($startTime + ($frameNum * $stepLength) +
                  ($stepLength / $channelFreq * $chInd)) * 1000,
                "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        } else if($binParamType == 321) { // inv 32
          if (($apPhisicsByPrefixes['1'][0][2] == 0) || ($apPhisicsByPrefixes['1'][0][2] == 2)) {
            $tempStr1 = substr ($codeValue, 0, 2);
            $tempStr2 = substr ($codeValue, 2, 2);
            $rotatedStr = $tempStr2 . $tempStr1;
            $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
            if (!($bpCode > 0)) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
              $algHeap[$binParam['code']] = 1;
            } else {
              $algHeap[$binParam['code']] = 0;
            }
          } else {
            if (isset($algHeap[$binParam['code']]) &&
              ($algHeap[$binParam['code']] == 1)
            ) {
              $param = array("frameNum" => $frameNum,
                  "time" => ($startTime + ($frameNum * $stepLength) +
                      ($stepLength / $channelFreq * $chInd)) * 1000,
                  "code" => $binParam['code']);
              array_push($phisicsBinaryParamsFrame, $param);
            }
          }
        }
      }
    }

    return $phisicsBinaryParamsFrame;
  }
}
