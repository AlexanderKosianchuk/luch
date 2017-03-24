<?php

namespace Model;

class FlightException
{

    public function CreateFlightExceptionTable($extFlightId, $extFlightTablesGuid)
    {
        $flightId = $extFlightId;

        $flightTablesGuid = $extFlightTablesGuid;
        $flightExTableName = $flightTablesGuid . "_ex";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `flights` SET exTableName = '".$flightExTableName."' WHERE id='".$flightId."';";
        $stmt = $link->prepare($query);
        $stmt->execute();

        $query = "CREATE TABLE `".$flightExTableName."` (`id` INT NOT NULL AUTO_INCREMENT, "
                . " `frameNum` INT,"
                . " `startTime` BIGINT,"
                . " `endFrameNum` INT,"
                . " `endTime` BIGINT,"
                . " `refParam` VARCHAR(255),"
                . " `code` VARCHAR(255),"
                . " `excAditionalInfo` TEXT,"
                . " `falseAlarm` BOOL DEFAULT 0,"
                . " `userComment` TEXT,"
                . " PRIMARY KEY (`id`))"
                . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

        $stmt = $link->prepare($query);
        $stmt->execute();

        $stmt->close();
        $c->Disconnect();

        unset($c);

        return $flightExTableName;
    }

    public function DropFlightExceptionTable($extFlightExTableName)
    {
        $flightExTableName = $extFlightExTableName;

        $stmt = true;
        if(($flightExTableName != null) && ($flightExTableName != ''))
        {
            $c = new DataBaseConnector;
            $link = $c->Connect();

            $query = "DROP TABLE IF EXISTS `".$flightExTableName."`;";
            $stmt = $link->prepare($query);
            $stmt->execute();

            $stmt->close();
            $c->Disconnect();

            unset($c);
        }

        return $stmt;
    }

    public function GetFlightExceptionTable($extExListTableName)
    {
        $exListTableName = $extExListTableName;

        $query = "SELECT * FROM `".$exListTableName."`;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $exList = array();
        while($row = $result->fetch_array())
        {
            $ex = array("code" => $row['code'],
                "text" => $row['text'],
                "refParam" => $row['refParam'],
                "minLength" => $row['minLength'],
                "alg" => $row['alg'],
                "comment" => $row['comment'],
                "algText" => $row['algText'],
                "visualization" => $row['visualization']);
            array_push($exList, $ex);
        }
        $result->free();
        $c->Disconnect();

        unset($c);

        return $exList;
    }

    public function GetFlightExceptionRefParams($extExListTableName)
    {
        $exListTableName = $extExListTableName;

        $query = "SELECT DISTINCT `refParam` FROM `".$exListTableName."`;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $excRefParamsList = array();
        while($row = $result->fetch_array())
        {
            $currList = explode(", ", $row['refParam']);

            for($i = 0; $i < count($currList); $i++)
            {
                $j = 0;
                $paramsCount = count($excRefParamsList);
                while($j < count($excRefParamsList) &&
                    $excRefParamsList[$j] != $currList[$i])
                {
                    $j++;
                }

                //if no matches found
                if($paramsCount == $j) {
                    array_push($excRefParamsList, $currList[$i]);
                }

            }
        }
        $result->free();
        $c->Disconnect();

        unset($c);

        return $excRefParamsList;
    }

