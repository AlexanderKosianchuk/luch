<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Bru
{
    public function CreateBruTypeTable()
    {
        $query = "SHOW TABLES LIKE 'brutypes';";
        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `brutypes` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `bruType` VARCHAR(255),
                `gradiApTableName` VARCHAR(255),
                `gradiBpTableName` VARCHAR(255),
                `excListTableName` VARCHAR(255),
                `paramSetTemplateListTableName` VARCHAR(20),
                `stepLength` FLOAT,
                `stepDivider` INT(11),
                `frameLength` INT(11),
                `wordLength` INT(11),
                `aditionalInfo` TEXT,
                `headerLength` INT(11),
                `headerScr` TEXT,
                `frameSyncroCode` VARCHAR(8),
                `previewParams` varchar(255),
                `author`  VARCHAR(200),
                `collada` VARCHAR(255),
                PRIMARY KEY (`id`)) " .
                "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";


            $stmt = $link->prepare($query);
            if (!$stmt->execute()) {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }
        unset($c);
    }

    public function GetBruList($extAvaliableBruTypesIds)
    {
        $avaliableBruTypesIds = $extAvaliableBruTypesIds;

        $bruList = array();
        if(count($avaliableBruTypesIds) > 0)
        {
            $inString = "";
            foreach($avaliableBruTypesIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $c = new DataBaseConnector();
            $link = $c->Connect();
            //var_dump($mySqliConnection);

            $query = "SELECT * FROM `brutypes` WHERE `id` IN (".$inString.") ORDER BY `id`;";
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $bruInfo = $this->GetBruInfo($row['bruType']);
                array_push($bruList, $bruInfo);
            }

            $result->free();
            $c->Disconnect();

            unset($c);
        }

        return $bruList;
    }

    public function GetBruInfo($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $bruInfo = array();
        foreach ($row as $key => $value)
        {
            $bruInfo[$key] = $value;
        }

        /*$bruInfo = array("id" => $row['id'],
            "bruType" => $row['bruType'],
            "gradiApTableName" => $row['gradiApTableName'],
            "gradiBpTableName" => $row['gradiBpTableName'],
            "excListTableName" => $row['excListTableName'],
            "paramSetTemplateListTableName" =>
                $row['paramSetTemplateListTableName'],
            "stepLength" => $row['stepLength'], //seconds in 1 frame
            "stepDivider" => $row['stepDivider'],
            "frameLength" => $row['frameLength'],
            "wordLength" => $row['wordLength'],
            "headerLength" => $row['headerLength'],
            "headerScr" => $row['headerScr'],
            "frameSyncroCode" => $row['frameSyncroCode'],
            "aditionalInfo" => $row['aditionalInfo']
        );*/

        $result->free();
        $c->Disconnect();

        unset($c);

        return $bruInfo;
    }

    public function GetBruInfoById($extBruTypeId)
    {
        $bruTypeId = $extBruTypeId;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `brutypes` WHERE `id` = '".$bruTypeId."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $bruInfo = array();
        foreach ($row as $key => $value)
        {
            $bruInfo[$key] = $value;
        }

        /*$bruInfo = array("id" => $row['id'],
         "bruType" => $row['bruType'],
                "gradiApTableName" => $row['gradiApTableName'],
                "gradiBpTableName" => $row['gradiBpTableName'],
                "excListTableName" => $row['excListTableName'],
                "paramSetTemplateListTableName" =>
                $row['paramSetTemplateListTableName'],
                "stepLength" => $row['stepLength'], //seconds in 1 frame
                "stepDivider" => $row['stepDivider'],
                "frameLength" => $row['frameLength'],
                "wordLength" => $row['wordLength'],
                "headerLength" => $row['headerLength'],
                "headerScr" => $row['headerScr'],
                "frameSyncroCode" => $row['frameSyncroCode'],
                "aditionalInfo" => $row['aditionalInfo']
        );*/

        $result->free();
        $c->Disconnect();

        unset($c);

        return $bruInfo;
    }

    public function GetBruApCyclo($extBruType, $extJtStartIndex, $extJtPageSize, $extJtSorting)
    {
        $bruType = $extBruType;
        $jtStartIndex = $extJtStartIndex;
        $jtPageSize = $extJtPageSize;
        $jtSorting = $extJtSorting;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT * FROM `".$cycloApTableName."` ";

        if($jtSorting != -1)
        {
            $jtSorting = explode(" ", $jtSorting);
            $jtSorting = "`" . $jtSorting[0] . "`" . $jtSorting[1];
            $query .= "ORDER BY " . $jtSorting . " ";
        }

        if(($extJtStartIndex != -1) && ($extJtPageSize != -1))
        {
            $query .= "LIMIT ". $jtStartIndex . ", " . $jtPageSize . " ";
        }

        $query .= "; ";

        $result = $link->query($query);
        //error_log($query);

        $cycloAp = array();
        while($row = $result->fetch_array())
        {
            $channel = str_replace(",", ", ", $row['channel']);

            $gradiParam = array("id" => $row['id'],
                "channel" => $channel,
                "code" => $row['code'],
                "name" => $row['name'],
                "dim" => $row['dim'],
                "minValue" => $row['minValue'],
                "maxValue" => $row['maxValue'],
                "color" => $row['color'],
                "type" => $row['type'],
                "prefix" => $row['prefix'],
                "mask" => $row['mask'],
                "shift" => $row['shift'],
                "minus" => $row['minus'],
                "k" => $row['k'],
                "xy" => $row['xy'],
                "alg" => $row['alg']);
            $gradiParam['xy'] = json_decode($gradiParam['xy'], true);
            array_push($cycloAp, $gradiParam);
        }
        //var_dump($cycloAp);
        $result->free();
        $c->Disconnect();

        unset($c);

        return $cycloAp;
    }

    public function GetBruApCycloParam($extBruType, $extParamId)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT * FROM `".$cycloApTableName."` WHERE `id`=".$paramId.";";

        $result = $link->query($query);
        //error_log($query);

        $cycloAp = array();
        while($row = $result->fetch_array())
        {
            $channel = str_replace(",", ", ", $row['channel']);

            $gradiParam = array("id" => $row['id'],
                    "channel" => $channel,
                    "code" => $row['code'],
                    "name" => $row['name'],
                    "dim" => $row['dim'],
                    "minValue" => $row['minValue'],
                    "maxValue" => $row['maxValue'],
                    "color" => $row['color'],
                    "type" => $row['type'],
                    "prefix" => $row['prefix'],
                    "mask" => $row['mask'],
                    "shift" => $row['shift'],
                    "minus" => $row['minus'],
                    "k" => $row['k'],
                    "xy" => $row['xy'],
                    "alg" => $row['alg']);
            $gradiParam['xy'] = json_decode($gradiParam['xy'], true);
            array_push($cycloAp, $gradiParam);
        }
        //var_dump($cycloAp);
        $result->free();
        $c->Disconnect();

        unset($c);

        return $cycloAp;
    }

    public function UpdateApCycloParamAttr($extBruType, $extParamId, $extParamAttr, $extParamAttrVal)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;
        $paramAttr = $extParamAttr;
        $paramAttrVal = $extParamAttrVal;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "UPDATE `".$cycloApTableName."` SET ";
        $query .= "`".$paramAttr."` = '".$paramAttrVal."' ";
        $query .= " WHERE `id` = '".$paramId."';";

        $stmt = $link->prepare($query);

        $res = "OK";
        if (!$stmt->execute())
        {
            $res = "ERROR";
            error_log('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
            echo('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
        }

        $stmt->close();
        $c->Disconnect();
        unset($c);

        return $res;
    }

    public function UpdateApCyclo($extBruType, $extParamId, $extParamData)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;
        $paramData = $extParamData;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "UPDATE `".$cycloApTableName."` SET ";
        foreach ($paramData as $key => $val)
        {
            if($key == 'channel')
            {
                $val = str_replace(' ', '', $val);
            }
            $query .= "`".$key."` = '".$val."', ";
        }

        $query = substr($query, 0, -2);
        $query .= " WHERE `id` = '".$paramId."';";

        $stmt = $link->prepare($query);

        $res = "OK";
        if (!$stmt->execute())
        {
            $res = "ERROR";
            error_log('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
            echo('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
        }

        $stmt->close();
        $c->Disconnect();
        unset($c);

        return $res;
    }

    public function CreateApCycloParam($extBruType, $extParamData)
    {
        $bruType = $extBruType;
        $paramData = $extParamData;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "INSERT INTO `".$cycloApTableName."` ( ";
        foreach ($paramData as $key => $val)
        {
            $query .= "`".$key."`, ";
        }

        $query = substr($query, 0, -2);
        $query .= " ) VALUES ( ";

        foreach ($paramData as $key => $val)
        {
            if($key == 'channel')
            {
                $val = str_replace(' ', '', $val);
            }
            $query .= "'".$val."', ";
        }

        $query = substr($query, 0, -2);
        $query .= ");";

        //error_log($query);

        $stmt = $link->prepare($query);

        $res = "OK";
        if (!$stmt->execute())
        {
            $res = "ERROR";
            error_log('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
            echo('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
        }

        $stmt->close();
        $c->Disconnect();
        unset($c);

        return $res;
    }

    public function DeleteApCycloParam($extBruType, $extParamId)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "DELETE FROM `".$cycloApTableName."` WHERE `id` = '".$paramId."';";

        $stmt = $link->prepare($query);

        $res = "OK";
        if (!$stmt->execute())
        {
            $res = "ERROR";
            error_log('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
            echo('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
        }

        $stmt->close();
        $c->Disconnect();
        unset($c);

        return $res;
    }

    public function GetBruApCycloRowsTotalCount($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT COUNT(*) FROM `".$cycloApTableName."`;";
        $result = $link->query($query);
        //echo($query);

        $cycloAp = array();
        $rowsCount = 0;
        if($row = $result->fetch_array())
        {
            $rowsCount = $row['COUNT(*)'];
        }
        //var_dump($cycloAp);
        $result->free();
        $c->Disconnect();

        unset($c);

        return $rowsCount;
    }

    public function GetBruApGradi($extBruType, $extParamId)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT `xy` FROM `".$cycloApTableName."` WHERE `id`=".$paramId.";";
        $result = $link->query($query);
        //error_log($query);

        $cycloAp = array();
        if($row = $result->fetch_array())
        {
            if(strlen($row['xy']) > 0)
            {
                $cycloAp = json_decode($row['xy'], true);
            }
        }

        $cycloApAssoc = array();

        foreach ($cycloAp as $code => $val)
        {
            $row = array(
                    'gradiId' => $code,
                    'gradiCode' => $val['y'],
                    'gradiPh' => $val['x']
            );
            $cycloApAssoc[] = $row;
        }

        //var_dump($cycloAp);
        $result->free();
        $c->Disconnect();

        unset($c);

        return $cycloApAssoc;
    }

    public function UpdateApGradi($extBruType, $extParamId, $extParamData)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;
        $paramData = $extParamData;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "UPDATE `".$cycloApTableName."` SET `xy` = '" . json_encode($paramData) . "' " .
                "WHERE `id` = " .$paramId. ";";

        $stmt = $link->prepare($query);

        $res = "OK";
        if (!$stmt->execute())
        {
            $res = "ERROR";
            error_log('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
            echo('Error during query execution while setting etalonTable name into slice table. Query - ' . $query);
        }

        $stmt->close();
        $c->Disconnect();
        unset($c);

        return $res;
    }

    public function GetBruApCycloPrefixOrganized($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $cycloAp = array();
        foreach($prefixesArr as $item => $prefix)
        {
            $query = "SELECT * FROM `".$cycloApTableName."` WHERE `prefix` LIKE '".$prefix."'  ORDER BY `channel` ASC;"; /* here used LIKE because troubles using = */
            $result = $link->query($query);

            $cycloParamArray = array();
            while($row = $result->fetch_array())
            {
                $gradiParam = array("id" => $row['id'],
                    "channel" => $row['channel'],
                    "code" => $row['code'],
                    "name" => $row['name'],
                    "dim" => $row['dim'],
                    "minValue" => $row['minValue'],
                    "maxValue" => $row['maxValue'],
                    "color" => $row['color'],
                    "type" => $row['type'],
                    "prefix" => $row['prefix'],
                    "mask" => $row['mask'],
                    "shift" => $row['shift'],
                    "minus" => $row['minus'],
                    "k" => $row['k'],
                    "xy" => $row['xy'],
                    "alg" => $row['alg']);
                $gradiParam['xy'] = json_decode($gradiParam['xy'], true);
                array_push($cycloParamArray, $gradiParam);
            }
            $cycloAp[$prefix] = $cycloParamArray;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $cycloAp;
    }

    public function GetBruApCycloPrefixFreq($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $channelFreq = array();
        foreach($prefixesArr as $item => $prefix)
        {
            $query = "SELECT `channel` FROM `".$cycloApTableName."` WHERE `prefix` LIKE '".$prefix."' LIMIT 1";
            $result = $link->query($query);

            $cycloParamArray = array();
            $row = $result->fetch_array();
            $channels = explode(",", $row["channel"]);
            $channelFreq[$prefix] = count($channels);
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $channelFreq;
    }

    public function GetBruApCycloPrefixes($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $prefixesArr;
    }

    public function GetBruBpCycloPrefixes($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloBpTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $prefixesArr;
    }

    public function GetBruBpCycloPrefixOrganized($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();

        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloBpTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $cycloBp = array();
        foreach($prefixesArr as $item => $prefix)
        {
            $query = "SELECT * FROM `".$cycloBpTableName."` WHERE `prefix` LIKE '".$prefix."'  ORDER BY `channel`, `mask` ASC;";
            $result = $link->query($query);

            $cycloParamArray = array();
            while($row = $result->fetch_array())
            {
                $gradiParam = array("id" => $row['id'],
                        "code" => $row['code'],
                        "channel" => $row['channel'],
                        "mask" => $row['mask'],
                        "name" => $row['name'],
                        "type" => $row['type'],
                        "prefix" => $row['prefix'],
                        "basis" => $row['basis'],
                        "color" => $row['color']);

                array_push($cycloParamArray, $gradiParam);
            }
            $cycloBp[$prefix] = $cycloParamArray;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $cycloBp;
    }

    public function GetBruBpCycloPrefixFreq($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloBpTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array())
        {
            array_push($prefixesArr, $row['prefix']);
        }

        $channelFreq = array();
        foreach($prefixesArr as $item => $prefix)
        {
            $query = "SELECT `channel` FROM `".$cycloBpTableName."` WHERE `prefix` LIKE '".$prefix."' LIMIT 1";
            $result = $link->query($query);

            $cycloParamArray = array();
            $row = $result->fetch_array();
            $channels = explode(",", $row["channel"]);
            $channelFreq[$prefix] = count($channels);
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $channelFreq;
    }

    /*public function GetBruBpGradi($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $query = "SELECT * FROM `".$cycloBpTableName."` ORDER BY `channel` ASC;";
        $result = $link->query($query);

        $cycloBp = array();
        while($row = $result->fetch_array())
        {
            $gradiParam = array("id" => $row['id'],
                "channel" => $row['channel'],
                "code" => $row['code'],
                "name" => $row['name'],
                "type" => $row['type'],
                "mask" => $row['mask'],
                "basis" => $row['basis'],
                "color" => $row['color']);
            array_push($cycloBp, $gradiParam);
        }
        $result->free();
        $c->Disconnect();

        unset($c);

        return $cycloBp;
    }*/

    public function GetBruApHeaders($extBruType)
    {
        $bruType = $extBruType;
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $apHeader = array();
        $query = "SELECT * FROM `".$cycloApTableName."` WHERE 1;";
        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                "channel" => $row['channel'],
                "code" => $row['code'],
                "name" => $row['name'],
                "dim" => $row['dim'],
                "minValue" => $row['minValue'],
                "maxValue" => $row['maxValue'],
                "color" => $row['color']);
            array_push($apHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $apHeader;
    }

    public function GetBruApHeadersWithPaging($extBruType, $extStartIndex, $extEndIndex)
    {
        $bruType = $extBruType;
        $startIndex = $extStartIndex;
        $endIndex = $extEndIndex;
        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $apHeader = array();
        $query = "SELECT * FROM `".$cycloApTableName."` WHERE 1 ".
            "LIMIT " . $startIndex . ", " . ($endIndex - $startIndex) . ";";
        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                    "channel" => $row['channel'],
                    "code" => $row['code'],
                    "name" => $row['name'],
                    "dim" => $row['dim'],
                    "minValue" => $row['minValue'],
                    "maxValue" => $row['maxValue'],
                    "color" => $row['color']);
            array_push($apHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $apHeader;
    }

    public function GetBruApHeadersByRequest($extBruType, $extRequest)
    {
        $bruType = $extBruType;
        $request = $extRequest;
        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $apHeader = array();
        $query = "SELECT * FROM `".$cycloApTableName."` ".
                "WHERE `code` LIKE '%" . $request . "%' OR `name` LIKE '%" . $request . "%' OR `channel` LIKE '%" . $request . "%' GROUP BY `code` ;";
        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                    "channel" => $row['channel'],
                    "code" => $row['code'],
                    "name" => $row['name'],
                    "dim" => $row['dim'],
                    "minValue" => $row['minValue'],
                    "maxValue" => $row['maxValue'],
                    "color" => $row['color']);
            array_push($apHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $apHeader;
    }

    public function GetBruBpHeaders($extBruType)
    {
        $bruType = $extBruType;
        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $bpHeader = array();

        $query = "SELECT * FROM `".$cycloBpTableName."` WHERE 1;";
        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                "code" => $row['code'],
                "channel" => $row['channel'],
                "name" => $row['name'],
                "type" => $row['type'],
                "mask" => $row['mask'],
                "basis" => $row['basis'],
                "color" => $row['color']);
            array_push($bpHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $bpHeader;
    }

    public function GetBruBpHeadersWithPaging($extBruType, $extStartIndex, $extEndIndex)
    {
        $bruType = $extBruType;
        $startIndex = $extStartIndex;
        $endIndex = $extEndIndex;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $bpHeader = array();

        $query = "SELECT * FROM `".$cycloBpTableName."` WHERE 1 ".
            "LIMIT " . $startIndex . ", " . ($endIndex - $startIndex) . ";";

        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                    "code" => $row['code'],
                    "channel" => $row['channel'],
                    "name" => $row['name'],
                    "type" => $row['type'],
                    "mask" => $row['mask'],
                    "basis" => $row['basis'],
                    "color" => $row['color']);
            array_push($bpHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $bpHeader;
    }

    public function GetBruBpHeadersByRequest($extBruType, $extRequest)
    {
        $bruType = $extBruType;
        $request = $extRequest;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $bpHeader = array();

        $query = "SELECT * FROM `".$cycloBpTableName."` ".
                "WHERE `code` LIKE '%" . $request . "%' OR  `name` LIKE '%" . $request . "%' OR `channel` LIKE '%" . $request . "%' GROUP BY `code` ;";

        $result = $link->query($query);

        while($row = $result->fetch_array())
        {
            $paramInfo = array("id" => $row['id'],
                    "code" => $row['code'],
                    "channel" => $row['channel'],
                    "name" => $row['name'],
                    "type" => $row['type'],
                    "mask" => $row['mask'],
                    "basis" => $row['basis'],
                    "color" => $row['color']);
            array_push($bpHeader, $paramInfo);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $bpHeader;
    }

//     public function GetBruApHeadersNormalized($extBruType)
//     {
//         $bruType = $extBruType;
//         $apHeader = $this->GetBruApGradi($bruType);

//         $apHeaderNorm = array();
//         $apHeaderNormCounter = -1;

//         for ($i = 0; $i < count($apHeader); $i++)
//         {
//         array_push($apHeaderNorm, $apHeader[$i]);
//         $apHeaderNormCounter++;
//         unset($apHeader[$i]);
//         $i--;
//         $apHeader = array_values($apHeader);

//         for($j = 0; $j < count($apHeader);)
//         {
//         if($apHeaderNorm[$apHeaderNormCounter]['code'] == $apHeader[$j]['code'])
//         {
//                         //if params have same codes, concat channel
//         $apHeaderNorm[$apHeaderNormCounter]['channel'] =
//         $apHeaderNorm[$apHeaderNormCounter]['channel'] . ", " .$apHeader[$j]['channel'];
//         unset($apHeader[$j]);
//         $apHeader = array_values($apHeader);
//         }
//         else
//         {
//         $j++;
//         }
//         }
//         }
//         return $apHeaderNorm;
//         }

//         public function GetBruBpHeadersNormalized($extBruType)
//         {
//         $bruType = $extBruType;
//         $bpHeader = $this->GetBruBpGradi($bruType);

//         $bpHeaderNorm = array();
//         $bpHeaderNormCounter = -1;

//         for ($i = 0; $i < count($bpHeader); $i++)
//         {
//         array_push($bpHeaderNorm, $bpHeader[$i]);
//         $bpHeaderNormCounter++;
//             unset($bpHeader[$i]);
//             $i--;
//             $bpHeader = array_values($bpHeader);

//             for($j = 0; $j < count($bpHeader);)
//             {
//             if($bpHeaderNorm[$bpHeaderNormCounter]['code'] == $bpHeader[$j]['code'])
//                 {
//                 //if params have same codes, concat channel and mask
//                         $bpHeaderNorm[$bpHeaderNormCounter]['channel'] =
//                 $bpHeaderNorm[$bpHeaderNormCounter]['channel'] . ", " .$bpHeader[$j]['channel'];
//                 unset($bpHeader[$j]);
//                 $bpHeader = array_values($bpHeader);
//                 }
//                     else
//                 {
//                     $j++;
//                 }
//             }
//         }
//         return $bpHeaderNorm;
//     }



//    public function ShowAllApHeaders($extApHeader)
//    {
//        $apHeader = (array)$extApHeader;
//        $cellCount = count($apHeader);
//        $tableWidth = $cellCount * 180 + 180;
//
//        printf("<table border=\"1\" width=\"%s px\"><tr>
//            <td class=\"VievTableCellHeader\" style=\"text-align:center;\">T</td>", $tableWidth);
//        for($i = 0; $i < $cellCount; $i++)
//        {
//            $paramInfo = $apHeader[$i];
//            printf("<td class=\"VievTableCellHeader\">%s, %s</br>%s (%s)</td>",
//                $paramInfo['name'], $paramInfo['dim'], $paramInfo['code'], $paramInfo['channel']);
//        }
//
//        printf("</tr></table>");
//    }

    public function GetSelectedFlightApHeadersByCodes($extCodes, $extBruType)
    {
        $bruType = $extBruType;
        $codes = $extCodes;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $apHeader = array();

        for($i = 0; $i < count($codes); $i++)
        {
            $query = "SELECT * FROM `".$cycloApTableName."` WHERE `code` = '".$codes[$i]."';";
            $result = $link->query($query);

            $row = $result->fetch_array();
            $paramInfo = array("id" => $row['id'],
                "channel" => $row['channel'],
                "code" => $row['code'],
                "name" => $row['name'],
                "dim" => $row['dim'],
                "minValue" => $row['minValue'],
                "maxValue" => $row['maxValue']);
            array_push($apHeader, $paramInfo);

        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $apHeader;
    }

    public function GetCodesArray($extBruType)
    {
        $bruType = $extBruType;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        //var_dump($mySqliConnection);

        $query = "SELECT `gradiApTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT * FROM `".$cycloApTableName."` ORDER BY `channel` ASC;";
        $result = $link->query($query);

        $codesArray = array();
        while($row = $result->fetch_array())
        {
            $paramCode = array($row['channel'] => $row['code']);
            $codesArray += $paramCode;
        }
        $result->free();
        $c->Disconnect();

        unset($c);

        return $codesArray;
    }
    public function GetParamType($extParamCode,
        $extCycloApTableName, $extCycloBpTableName)
    {
        $paramCode = $extParamCode;
        $cycloApTableName = $extCycloApTableName;
        $cycloBpTableName = $extCycloBpTableName;
        $paramType = "null";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $query = "SELECT `id` FROM `".$cycloApTableName."` WHERE (`code` = ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param('s', $paramCode);
        $stmt->execute();
        $stmt->bind_result($id);
        while ($stmt->fetch())
        {
            $paramType = PARAM_TYPE_AP;
        }
        $stmt->close();

        //if param not in Ap look in gradiBpTable
        if($paramType != PARAM_TYPE_AP)
        {
            $query = "SELECT `id` FROM `".$cycloBpTableName."` WHERE (`code` = ?);";
            $stmt = $link->prepare($query);
            $stmt->bind_param('s', $paramCode);
            $stmt->execute();
            $stmt->bind_result($id);
            while ($stmt->fetch())
            {
                $paramType = PARAM_TYPE_BP;
            }
        }

        unset($c);
        //if param not found neithr in gradiApTable not gradiBpTable,
        //it stais null that means error
        if($paramType == "null")
        {
            //err log
        }
        return $paramType;
    }

    public function GetChannelsAndMasksByCode($extBpGradiTableName, $extParamCode)
    {
        $code = (array)$extParamCode;
        $bpGradiTableName = $extBpGradiTableName;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `channel`, `mask` FROM `".$bpGradiTableName.
            "` WHERE `code` IN (";

        for($i = 0; $i < count($code); $i++)
        {
            $query .= "'" . $code[$i]."', ";
        }

        $query  = substr($query , 0, -2);
        $query .= ");";

        $result = $link->query($query);
        $channel = array();
        $mask = array();

        while($row = $result->fetch_array())
        {
            $channel[] = $row['channel'];
            $mask[] = $row['mask'];
        }

        $c->Disconnect();
        unset($c);

        $channelAndMask = array();
        array_push($channelAndMask, $channel);
        array_push($channelAndMask, $mask);

        return $channelAndMask;
    }

    /*public function GetChannelsByCode($extApGradiTableName, $extParamCode)
    {
        $code = (array)$extParamCode;
        $apGradiTableName = $extApGradiTableName;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `channel` FROM `".$apGradiTableName."` WHERE `code` IN (";

        for($i = 0; $i < count($code); $i++)
        {
            $query .= "'".$code[$i]."', ";
        }

        $query  = substr($query , 0, -2);
        $query .= ");";

        $result = $link->query($query);
        $channel = array();

        while($row = $result->fetch_array())
        {
            $channel[] = $row['channel'];
        }

        $c->Disconnect();
        unset($c);

        return $channel;
    }*/

    public function GetParamInfoByCode($extCycloApTableName, $extCycloBpTableName, $extParamCode, $extParamType = "")
    {
        $cycloApTableName = $extCycloApTableName;
        $cycloBpTableName = $extCycloBpTableName;
        $paramType = $extParamType;
        $paramCode = $extParamCode;

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $paramInfo = array();

        if($paramType == "") {
            $paramType = $this->GetParamType($paramCode, $cycloApTableName, $cycloBpTableName);
        }

        $freq = 1;

        if($paramType == PARAM_TYPE_AP) {
            $query = "SELECT * FROM `".$cycloApTableName."` WHERE `code` = '".$paramCode."' LIMIT 1;";
            $result = $link->query($query);
            $row = $result->fetch_array();

            if(strpos($row['channel'], ",")) {
                $freq = explode(",", $row['channel']);
                $freq = array_map("trim", $freq);
                $freq = array_filter($freq);
                $freq = count($freq);
            }

            $paramInfo = array("id" => $row['id'],
                "code" => $row['code'],
                "channel" => $row['channel'],
                "k" => $row['k'],
                "dim" => $row['dim'],
                "name" => $row['name'],
                "minValue" => $row['minValue'],
                "maxValue" => $row['maxValue'],
                "color" => $row['color'],
                "type" => $row['type'],
                "prefix" => $row['prefix'],
                "mask" => $row['mask'],
                "minus" => $row['minus'],
                "shift" => $row['shift'],
                "freq" => $freq,
                "paramType" => PARAM_TYPE_AP);
        } else if($paramType == PARAM_TYPE_BP) {
            $query = "SELECT * FROM `".$cycloBpTableName."` WHERE `code` = '".$paramCode."' LIMIT 1;";
            $result = $link->query($query);
            $row = $result->fetch_array();

            if(strpos(",", $row['channel'])) {
                var_dump($row['channel']);
                $freq = explode(",", $row['channel']);
                $freq = array_map("trim", $freq);
                $freq = array_filter($freq);
                $freq = count($freq);
            }

            $paramInfo = array("id" => $row['id'],
                "code" => $row['code'],
                "channel" => $row['channel'],
                "name" => $row['name'],
                "type" => $row['type'],
                "prefix" => $row['prefix'],
                "freq" => $freq,
                "mask" => $row['mask'],
                "basis" => $row['basis'],
                "color" => $row['color'],
                "paramType" => PARAM_TYPE_BP);
        }

        $c->Disconnect();
        unset($c);
        return $paramInfo;
    }

    public function GetParamNames($extBruType, $extParamCodeArr)
    {
        $bruType = $extBruType;
        $paramCodeArr = $extParamCodeArr;
        $paramCodeArrImploded = implode("','", $paramCodeArr);
        $paramCodeArrImploded = "'" . $paramCodeArrImploded . "'";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $paramInfo = array();

        $query = "SELECT `gradiApTableName`, `gradiBpTableName` FROM `brutypes` WHERE `bruType` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $names = "";

        $query = "SELECT `code`, `name`, `dim` FROM `".$cycloApTableName."` WHERE `code` ".
                "IN (".$paramCodeArrImploded.") " .
                "ORDER BY `code`;";
        $result = $link->query($query);
        while($row = $result->fetch_array())
        {
            $names .= $row['code'] . " : " . $row['name'] . "(" . $row['dim'] . ") \n ";
        }

        $query = "SELECT `code`, `name` FROM `".$cycloBpTableName."` WHERE `code` ".
                "IN (".$paramCodeArrImploded.") " .
                "ORDER BY `code`;";
        $result = $link->query($query);
        while($row = $result->fetch_array())
        {
            $names .= $row['code'] . " : " . $row['name'] ."\n ";
        }

        $c->Disconnect();
        unset($c);

        return $names;
    }

    public function UpdateParamColor($extParamTable, $extParamCode, $extColor)
    {
        $paramTable = $extParamTable;
        $paramCode = $extParamCode;
        $color = $extColor;

        $query = "UPDATE `".$paramTable."` SET `color` = '".$color."' WHERE `code` = '".$paramCode."';";

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

    public function GetParamColor($extParamTable, $extParamCode)
    {
        $paramTable = $extParamTable;
        $paramCode = $extParamCode;

        $query = "SELECT `color` FROM `".$paramTable."` WHERE `code` = '".$paramCode."' LIMIT 1;";

        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();
        $color = $row['color'];

        $c->Disconnect();
        unset($c);

        return $color;
    }

    public function GetBrutypesByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `id` FROM `brutypes` WHERE `author` = '".$author."';";
        $mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

        $list = array();
        while($row = $mySqliResult->fetch_array())
        {
            $item = $this->GetBruInfoById($row['id']);
            array_push($list, $item);
        }
        $mySqliResult->free();
        $c->Disconnect();

        unset($c);

        return $list;
    }

    public function DeleteBrutypesByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `brutypes` WHERE `author` = '".$author."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }


}



?>
