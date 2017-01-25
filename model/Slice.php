<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Slice
{
    //================================================================
    //Slices
    //================================================================

    public function CreateSliceTable()
    {
        $query = "SHOW TABLES LIKE 'slices';";
        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `slices` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `code` VARCHAR(255),
                `name` VARCHAR(255),
                `creationTime` BIGINT(20),
                `lastModifyTime` BIGINT(20),
                `sliceTableName` VARCHAR(255),
                `etalonTableName` VARCHAR(255),
                `author` VARCHAR(200) DEFAULT ' ',
                PRIMARY KEY (`id`));";

            $stmt = $link->prepare($query);
            if (!$stmt->execute()) {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }
        unset($c);
    }

    public function CreateSlice($extName, $extCode, $extAuthor)
    {
        $name = $extName;
        $code = $extCode;
        $author = $extAuthor;
        $creationTime = time();

        $tableName = uniqid();
        $slTableName = "_".$tableName ."_cl";

        $sliceTypeInfo = $this->GetSliceTypeInfo($code);

        $paramNames = array();

        if($sliceTypeInfo['children'] != '')
        {
            $childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
            $childCodesArray = array_map('trim', $childCodesArray);
            $childParams = '';

            for($j = 0; $j < count($childCodesArray); $j++)
            {
                $childCode = $childCodesArray[$j];
                $childSliceTypeInfo = $this->GetSliceTypeInfo($childCode);

                $sliceTypeInfo = $childSliceTypeInfo;
                $sliceAlgAp = trim($sliceTypeInfo['algAp']);
                $sliceAlgApArray = (array)explode("#", $sliceAlgAp);

                $bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
                $bpParamNamesArray = array_filter($bpParamNamesArray);
                $bpParamNamesArray = array_map('trim', $bpParamNamesArray);

                $apParamNamesArray = array();
                foreach($sliceAlgApArray as $apAlg)
                {
                    $paramCode = $this->GetInnerSubstring($apAlg);
                    //because only one substr element required
                    $paramCode = $paramCode[0];
                    $apParamNamesArray[] = $paramCode;
                }

                $paramNames = array_merge($paramNames, $apParamNamesArray);
                $paramNames = array_merge($paramNames, $bpParamNamesArray);
            }

            $paramNames = array_map('trim', $paramNames);
            $paramNames = array_unique($paramNames);
            $paramNames = array_filter($paramNames);
            //to remove unused indexes
            $paramNames = implode(',', $paramNames);
            $paramNames = explode(',', $paramNames);
        }
        else
        {
            $sliceAlgAp = trim($sliceTypeInfo['algAp']);
            $sliceAlgApArray = (array)explode("#", $sliceAlgAp);

            $bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
            $bpParamNamesArray = array_filter($bpParamNamesArray);
            $bpParamNamesArray = array_map('trim', $bpParamNamesArray);

            $apParamNamesArray = array();
            foreach($sliceAlgApArray as $apAlg)
            {
                $paramCode = $this->GetInnerSubstring($apAlg);
                //because only one substr element required
                $paramCode = $paramCode[0];
                $apParamNamesArray[] = $paramCode;
            }

            $paramNames = array_merge($paramNames, $apParamNamesArray);
            $paramNames = array_merge($paramNames, $bpParamNamesArray);
        }

        $query = "CREATE TABLE `".$slTableName."` (`id` BIGINT NOT NULL AUTO_INCREMENT,
        `flightId` MEDIUMINT,
        `engineSerial`  VARCHAR(255),
        `sliceCode` VARCHAR(255),
        `frameNum` MEDIUMINT";
        for($i = 0; $i < count($paramNames); $i++)
        {
            $query .= ", `" . trim($paramNames[$i]) . "` FLOAT(7,2)";
        }
        $query .=  ", PRIMARY KEY (`id`));";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);
        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }

        $sliceInfo['paramNames'] = $paramNames;

        $query = "INSERT INTO `slices` (`code`, `name`, `creationTime`, `lastModifyTime`, `sliceTableName`, `author`) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param("ssiiss", $code, $name, $creationTime, $creationTime, $slTableName, $author);

        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }

        $query = "SELECT LAST_INSERT_ID();";
        $result = $link->query($query);
        $row = $result->fetch_array();
        $sliceInfo['id'] = $row["LAST_INSERT_ID()"]; // last inserted id

        $stmt->close();
        $c->Disconnect();
        unset($c);
        return $sliceInfo;
    }

    public function CreateTmpSlice($extName, $extCode)
    {
        $name = $extName;
        $code = $extCode;
        $creationTime = time();

        $tableName = uniqid();
        $slTableName = "_".$tableName ."_cl_tmp";

        $sliceTypeInfo = $this->GetSliceTypeInfo($code);

        $paramNames = array();

        if($sliceTypeInfo['children'] != '')
        {
            $childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
            $childCodesArray = array_map('trim', $childCodesArray);
            $childParams = '';

            for($j = 0; $j < count($childCodesArray); $j++)
            {
                $childCode = $childCodesArray[$j];
                $childSliceTypeInfo = $this->GetSliceTypeInfo($childCode);

                $sliceTypeInfo = $childSliceTypeInfo;
                $sliceAlgAp = trim($sliceTypeInfo['algAp']);
                $sliceAlgApArray = (array)explode("#", $sliceAlgAp);

                $bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
                $bpParamNamesArray = array_filter($bpParamNamesArray);
                $bpParamNamesArray = array_map('trim', $bpParamNamesArray);

                $apParamNamesArray = array();
                foreach($sliceAlgApArray as $apAlg)
                {
                    $paramCode = $this->GetInnerSubstring($apAlg);
                    //because only one substr element required
                    $paramCode = $paramCode[0];
                    $apParamNamesArray[] = $paramCode;
                }

                $paramNames = array_merge($paramNames, $apParamNamesArray);
                $paramNames = array_merge($paramNames, $bpParamNamesArray);
            }

            $paramNames = array_map('trim', $paramNames);
            $paramNames = array_unique($paramNames);
            $paramNames = array_filter($paramNames);
            //to remove unused indexes
            $paramNames = implode(',', $paramNames);
            $paramNames = explode(',', $paramNames);
        }
        else
        {
            $sliceAlgAp = trim($sliceTypeInfo['algAp']);
            $sliceAlgApArray = (array)explode("#", $sliceAlgAp);

            $bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
            $bpParamNamesArray = array_filter($bpParamNamesArray);
            $bpParamNamesArray = array_map('trim', $bpParamNamesArray);

            $apParamNamesArray = array();
            foreach($sliceAlgApArray as $apAlg)
            {
                $paramCode = $this->GetInnerSubstring($apAlg);
                //because only one substr element required
                $paramCode = $paramCode[0];
                $apParamNamesArray[] = $paramCode;
            }

            $paramNames = array_merge($paramNames, $apParamNamesArray);
            $paramNames = array_merge($paramNames, $bpParamNamesArray);
        }

        $query = "CREATE TABLE `".$slTableName."` (`id` BIGINT NOT NULL AUTO_INCREMENT,
        `flightId` MEDIUMINT,
        `engineSerial`  VARCHAR(255),
        `sliceCode` VARCHAR(255),
        `frameNum` MEDIUMINT";
        for($i = 0; $i < count($paramNames); $i++)
        {
            $query .= ", `" . trim($paramNames[$i]) . "` FLOAT(7,2)";
        }
        $query .=  ", PRIMARY KEY (`id`));";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);
        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }

        $sliceInfo['paramNames'] = $paramNames;
        $sliceInfo['tableName'] = $slTableName;

        $c->Disconnect();
        unset($c);
        return $sliceInfo;
    }

    public function GetSliceList($extAvailableSlicesIds)
    {
        $availableSlicesIds = $extAvailableSlicesIds;

        $sliceList = array();
        if(count($availableSlicesIds) > 0)
        {
            $inString = "";
            foreach($availableSlicesIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $c = new DataBaseConnector();
            $link = $c->Connect();

            $result = $link->query("SELECT * FROM `slices` WHERE `id` IN (".$inString.") ORDER BY `id`;");

            while($row = $result->fetch_array())
            {
                $sliceInfo = $this->GetSliceInfo($row['id']);
                array_push($sliceList, $sliceInfo);
            }

            $c->Disconnect();
            unset($c);
        }

        return $sliceList;
    }

    public function GetSliceInfo($extSliceId)
    {
        $sliceId = $extSliceId;
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT * FROM `slices` WHERE `id`=".$sliceId." LIMIT 1");

        $row = $result->fetch_array();

        $sliceInfo = array("id"=>$row['id'],
                "code"=>$row['code'],
                "name"=>$row['name'],
                "creationTime"=>date('H:i:s Y-m-d', $row['creationTime']),
                "lastModifyTime"=>date('H:i:s Y-m-d', $row['lastModifyTime']),
                "sliceTableName"=>$row['sliceTableName'],
                "etalonTableName"=>$row['etalonTableName'],
                "author"=>$row['author']);

        $c->Disconnect();
        unset($c);

        return $sliceInfo;
    }

    public function UpdateSliceTime($extSliceId)
    {
        $sliceId = $extSliceId;
        $c = new DataBaseConnector();
        $link = $c->Connect();
        $currTime = time();

        $query = "UPDATE `slices` SET `lastModifyTime`=".$currTime."  WHERE `id`=".$sliceId.";";
        $stmt = $link->prepare($query);
        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function GetSliceTypesList()
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT * FROM `slicetypes`");

        $sliceTypeList = array();
        while($row = $result->fetch_array())
        {
            $sliceTypeInfo = $this->GetSliceTypeInfo($row['code']);
            array_push($sliceTypeList, $sliceTypeInfo);

        }

        $c->Disconnect();
        unset($c);

        return $sliceTypeList;
    }

    public function GetSliceTypeInfo($extSliceTypeCode)
    {
        $sliceTypeCode = $extSliceTypeCode;

        $query = "SELECT * FROM `slicetypes` WHERE `code`='".$sliceTypeCode."' LIMIT 1;";
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query($query);
        $row = $result->fetch_array();
        $sliceInfo = array("id"=>$row['id'],
                "code"=>$row['code'],
                "children"=>$row['children'],
                "bruType"=>$row['bruType'],
                "bpParamNames"=>$row['bpParamNames'],
                "algAp"=>$row['algAp'],
                "algBp"=>$row['algBp']);

        $c->Disconnect();
        unset($c);

        return $sliceInfo;
    }

    public function GetSliceEngineSlicesFlightCountPairs($extSliceTableName, $extSliceId)
    {
        $sliceTableName =$extSliceTableName;
        $sliceId = $extSliceId;

        $slicePairs = array();

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT DISTINCT `engineSerial` FROM `".$sliceTableName."` WHERE 1;";
        $result = $link->query($query);
        while($row = $result->fetch_array())
        {
            $query = "SELECT DISTINCT `sliceCode` FROM `".$sliceTableName."` WHERE `engineSerial` = '".$row['engineSerial']."';";
            $result2 = $link->query($query);
            while($row2 = $result2->fetch_array())
            {
                $flightCount = 0;
                $query = "SELECT DISTINCT `flightId` FROM `".$sliceTableName."` WHERE `engineSerial` = '".$row['engineSerial']."' AND `sliceCode` = '".$row2['sliceCode']."';";
                $result3 = $link->query($query);
                while($row3 = $result3->fetch_array())
                {
                    $flightCount++;
                }

                $curPair = array("engineSerial" => $row['engineSerial'], "sliceCode" => $row2['sliceCode'], "flightCount" => $flightCount);
                array_push($slicePairs, $curPair);
            }
        }

        $c->Disconnect();
        unset($c);
        return $slicePairs;
    }

    public function DeleteSlice($extSliceId)
    {
        $sliceId = $extSliceId;

        $sliceInfo = $this->GetSliceInfo($sliceId);
        $sliceTableName = $sliceInfo['sliceTableName'];

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `slices` WHERE id=".$sliceId.";";
        $stmt = $link->prepare($query);
        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }
        $stmt->close();

        $query = "DROP TABLE `". $sliceTableName ."`;";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function DeleteTmpSlice($extTmpSliceTableName)
    {
        $table = $extTmpSliceTableName;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DROP TABLE `". $table ."`;";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function FormSliceData($extFlightId, $extEngineSerial, $extSliceCode,
            $extSliceAlgApArrayPrepared, $extSliceAlgBpArrayPrepared,
            $extApParamNamesArray, $extBpParamNamesArray)
    {
        $flightId = $extFlightId;
        $engineSerial = $extEngineSerial;
        $sliceCode = $extSliceCode;
        $sliceAlgApArray = $extSliceAlgApArrayPrepared;
        $sliceAlgBpArray = $extSliceAlgBpArrayPrepared;
        $apParamNamesArray = $extApParamNamesArray;
        $bpParamNamesArray = $extBpParamNamesArray;

        if(count($bpParamNamesArray) != count($sliceAlgBpArray))
        {
            exit("Unmach params count and alg count. Slice type error");
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        //first alg always multyquery
        $query = $sliceAlgApArray[0];
        $paramName = $apParamNamesArray[0];

        $sliceData = array();
        $frameNumDelimiter = " AND `frameNum` IN (";
        $rowsTotalNum = 0;
        $res = $link->query($query);
        if (!$link->multi_query($query))
        {
            //err log
            echo "Can't exec multi query: (" .$query . ") " . $link->error;
        }

        do
        {
            if ($res = $link->store_result())
            {

                while($row = $res->fetch_array())
                {
                    $fieldCounter = 0;
                    $queryData[$fieldCounter] = $row['frameNum'];
                    $fieldCounter++;
                    $queryData[$fieldCounter] = $flightId;
                    $fieldCounter++;
                    $queryData[$fieldCounter] = $engineSerial;
                    $fieldCounter++;
                    $queryData[$fieldCounter] = $sliceCode;
                    $fieldCounter++;
                    $queryData[$fieldCounter] = $row[$paramName];

                    array_push($sliceData, $queryData);
                    $rowsTotalNum++;
                    $frameNumDelimiter .= $row['frameNum'] . ", ";
                }
                $res->free();
            }
        } while ($link->more_results() && $link->next_result());

        if(count($sliceData) < 1)
        {
            exit("No suituble data in flight for current slice");
        }

        $frameNumDelimiter = substr($frameNumDelimiter, 0, -2);
        $frameNumDelimiter .= ");";

        for($i = 1; $i < count($sliceAlgApArray); $i++)
        {
            $query = $sliceAlgApArray[$i];
            $paramName = $apParamNamesArray[$i];
            $query .= $frameNumDelimiter;

            $result = $link->query($query);

            $fieldCounter++;
            $rowCounter = 0;

            while($row = $result->fetch_array())
            {
                $sliceData[$rowCounter][$fieldCounter] = $row[$paramName];
                $rowCounter++;
            }
        }

        //bp
        for($i = 0; $i < count($sliceAlgBpArray); $i++)
        {
            $query = $sliceAlgBpArray[$i];
            $paramName = $bpParamNamesArray[$i];
            $query .= $frameNumDelimiter;

            $result = $link->query($query);

            $fieldCounter++;
            //put zero for all rows, if no bp on query
            for($rowCounter = 0; $rowCounter < $rowsTotalNum; $rowCounter++)
            {
                $sliceData[$rowCounter][$fieldCounter] = 0;
            }
            $rowCounter = 0;

            while($row = $result->fetch_array())
            {
                if($sliceData[$rowCounter][0] == $row['frameNum'])
                {
                    if(isset($row['value']))
                    {
                        $sliceData[$rowCounter][$fieldCounter] = $row['value'];
                    }
                    else //then it's bp and put 1
                    {
                        $sliceData[$rowCounter][$fieldCounter] = 1;
                    }
                }
                else
                {
                    $sliceData[$rowCounter][$fieldCounter] = 0;
                }
                $rowCounter++;
            }
        }

        $c->Disconnect();
        unset($c);

        return $sliceData;
    }

    public function InsertSliceData($extSliceData, $extSliceTableName,
            $extApParamNamesArray, $extBpParamNamesArray)
    {
        $sliceData = $extSliceData;
        $sliceTableName = $extSliceTableName;
        $apParamNamesArray = $extApParamNamesArray;
        $bpParamNamesArray = $extBpParamNamesArray;

        if(count($sliceData) < 1)
        {
            exit("No slice data");
        }

        $query = "INSERT INTO `".$sliceTableName."` (`frameNum`, `flightId`, `engineSerial`, `sliceCode`";
        for($i = 0; $i < count($apParamNamesArray); $i++)
        {
            $query .= ", `".$apParamNamesArray[$i]."`";
        }

        for($i = 0; $i < count($bpParamNamesArray); $i++)
        {
            $query .= ", `".$bpParamNamesArray[$i]."`";
        }

        $query .= ") VALUES ";

        for($i = 0; $i < count($sliceData); $i++)
        {
            $row = $sliceData[$i];

            $query .= "(";
            for($j = 0; $j < count($row); $j++)
            {
                $query .= "'".$row[$j] . "', ";
            }
            $query = substr($query, 0, -2);
            $query .= "), ";
        }
        $query = substr($query, 0, -2);

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);

        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }
        $stmt->close();
        $c->Disconnect();
        unset($c);
    }

    //================================================================
    //Summer
    //================================================================
    public function GetSliceSummer($extSliceCode, $extItemCode = '')
    {
        $sliceCode = $extSliceCode;
        $itemCode = $extItemCode;

        if(empty($itemCode))
        {
            $query = "SELECT * FROM `slicesummer` WHERE `sliceCode` LIKE '%|".$sliceCode."|%' ORDER BY `id`;";
            $c = new DataBaseConnector();
            $link = $c->Connect();

            $result = $link->query($query);
            $summerList = array();
            while($row = $result->fetch_array())
            {
                $summerInfo = array("code"=>$row['code'],
                        "name"=>$row['name'],
                        "sliceCode"=>$row['sliceCode'],
                        "alg"=>$row['alg']);
                array_push($summerList, $summerInfo);
            }

            $c->Disconnect();
            unset($c);

            return $summerList;
        }
        else
        {
            $query = "SELECT * FROM `slicesummer` WHERE `sliceCode` LIKE '%|".$sliceCode."|%' AND `code` = '".$itemCode."' ORDER BY `id`;";
            $c = new DataBaseConnector();
            $link = $c->Connect();

            $result = $link->query($query);
            $summerInfo = array();
            while($row = $result->fetch_array())
            {
                $summerInfo = array("code"=>$row['code'],
                        "name"=>$row['name'],
                        "sliceCode"=>$row['sliceCode'],
                        "alg"=>$row['alg']);
            }

            $c->Disconnect();
            unset($c);

            return $summerInfo;
        }
    }

    public function CalcSummerItem($extSummerInfo, $extSliceTableName, $extSliceCode)
    {
        $summerInfo = $extSummerInfo;
        $sliceTableName = $extSliceTableName;
        $sliceCode = $extSliceCode;

        $algParamNamesArr = $this->GetInnerSubstring($summerInfo['alg']);
        $algParams = array();

        $c = new DataBaseConnector();
        $link = $c->Connect();

        for($i = 0; $i < count($algParamNamesArr); $i++)
        {
            $query = "SELECT AVG(`".$algParamNamesArr[$i]."`) FROM `".$sliceTableName."` WHERE `sliceCode`='".$sliceCode."';";
            $result = $link->query($query);
            $summerList = array();
            $row = $result->fetch_array();
            array_push($algParams, $row[0]);
        }
        $c->Disconnect();
        unset($c);

        $alg = $summerInfo['alg'];

        //replacing names by avg values
        for($i = 0; $i < count($algParamNamesArr); $i++)
        {
            $alg = str_replace("|".$algParamNamesArr[$i]."|", $algParams[$i], $alg);
        }

        $res = 0;
        eval($alg);

        return $res;
    }

    public function GetFlightsFromSliceRaw($extSliceTableName, $extSliceCode = 0)
    {
        $sliceTableName = $extSliceTableName;
        $sliceCode = $extSliceCode;
        $flightIds = array();
        if($sliceCode == 0)
        {
            $c = new DataBaseConnector();
            $link = $c->Connect();

            $query = "SELECT DISTINCT(`flightId`) FROM `".$sliceTableName."`;";
            $result = $link->query($query);
            while($row = $result->fetch_array())
            {
                array_push($flightIds, $row[0]);
            }

            $c->Disconnect();
            unset($c);
        }
        else
        {
            $c = new DataBaseConnector();
            $link = $c->Connect();

            $query = "SELECT DISTINCT(`flightId`) FROM `".$sliceTableName."` WHERE `sliceCode`='".$sliceCode."';";
            $result = $link->query($query);
            while($row = $result->fetch_array())
            {
                array_push($flightIds, $row[0]);
            }

            $c->Disconnect();
            unset($c);
        }

        return $flightIds;
    }

    public function GetSliceDataByParamCodes($extSliceTableName, $extSliceCode, $extFlightId, $extCodesArr)
    {
        $sliceTableName = $extSliceTableName;
        $sliceCode = $extSliceCode;
        $flightId = $extFlightId;
        $codesArr = $extCodesArr;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `".$sliceTableName."` WHERE
            `sliceCode`='".$sliceCode."' AND
            `flightId`='".$flightId."';";
        $result = $link->query($query);

        $sliceData = array();
        while($row = $result->fetch_array())
        {
            for($i = 0; $i < count($codesArr); $i++)
            {
            $sliceData[$codesArr[$i]][] =  $row[$codesArr[$i]];
            }
            }

            $c->Disconnect();
            unset($c);

            return $sliceData;
    }

    //================================================================
    //Etalon
    //================================================================
    public function SetEtalonTableName($extSliceId, $extEtalonTableName)
    {
        $sliceId = $extSliceId;
        $etalonTableName = $extEtalonTableName;

        $query = "UPDATE `slices` SET `etalonTableName` = '".$etalonTableName."' WHERE `id` = '".$sliceId."';";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);

        if (!$stmt->execute())
        {
            echo('Error during query execution while setting etalonTable name into slice table' . $query);
        }
        $stmt->close();
        $c->Disconnect();
        unset($c);
    }

    public function GetSliceEtalonParamsList($extSliceCode)
    {
        $sliceCode = $extSliceCode;
        $sliceEtalonParamsList = array();
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `sliceetalonparams` WHERE `sliceCode` LIKE '%|".$sliceCode."|%' AND
            `XCode` IN (SELECT `code` FROM `slicesummer`) AND
            `YCode` IN (SELECT `code` FROM `slicesummer`);";
        $result = $link->query($query);
        while($row = $result->fetch_array())
        {
            $sliceEtalonParams = array(
                    "id" => $row["id"],
                    "sliceCode" => $sliceCode,
                    "XCode" => $row["XCode"],
                    "YCode" => $row["YCode"],
            );
            array_push($sliceEtalonParamsList, $sliceEtalonParams);
        }

        $c->Disconnect();
        unset($c);
        return $sliceEtalonParamsList;
    }

    //not exist in slicesummer and shood be calculated on exist A B C
    public function GetSliceEtalonRatedParamsList($extSliceCode)
    {
        $sliceCode = $extSliceCode;
        $sliceEtalonParamsList = array();
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `sliceetalonparams` WHERE `sliceCode` LIKE '%|".$sliceCode."|%' AND
        `XCode` NOT IN (SELECT `code` FROM `slicesummer`) AND
        `YCode` NOT IN (SELECT `code` FROM `slicesummer`);";
        $result = $link->query($query);
        while($row = $result->fetch_array())
        {
            $sliceEtalonParams = array(
                    "id" => $row["id"],
                    "sliceCode" => $sliceCode,
                    "XCode" => $row["XCode"],
                    "YCode" => $row["YCode"],
            );
            array_push($sliceEtalonParamsList, $sliceEtalonParams);
        }

        $c->Disconnect();
        unset($c);
        return $sliceEtalonParamsList;
    }

    public function CalcSummerItemForEtalon($extSummerInfo, $extSliceTableName, $extSliceCode, $extEngineSerial)
    {
        $summerInfo = $extSummerInfo;
        $sliceTableName = $extSliceTableName;
        $sliceCode = $extSliceCode;
        $engineSerial = $extEngineSerial;

        $algParamNamesArr = $this->GetInnerSubstring($summerInfo['alg']);

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT DISTINCT `flightId` FROM `".$sliceTableName."` WHERE `sliceCode`='".$sliceCode."' AND `engineSerial` = '".$engineSerial."';";
        $result = $link->query($query);
        $flightIdArr = array();
        while($row = $result->fetch_array())
        {
            array_push($flightIdArr, $row['flightId']);
        }

        $avgFlightValues = array();
        //calc avg value for each flight
        for($j = 0; $j < count($flightIdArr); $j++)
        {
            $algParams = array();
            for($i = 0; $i < count($algParamNamesArr); $i++)
            {
                $query = "SELECT AVG(`".$algParamNamesArr[$i]."`) FROM `".$sliceTableName."`
                WHERE `sliceCode`='".$sliceCode."' AND `flightId`='".$flightIdArr[$j]."' AND `engineSerial` = '".$engineSerial."';";
                $result = $link->query($query);
                $row = $result->fetch_array();
                array_push($algParams, $row[0]);
            }

            $alg = $summerInfo['alg'];

            //replacing names by avg values
            for($i = 0; $i < count($algParamNamesArr); $i++)
            {
                $alg = str_replace("|".$algParamNamesArr[$i]."|", $algParams[$i], $alg);
            }

            $res = 0;

            eval($alg);
            array_push($avgFlightValues, $res);
        }

        $c->Disconnect();
        unset($c);

        return $avgFlightValues;
    }

    public function InsertEtalonItem($extEtalonTableName, $extSliceId, $extSliceCode, $extEngineSerial,
            $extSliceEtalonParamsXCode, $extSliceEtalonParamsYCode,
            $extAvgFlightValuesXCode, $extAvgFlightValuesYCode,
            $extXAvgVal, $extYAvgVal, $extA, $extB, $extC)
    {
        $etalonTableName = $extEtalonTableName;
        $sliceId = $extSliceId;
        $sliceCode = $extSliceCode;
        $engineSerial = $extEngineSerial;
        $sliceEtalonParamsXCode = $extSliceEtalonParamsXCode;
        $sliceEtalonParamsYCode = $extSliceEtalonParamsYCode;
        $avgFlightValuesXCode = implode(",",$extAvgFlightValuesXCode);
        $avgFlightValuesYCode = implode(",",$extAvgFlightValuesYCode);
        $XAvgVal = $extXAvgVal;
        $YAvgVal = $extYAvgVal;
        $A = $extA;
        $B = $extB;
        $C = $extC;

        $query = "INSERT INTO `".$etalonTableName."` (`sliceId`,
        `sliceCode`, `engineSerial`, `XCode`, `YCode`, `XAvgGeneral`, `YAvgGeneral`,
        `XAvgFlightValues`, `YAvgFlightValues`,
        `A`, `B`, `C`) VALUES ('".$sliceId."',
        '".$sliceCode."','".$engineSerial."','".$sliceEtalonParamsXCode."', '".$sliceEtalonParamsYCode."',
        '".$XAvgVal."', '".$YAvgVal."',
        '".$avgFlightValuesXCode."', '".$avgFlightValuesYCode."',
        '".$A."', '".$B."', '".$C."');";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);

        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
        }
        $stmt->close();
        $c->Disconnect();
        unset($c);
    }

    public function GetEtalonRowUnknownPosition($extEtaloneTableName, $extSliceId, $extEngineSerial, $extSliceCode, $extXCode, $extYCode)
    {
        $etaloneTableName = $extEtaloneTableName;
        $sliceId = $extSliceId;
        $engineSerial = $extEngineSerial;
        $sliceCode = $extSliceCode;
        $XCode = $extXCode;
        $YCode = $extYCode;

        $query = "SELECT * FROM `".$etaloneTableName."` WHERE " .
            "`sliceId` = '".$sliceId."' AND " .
            "`engineSerial` = '".$engineSerial."' AND " .
            "`sliceCode` LIKE '".$sliceCode."Engine%' AND " .
            "`XCode` = '".$XCode."' AND " .
            "`YCode` = '".$YCode."';";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);

        if($row = $result->fetch_array())
        {
            $etalonRow = array(
                    "id" => $row["id"],
                    "sliceId" => $row["sliceId"],
                    "sliceCode" => $row["sliceCode"],
                    "XCode" => $row["XCode"],
                    "YCode" => $row["YCode"],
                    "XAvgGeneral" => $row["XAvgGeneral"],
                    "YAvgGeneral" => $row["YAvgGeneral"],
                    "XAvgFlightValues" => $row["XAvgFlightValues"],
                    "YAvgFlightValues" => $row["YAvgFlightValues"],
                    "A" => $row["A"],
                    "B" => $row["B"],
                    "C" => $row["C"]);
        }
        else
        {
            $etalonRow = null;
        }

        $c->Disconnect();
        unset($c);

        return $etalonRow;
    }

    public function GetEtalonRow($extEtaloneTableName, $extSliceId, $extEngineSerial, $extSliceCode, $extXCode, $extYCode)
    {
        $etaloneTableName = $extEtaloneTableName;
        $sliceId = $extSliceId;
        $engineSerial = $extEngineSerial;
        $sliceCode = $extSliceCode;
        $XCode = $extXCode;
        $YCode = $extYCode;

        $query = "SELECT * FROM `".$etaloneTableName."` WHERE " .
                "`sliceId` = '".$sliceId."' AND " .
                "`engineSerial` = '".$engineSerial."' AND " .
                "`sliceCode` = '".$sliceCode."' AND " .
                "`XCode` = '".$XCode."' AND " .
                "`YCode` = '".$YCode."';";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);

        if($row = $result->fetch_array())
        {
            $etalonRow = array(
                    "id" => $row["id"],
                    "sliceCode" => $row["sliceId"],
                    "XCode" => $row["XCode"],
                    "YCode" => $row["YCode"],
                    "XAvgGeneral" => $row["XAvgGeneral"],
                    "YAvgGeneral" => $row["YAvgGeneral"],
                    "XAvgFlightValues" => $row["XAvgFlightValues"],
                    "YAvgFlightValues" => $row["YAvgFlightValues"],
                    "A" => $row["A"],
                    "B" => $row["B"],
                    "C" => $row["C"]);
        }
        else
        {
            $etalonRow = null;
        }

        $c->Disconnect();
        unset($c);

        return $etalonRow;
    }

    public function GetEtalonEngineSlicesPairs($extEtaloneTableName, $extSliceId)
    {
        $etaloneTableName = $extEtaloneTableName;
        $sliceId = $extSliceId;

        $query = "SELECT DISTINCT `engineSerial` FROM `".$etaloneTableName."` WHERE `sliceId` = '".$sliceId."';";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);

        $etalonRow = null;

        while($row = $result->fetch_array())
        {
            $query = "SELECT DISTINCT `sliceCode` FROM `".$etaloneTableName."` WHERE `engineSerial` = '".$row['engineSerial']."' AND `sliceId` = '".$sliceId."';";
            $result2 = $link->query($query);

            while($row2 = $result2->fetch_array())
            {
                $etalonRow[] = array("sliceCode" => $row2['sliceCode'], "engineSerial" => $row['engineSerial']);
            }
        }

        $c->Disconnect();
        unset($c);

        return $etalonRow;
    }

    //================================================================
    //Engine
    //================================================================
    public function GetEngineSerialsInSlice($extSliceTableName, $extSliceCode)
    {
        $sliceTableName = $extSliceTableName;
        $sliceCode = $extSliceCode;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT DISTINCT `engineSerial` FROM `".$sliceTableName."` WHERE `sliceCode`='".$sliceCode."';";
        $result = $link->query($query);
        $engineSerialArr = array();
        while($row = $result->fetch_array())
        {
            array_push($engineSerialArr, $row['engineSerial']);
        }

        return $engineSerialArr;
    }

    public function CreateEngineEtalonModel($extSliceTableName)
    {
        $sliceTableName = $extSliceTableName;
        $etalonTableName = $sliceTableName . "_etalon";

        $query = "CREATE TABLE `".$etalonTableName."` (`id` MEDIUMINT NOT NULL AUTO_INCREMENT,
        `sliceId` VARCHAR(255),
        `sliceCode` VARCHAR(255),
        `engineSerial` VARCHAR(255),
        `XCode` VARCHAR(50),
        `YCode` VARCHAR(50),
        `XAvgGeneral` DOUBLE,
        `YAvgGeneral` DOUBLE,
        `XAvgFlightValues` TEXT,
        `YAvgFlightValues` TEXT,
        `A` DOUBLE,
        `B` DOUBLE,
        `C` DOUBLE,
        PRIMARY KEY (`id`));";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $stmt = $link->prepare($query);
        if (!$stmt->execute()) {
            echo('Error during query execution ' . $query);
            $etalonTableName = -1; //error flag returning
        }
        $c->Disconnect();
        unset($c);

        return $etalonTableName;
    }

    public function DropEngineEtalonModel($extSliceTableName)
    {
        $sliceTableName = $extSliceTableName;
        $etalonTableName = $sliceTableName . "_etalon";

        if($this->GetEngineEtalonTableName($sliceTableName) != -1)
        {
            $query = "DROP TABLE `".$etalonTableName."`;";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $stmt = $link->prepare($query);
            if (!$stmt->execute()) {
                echo('Error during query execution ' . $query);
            }
            $c->Disconnect();
            unset($c);
        }
    }


    public function GetEngineEtalonTableName($extSliceTableName)
    {
        $sliceTableName = $extSliceTableName;
        $etalonTableName = $sliceTableName . "_etalon";

        $query = "DESCRIBE `".$etalonTableName."`";

        $c = new DataBaseConnector();
        $link = $c->Connect();

        if(!$link->query($query))
        {
            $etalonTableName = -1;
        }

        return $etalonTableName;
    }

    public function GetSlicesByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `id` FROM `slices` WHERE `author` = '".$author."';";
        $mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

        $list = array();
        while($row = $mySqliResult->fetch_array())
        {
            $item = $this->GetSliceInfo($row['id']);
            array_push($list, $item);
        }
        $mySqliResult->free();
        $c->Disconnect();

        unset($c);

        return $list;
    }

    public function DeleteSlicesByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `slices` WHERE `author` = '".$author."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function GetInnerSubstring($string)
    {
        $delim = '|';
        $delimitedArr = array();
        $stringArr = explode($delim, $string);
        for($i = 1; $i < count($stringArr); $i+=2)
        {

            array_push($delimitedArr, $stringArr[$i]);
        }

        return $delimitedArr;
    }

}
?>