    public function PerformProcessingByExceptions($extExList, $extFlightInfo, $extFlightExTableName, $extApTableName, $extBpTableName, $extStartCopyTime, $extStepLength)
    {
        $exList = $extExList;
        $flightInfo = $extFlightInfo;
        $flightExTableName = $extFlightExTableName;
        $apTableName = $extApTableName;
        $bpTableName = $extBpTableName;
        $startCopyTime =  $extStartCopyTime;
        $stepLength = $extStepLength;

        $eventsList = array();
        $c = new DataBaseConnector;
        $c2 = new DataBaseConnector;
        $link = $c->Connect();
        $link2 = $c2->Connect();

        $query = $exList['alg'];

        $query = str_replace("[ap]", $apTableName, $query);
        $query = str_replace("[bp]", $bpTableName, $query);
        $query = str_replace("[ex]", $flightExTableName, $query);

        foreach ($flightInfo as $flightInfoKey => $flightInfoVal)
        {
            $query = str_replace("[".$flightInfoKey."]", $flightInfoVal, $query);
        }

        $aditionalQueries = array();

        //check delimiter exist in string
        if(strpos($query,'#') !== false)
        {
            $query = explode("#", $query);
            $queryAlg = $query[0];

            if((count($query) - 1) % 2 == 0)
            {
                for($i = 1; $i < count($query); $i+=2)
                {
                    $aditionalQueries[] = array(
                        "aditionalInfoComment" => $query[$i],
                        "aditionalInfoAlg" => $query[$i + 1]);
                }
            }
            //else no aditional info
        } else {
            $queryAlg = $query;
        }


        if (!$link->multi_query($queryAlg)) {
            //err log
            error_log("Impossible to execute multiquery: (" .
                $queryAlg . ") " . $link->error);
        }

        do
        {
            if ($res = $link->store_result())
            {
                $resultArr = array();
                while($row = $res->fetch_array())
                {
                    //exception alg can return frameNum or frameNum and time
                    if(isset($row['time'])) {
                        $resultArr[] = array(
                            "frameNum" => $row['frameNum'],
                            "time" => $row['time']);
                    } else {
                        $time = ($startCopyTime + $row['frameNum'] * $stepLength) * 1000; //1000 to convert in microsec
                        $resultArr[] = array(
                            "frameNum" => $row['frameNum'],
                            "time" => $time);
                    }
                }

                //reorganize arr to simplify inserting
                $normalizedResultArr = array();
                if(count($resultArr) > 0) {
                    $endFrameNum = $resultArr[0]['frameNum'] + 1;
                    $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000; //1000 to convert in microsec
                    $normalizedResultArr[] = array(
                            "frameNum" => $resultArr[0]["frameNum"],
                            "startTime" => $resultArr[0]["time"],
                            "endFrameNum" => $endFrameNum,
                            "endTime" => $endTime);

                    for($j = 1; $j < count($resultArr); $j++)
                    {
                        $prevFrameNum = $resultArr[$j - 1]['frameNum'];
                        $curFrameNum = $resultArr[$j]['frameNum'];

                        if(($curFrameNum - $prevFrameNum) > 1) {
                            $endFrameNum =  $curFrameNum + 1;
                            $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000;
                            $normalizedResultArr[] = array(
                                "frameNum" => $curFrameNum,
                                "startTime" => $resultArr[$j]["time"],
                                "endFrameNum" => $endFrameNum,
                                "endTime" => $endTime);
                        } else {
                            $endTime = ($startCopyTime + $curFrameNum * $stepLength) * 1000;

                            $normalizedResultArr[count($normalizedResultArr) - 1]["endFrameNum"] = $curFrameNum;
                            $normalizedResultArr[count($normalizedResultArr) - 1]["endTime"] = $endTime;
                        }
                    }
                }

                //remove events less minLength
                $checkedNormalizedResultArr = array();
                if($exList["minLength"] != 0)
                {
                    for($j = 0; $j < count($normalizedResultArr); $j++)
                    {
                        if(($normalizedResultArr[$j]["endTime"] - $normalizedResultArr[$j]["startTime"]) > $exList["minLength"])
                        {
                            $checkedNormalizedResultArr[] = $normalizedResultArr[$j];
                        }
                    }
                    $normalizedResultArr = $checkedNormalizedResultArr;
                }

                for($j = 0; $j < count($normalizedResultArr); $j++)
                {
                    $aditionalInfoStr = "";

                    for($k = 0; $k < count($aditionalQueries); $k++)
                    {
                        $aditionalInfoStr .= $aditionalQueries[$k]["aditionalInfoComment"] . " ";
                        $query = $aditionalQueries[$k]["aditionalInfoAlg"];

                        //in aditionalQueries we can use this variables
                        //startFrameNum
                        //endFrameNum
                        //startTime
                        //endTime

                        $query = str_replace("[startFrameNum]", $normalizedResultArr[$j]['frameNum'], $query);
                        $query = str_replace("[endFrameNum]", $normalizedResultArr[$j]['endFrameNum'], $query);
                        $query = str_replace("[startTime]", $normalizedResultArr[$j]['startTime'], $query);
                        $query = str_replace("[endTime]", $normalizedResultArr[$j]['endTime'], $query);

                        $c3 = new DataBaseConnector;
                        $link = $c3->Connect();
                        $result = $link->query($query);

                        $excRefParamsList = array();
                        $row = $result->fetch_array();

                        $aditionalInfoStr .= $row[0] . "; ";
                        $c3->Disconnect();
                        unset($c3);
                    }

                    $query = "INSERT INTO `".$flightExTableName."` (`frameNum`,".
                            "`startTime`, ".
                            "`endFrameNum`, ".
                            "`endTime`, ".
                            "`refParam`, ".
                            "`code`, ".
                            "`excAditionalInfo`) ".
                            "VALUES (".$normalizedResultArr[$j]['frameNum'].",".
                            $normalizedResultArr[$j]['startTime'].",".
                            $normalizedResultArr[$j]['endFrameNum'].",".
                            $normalizedResultArr[$j]['endTime'].",'".
                            $exList['refParam']."','".
                            $exList['code']."','".
                            $aditionalInfoStr."');";
                    $stmt = $link2->prepare($query);
                    $stmt->execute();
                }

                $res->free();
            }
        } while ($link->more_results() && $link->next_result());

        $c->Disconnect();
        $c2->Disconnect();

        unset($c);
        unset($c2);

        return $eventsList;
    }

