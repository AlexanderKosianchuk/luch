<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Channel
{
    private function GetFlightChannel($extTableName, $extCode, $extCodeTablePrefix,
            $extStartFrame, $extEndFrame, $extDivider = 1)
    {
        $tableName = $extTableName;
        $code = $extCode;
        $prefix = $extCodeTablePrefix;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $divider = $extDivider;

        if($divider == 1)
        {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
            ((`frameNum` >= ".$startFrame.") AND
            (`frameNum` < ".$endFrame."))
            ORDER BY `time` ASC";
        }
        else
        {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
            ((`frameNum` >= ".$startFrame.") AND
            (`frameNum` < ".$endFrame.")) AND
            (`frameNum` % ".$divider." = 0)
            GROUP BY frameNum
            ORDER BY `time` ASC";
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);

        $pointPairList = array();
        while($row = $result->fetch_array())
        {
            $point = array($row['time'], $row[$code]);
            $pointPairList[] = $point;
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $pointPairList;
    }

    public function GetFlightParam($extApTableName,
            $extSeriesCountDivider,    $extStartFrame, $extEndFrame,
            $extCode, $extCodeTablePrefix, $extCodeFreq)
    {
        $apTableName = $extApTableName;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $seriesDivider = $extSeriesCountDivider;
        $code = $extCode;
        $prefix = $extCodeTablePrefix;
        $codeFreq = $extCodeFreq;

        $framesCount = $endFrame - $startFrame;
        $pointCount = $framesCount * $codeFreq * $seriesDivider;

        $pointPairList = array();

        if($pointCount < POINT_MAX_COUNT)
        {
            $pointPairList = $this->GetFlightChannel($apTableName, $code, $prefix,
                    $startFrame, $endFrame);
        }
        else
        {
            //modifying divider to perform compression
            $divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
            $pointPairList = $this->GetFlightChannel($apTableName, $code, $prefix,
                    $startFrame, $endFrame, $divider);
        }
        return $pointPairList;
    }

    public function GetFlightParamValue($extApTableName,
            $extFrame, $extCode, $extCodeTablePrefix)
    {
        $apTableName = $extApTableName;
        $frame = $extFrame;
        $code = $extCode;
        $prefix = $extCodeTablePrefix;

        $pointPairList = array();

        $query = "SELECT `time`, `".$code."` FROM `".$apTableName."_".$prefix."` WHERE
        `frameNum` = ".$frame."
        ORDER BY `time` ASC";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);

        $row = $result->fetch_array();

        $point = array($row['time'], $row[$code]);

        $result->free();
        $c->Disconnect();

        unset($c);

        return $point;
    }

    private function GetFlightChannelWithExactSection($extTableName, $extCode, $extCodeTablePrefix,
            $extStartFrame, $extEndFrame, $extSeriesDivider, $extTotalFramesCount, $extDivider, $extBrute)
    {
        $tableName = $extTableName;
        $code = $extCode;
        $prefix = $extCodeTablePrefix;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $divider = $extDivider;
        $seriesDivider = $extSeriesDivider;
        $totalFramesCount = $extTotalFramesCount;
        $brute = $extBrute;

        $pointPairList = array();

        if($brute == false)
        {
            $divider = ceil($totalFramesCount * $seriesDivider / POINT_MAX_COUNT);

            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` < ".$startFrame.") AND
                (`frameNum` % ".$divider." = 0))
                ORDER BY `time` ASC";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();

            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` >= ".$startFrame.") AND
                (`frameNum` <= ".$endFrame."))
                ORDER BY `time` ASC";

            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();

            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` > ".$endFrame.") AND
                (`frameNum` % ".$divider." = 0))
                ORDER BY `time` ASC";
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();
            $c->Disconnect();
            unset($c);
        }
        else
        {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
            (`frameNum` % ".$divider." = 0)
            ORDER BY `time` ASC";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }

            $result->free();
            $c->Disconnect();

            unset($c);
        }

        return $pointPairList;
    }

    public function GetFlightParamWithExactSection($extApTableName,
            $extSeriesCountDivider,    $extStartFrame, $extEndFrame,
            $extCode, $extCodeTablePrefix, $extCodeFreq)
    {
        $apTableName = $extApTableName;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $seriesDivider = $extSeriesCountDivider;
        $code = $extCode;
        $prefix = $extCodeTablePrefix;
        $codeFreq = $extCodeFreq;
        $totalFramesCount = $extEndFrame - $extStartFrame;

        $framesCount = $endFrame - $startFrame;
        $pointCount = $framesCount * $codeFreq * $seriesDivider;

        $pointPairList = array();

        //if not much points betweet start and end frames
        //we should build this segment very exact
        //else use general compression by divider
        if($pointCount < POINT_MAX_COUNT)
        {
            $divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
            $pointPairList = $this->GetFlightChannelWithExactSection($apTableName, $code, $prefix,
                    $startFrame, $endFrame, $seriesDivider, $totalFramesCount, $divider, false);
        }
        else
        {
            //modifying divider to perform compression
            $divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
            $pointPairList = $this->GetFlightChannelWithExactSection($apTableName, $code, $prefix,
                    $startFrame, $endFrame, $seriesDivider, $totalFramesCount, $divider, true);
        }
        return $pointPairList;
    }

    private function GetBinaryChannel($extTableName, $extCode, $extStepLength, $extFreq)
    {
        $tableName = $extTableName;
        $code = $extCode;
        $stepLength = $extStepLength;
        $freq = $extFreq;
        $stepMicroTime = $stepLength / $freq * 1000;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE " .
            "`code` = '".$code."' " .
            "ORDER BY `time` ASC;";

        $result = $link->query($query);

        $pointPairList = array();
        $pointPairList2 = array();

        //if exists though one row in table
        if($row = $result->fetch_array())
        {
            $point = array('null','null');
            $pointPairList[] = $point;
            $currTime = $row['time'];

            $point = array($currTime, 1);
            $pointPairList[] = $point;
            $previousTime = $currTime;

            $pointPairList2[] = $point;

            //our task is to find first appearence of bp, write it, path to the last
            //appearence, also write it, put null and than search next appearance
            while($row = $result->fetch_array())
            {
                $currTime = $row['time'];

                $pointPairList2[] = array($currTime, 1);
                if($previousTime == $currTime - $stepMicroTime)
                {
                    if(count($pointPairList) > 2)
                    {
                        if($pointPairList[count($pointPairList) - 3][1] == 'null')
                        {
                            $point = array($currTime, 1);
                            $pointPairList[count($pointPairList) - 1] = $point;
                            $previousTime = $currTime;
                        }
                        else
                        {
                            $point = array($currTime, 1);
                            $pointPairList[] = $point;
                            $previousTime = $currTime;
                        }
                    }
                    else
                    {
                        $point = array($currTime, 1);
                        $pointPairList[] = $point;
                        $previousTime = $currTime;
                    }
                }
                else
                {
                    $point = array('null','null');
                    $pointPairList[] = $point;
                    $point = array($currTime, 1);
                    $pointPairList[] = $point;
                    $previousTime = $currTime;
                }


            }

            $result->free();
        }
        else
        {
            $point = array('null','null');
            $pointPairList[] = $point;
        }
        $c->Disconnect();

        unset($c);


        return $pointPairList;
    }

    public function GetBinaryParam($extTableName, $extCode, $extStepLength, $extFreq)
    {
        $bpTableName = $extTableName;
        $stepLength = $extStepLength;
        $code = $extCode;
        $freq = $extFreq;

        $pointPairList = array();

        $pointPairList = $this->GetBinaryChannel($bpTableName, $code, $stepLength, $freq);

        $tempString = json_encode($pointPairList);
        //in bin params point equal to null we had put ["null","null"]
        $searchSubstr = '["null","null"]';
        //$searchSubstr = 'null';
        $transmitStr = str_replace($searchSubstr, 'null', $tempString);
        //var_dump($transmitStr);


        return json_decode($transmitStr);
        //return $pointPairList;
    }

    public function GetNormalizedApParam($extApTableName,
            $extStepDivider, $extCode, $extFreq, $extPefix,
            $extStartFrame, $extEndFrame)
    {
        $tableName = $extApTableName . "_" . $extPefix;
        $stepDivider = $extStepDivider;
        $code = $extCode;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $steps = $extFreq;
        $duplication = $stepDivider / $steps;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `".$code."` FROM `".$tableName."` WHERE
            `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame."
            ORDER BY `frameNum`ASC;";

        $result = $link->query($query);

        $normArr = array();
        while($row = $result->fetch_array())
        {
            array_push($normArr, $row[$code]);
            for($i = 1; $i < $duplication; $i++)
            {
                array_push($normArr, $row[$code]);
            }
        }

        $result->free();
        $c->Disconnect();

        return $normArr;
    }

    public function GetNormalizedBpParam($extBpTableName,
            $extStepDivider, $extCode, $extFreq, $extPefix,
            $extStartFrame, $extEndFrame)
    {
        $tableName = $extBpTableName . "_".$extPefix;
        $stepDivider = $extStepDivider;
        $code = $extCode;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $steps = $extFreq;
        $duplication = $stepDivider / $steps;
        $totalRows = ($endFrame - $startFrame) * $stepDivider;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE `code` = '" . $code . "' ".
            "AND `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame. " ".
            "ORDER BY `time` ASC;";

        $result = $link->query($query);

        $normArr = array();
        for($i = 0; $i < $totalRows; $i++)
        {
            $normArr[$i] = 0;
        }

        while($row = $result->fetch_array())
        {
            $position = ($row['frameNum'] - $startFrame) * $stepDivider;
            //error_log($position);
            $normArr[$position] = 1;
            for($i = 1; $i < $stepDivider; $i++)
            {
                $position = ($row['frameNum'] - $startFrame) * $stepDivider + $i;
                $normArr[$position] = 1;
            }
        }

        $result->free();
        $c->Disconnect();

        return $normArr;
    }

    public function NormalizeTime($extStepDivider, $extStepLength,
            $extTotalFrameNum, $extStartCopyTime, $extStartFrame, $extEndFrame)
    {
        $stepLength = $extStepLength;
        $stepDivider = $extStepDivider;
        $totalFrameNum = $extTotalFrameNum;
        $startCopyTime = $extStartCopyTime;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        //date_default_timezone_set('Europe/Kiev');
        $stepMicroTime = round($stepLength * 1000 / $stepDivider, 0);

        $normTime = array();
        for($i = $startFrame; $i < $endFrame; $i++)
        {
            $microTime = 0;
            $dateInterval = $i * $stepLength;
            $currTime = $startCopyTime + $dateInterval;
            array_push($normTime, date("H:i:s", $currTime). "." . $microTime);
            for($j = 1; $j < $stepDivider; $j++)
            {
                $microTime = $j * $stepMicroTime;
                $dateInterval = $i * $stepLength;
                $currTime = $startCopyTime + $dateInterval;
                array_push($normTime, date("H:i:s", $currTime) . "." . $microTime);
            }
        }
        return $normTime;
    }

    public function GetParamMinMax($extApTableName, $extParamCode)
    {
        $apTableName = $extApTableName;
        $paramCode = $extParamCode;

        $minMax = array();

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT MIN(`".$paramCode."`), MAX(`".$paramCode."`) FROM `".$apTableName."` WHERE 1;";
        //error_log($query);
        $result = $link->query($query);

        $row = $result->fetch_array();
        $minMax['min'] = $row["MIN(`".$paramCode."`)"];
        $minMax['max'] = $row["MAX(`".$paramCode."`)"];

        $result->free();
        $c->Disconnect();

        return $minMax;

    }
}