    public function GetExcApByCode($extExcTableName, $extRefParam,
        $extApTableName, $extExcListTableName)
    {
        $excTableName = $extExcTableName;
        $apTableName = $extApTableName;
        $excListTableName = $extExcListTableName;
        $refParam = $extRefParam;
        $excList = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `startTime`, `endTime`, `code`, `refParam`, `excAditionalInfo`, `userComment` ".
         "FROM `".$excTableName."` WHERE `refParam` = ? AND `falseAlarm` != '1';";
        //$query = "SELECT `frameNum`, `code` FROM `".$excTableName."` WHERE `refParam` = ?;";
        $stmt = $link->prepare($query);
        $stmt->bind_param('s', $refParam);
        $stmt->execute();
        $stmt->bind_result($frameNum, $startTime, $endTime, $code, $refParam, $aditionalInfo, $userComment);

        while ($stmt->fetch())
        {
            $link3 = $c->Connect();
            $query = "SELECT `status`, `text`, `comment`, `algText`, `visualization` FROM `".$excListTableName."` WHERE `code` = ?;";
            $stmt3 = $link3->prepare($query);
            $stmt3->bind_param('s', $code);
            $stmt3->execute();
            $stmt3->bind_result($excStatus, $excText, $excComment, $algText, $visualization);
            $stmt3->fetch();

            $comment = $this->UnicodeConv($excText) . "; "; //Because of cyrillic string

            if(($excComment != "") && ($excComment != " ") && ($excComment != null))
            {
                $comment .= $this->UnicodeConv($excComment) . "; ";
            }

            if(($excStatus != "") && ($excStatus != " ") && ($excStatus != null))
            {
                $comment .= "Status: " . $excStatus . "; ";
            }

            if(($algText != "") && ($algText != " ") && ($algText != null))
            {
                $comment .= $this->UnicodeConv($algText) . "; ";
            }

            if(($userComment != "") && ($userComment != " ") && ($userComment != null))
            {
                $comment .= $this->UnicodeConv($userComment) . "; ";
            }

            if(($aditionalInfo != "") && ($aditionalInfo != " ") && ($aditionalInfo != null))
            {
                $comment .= $this->UnicodeConv($aditionalInfo) . " ";
            }

            $link2 = $c->Connect();
            $query = "SELECT `".$refParam."` FROM `".$apTableName."` WHERE `frameNum` = ? LIMIT 1;";
            $stmt2 = $link2->prepare($query);
            $stmt2->bind_param('i', $frameNum);
            $stmt2->execute();
            $stmt2->bind_result($value);
            while ($stmt2->fetch())
            {
                $exc = array($startTime, $endTime, $code, $value, $comment, $visualization);
                $excList[] = $exc;
            }
            $stmt2->close();

            $stmt3->close();
        }

        $stmt->close();
        unset($c);

        return $excList;
    }

    public function GetExcBpByCode($extExcTableName, $extRefParam,
            $extStepLength, $extStartCopyTime, $extExcListTableName)
    {
        $excTableName = $extExcTableName;
        $excListTableName = $extExcListTableName;

        $stepLength = $extStepLength;
        $stepTime = $stepLength * 1000;
        $startTime = $extStartCopyTime * 1000;
        $refParam = $extRefParam;

        $excList = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `code`, `startTime`, `endTime`, `excAditionalInfo`, `userComment` ".
            "FROM `".$excTableName."` WHERE `refParam` = ? AND `falseAlarm` != '1';";
        //$query = "SELECT `frameNum`, `code` FROM `".$excTableName."` WHERE `refParam` = ?;";
        $stmt = $link->prepare($query);
        $stmt->bind_param('s', $refParam);
        $stmt->execute();
        $stmt->bind_result($frameNum, $code, $startTime, $endTime, $aditionalInfo, $userComment);

        while ($stmt->fetch())
        {
            $link3 = $c->Connect();
            $query = "SELECT `status`, `text`, `comment`, `algText`, `visualization` FROM `".$excListTableName."` WHERE `code` = ?;";
            $stmt3 = $link3->prepare($query);
            $stmt3->bind_param('s', $code);
            $stmt3->execute();
            $stmt3->bind_result($excStatus, $excText, $excComment, $algText, $visualization);
            $stmt3->fetch();

            $comment = $this->UnicodeConv($excText) . "; "; //Because of cyrillic string

            if(($excComment != "") && ($excComment != " ") && ($excComment != null))
            {
                $comment .= $this->UnicodeConv($excComment) . "; ";
            }

            if(($excStatus != "") && ($excStatus != " ") && ($excStatus != null))
            {
                $comment .= "Status: " . $excStatus . "; ";
            }

            if(($algText != "") && ($algText != " ") && ($algText != null))
            {
                $comment .= $this->UnicodeConv($algText) . "; ";
            }

            if(($userComment != "") && ($userComment != " ") && ($userComment != null))
            {
                $comment .= $this->UnicodeConv($userComment) . "; ";
            }

            if(($aditionalInfo != "") && ($aditionalInfo != " ") && ($aditionalInfo != null))
            {
                $comment .= $this->UnicodeConv($aditionalInfo) . " ";
            }

            $exc = array($startTime, $endTime, $code, 1,
                    $comment, $visualization);
            $excList[] = $exc;

            $stmt3->close();
        }

        $stmt->close();
        unset($c);

        return $excList;
    }

    private function UnicodeConv($originalString)
    {
        // The four \\\\ in the pattern here are necessary to match \u in the original string
        $replacedString = preg_replace("/\\\\u(\w{4})/", "&#$1;", $originalString);
        $unicodeString = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
        return $unicodeString;
    }

    public function GetFlightEventsList($extExcEventsTableName)
    {
        $excEventsTableName = $extExcEventsTableName;
        $query = "SELECT DISTINCT * FROM `".$excEventsTableName."` ORDER BY `frameNum`;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $excEventsList = array();
        while($row = $result->fetch_array())
        {
            $ex = array("id" => $row['id'],
                "frameNum" => $row['frameNum'],
                "startTime" => $row['startTime'],
                "endFrameNum" => $row['endFrameNum'],
                "endTime" => $row['endTime'],
                "refParam" => $row['refParam'],
                "code" => $row['code'],
                "excAditionalInfo" => $row['excAditionalInfo'],
                "falseAlarm" => $row['falseAlarm'],
                "userComment" => $row['userComment']);
            array_push($excEventsList, $ex);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $excEventsList;
    }

    public function GetFlightEventsParamsList($extExcEventsTableName)
    {
        $excEventsTableName = $extExcEventsTableName;
        $query = "SELECT DISTINCT `refParam` FROM `".$excEventsTableName."` WHERE 1;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $excEventsCodesList = array();
        while($row = $result->fetch_array()) {
            array_push($excEventsCodesList, $row['refParam']);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $excEventsCodesList;
    }

    public function GetExcInfo($extExcTableName, $extRefParam, $extCode)
    {
        $refParam = $extRefParam;
        $excTableName = $extExcTableName;
        $code = $extCode;

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $excInfo = array();

        $query = "SELECT `text`, `code`, `status`, `comment`, `algText` FROM `".$excTableName."` WHERE (`refParam` = ?) AND (`code` = ?);";

        $stmt = $link->prepare($query);
        $stmt->bind_param('ss', $refParam, $code);

        $stmt->execute();
        $stmt->bind_result($text, $code, $status, $comment, $algText);
        while ($stmt->fetch())
        {
            $excInfo = array("code" => $code,
                "text" => $text,
                "status" => $status,
                "comment" => $comment,
                "algText" => $algText);
        }

        $stmt->close();
        unset($c);

        return $excInfo;
    }

    public function UpdateFalseAlarmState($extExcTableName, $extExcId, $extFalseAlarmState)
    {
        $excTableName = $extExcTableName;
        $excId = $extExcId;
        $falseAlarmState = $extFalseAlarmState;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$excTableName."` SET `falseAlarm` = '".$falseAlarmState."' WHERE id='".$excId."';";

        $stmt = $link->prepare($query);
        $stmt->execute();

        $stmt->close();
        $c->Disconnect();

        unset($c);

    }

    public function UpdateUserComment($excTableName, $excId, $userComment)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$excTableName."` SET `userComment` = '".$userComment."' WHERE id='".$excId."';";
        $stmt = $link->prepare($query);
        $stmt->execute();

        $stmt->close();
        $c->Disconnect();

        unset($c);
    }
}
