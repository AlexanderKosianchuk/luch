<?php

namespace Model;

use Exception;

class Fdr
{
    private $table = 'fdrs';
    private $apPrefix = '_ap';
    private $bpPrefix = '_bp';

    public function CreateBruTypeTable()
    {
        $query = "SHOW TABLES LIKE '".$this->table."';";
        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `".$this->table."` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255),
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

    public function getApTableName($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $fdrInfo = $this->getFdrInfo($fdrId);

        return $fdrInfo['code'].$this->apPrefix;
    }

    public function getBpTableName($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $fdrInfo = $this->getFdrInfo($fdrId);

        return $fdrInfo['code'].$this->bpPrefix;
    }

    public function getFdrList($availableIds)
    {
        return $this->GetBruList($availableIds);
    }

    public function GetBruList($availableBruTypesIds)
    {
        $bruList = array();
        if(count($availableBruTypesIds) > 0)
        {
            $inString = "";
            foreach($availableBruTypesIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $c = new DataBaseConnector;
            $link = $c->Connect();

            $query = "SELECT * FROM `".$this->table."` WHERE `id` IN (".$inString.") ORDER BY `id`;";
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $fdrInfo = $this->getFdrInfo(intval($row['id']));
                array_push($bruList, $fdrInfo);
            }

            $result->free();
            $c->Disconnect();

            unset($c);
        }

        return $bruList;
    }

    public function getFdrInfo($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT * "
            ." FROM `".$this->table."`"
            ." WHERE `id` = ? LIMIT 1;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("i", $fdrId);
        $stmt->execute();
        $result = $stmt->get_result();

        $fdrInfo = [];
        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $fdrInfo[$key] = $value;
            }
        }

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $fdrInfo;
    }

    public function GetBruInfo($bruType)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT * FROM `".$this->table."` WHERE `code` = '".$bruType."' LIMIT 1;";

        $result = $link->query($query);
        $row = $result->fetch_array();

        $fdrInfo = array();
        foreach ($row as $key => $value) {
            $fdrInfo[$key] = $value;
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $fdrInfo;
    }

    public function GetBruInfoById($extBruTypeId)
    {
        $bruTypeId = $extBruTypeId;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT * FROM `".$this->table."` WHERE `id` = '".$bruTypeId."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $fdrInfo = array();
        foreach ($row as $key => $value)
        {
            $fdrInfo[$key] = $value;
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $fdrInfo;
    }

    public function getFDRapCyclo($fdrId, $startIndex = 0, $pageSize = 50, $sorting = 'ASC')
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $fdrInfo = $this->getFdrInfo($fdrId);
        $apTable = $fdrInfo['code'].$this->apPrefix;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT * "
            ." FROM `".$apTable."`"
            ." WHERE 1"
            ." ORDER BY `id` " . $sorting;

        if ($startIndex !== null) {
            $q .= " LIMIT ? , ?";
        }
        $q .= ";";

        $stmt = $link->prepare($q);
        if ($startIndex !== null) {
            $stmt->bind_param("ii", $startIndex, $pageSize);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $cyclo = [];
        while($row = $result->fetch_array()) {
            $cyclo[] = $row;
        }

        $result->free();
        $stmt->close();

        $c->Disconnect();
        unset($c);
        return $cyclo;
    }

    public function GetBruApCyclo($extBruType, $extJtStartIndex, $extJtPageSize, $extJtSorting)
    {
        $bruType = $extBruType;
        $jtStartIndex = $extJtStartIndex;
        $jtPageSize = $extJtPageSize;
        $jtSorting = $extJtSorting;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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
            if ($row['xy'] !== null) {
                $gradiParam['xy'] = json_decode($gradiParam['xy'], true);
            }
            array_push($cycloAp, $gradiParam);
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $cycloAp;
    }

    public function GetBruApCycloParam($extBruType, $extParamId)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT * FROM `".$cycloApTableName."` WHERE `id`=".$paramId.";";

        $result = $link->query($query);

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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $result->free();
        $c->Disconnect();

        unset($c);

        return $rowsCount;
    }

    public function GetBruApGradi($extBruType, $extParamId)
    {
        $bruType = $extBruType;
        $paramId = $extParamId;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT `xy` FROM `".$cycloApTableName."` WHERE `id`=".$paramId.";";
        $result = $link->query($query);

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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

    public function GetBruApCycloPrefixOrganized($fdrId)
    {
        $c = new DataBaseConnector;

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `id` = ".$fdrId." LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array()) {
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

    public function GetBruApCycloPrefixFreq($fdrId)
    {
        $c = new DataBaseConnector;
        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `id` = ".$fdrId." LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while($row = $result->fetch_array()) {
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

    public function GetBruApCycloPrefixes($fdrId)
    {
        $c = new DataBaseConnector;
        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `id` = ".$fdrId." LIMIT 1;";
        $link = $c->Connect();
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $query = "SELECT DISTINCT(`prefix`) FROM `".$cycloApTableName."` ORDER BY `prefix`;";
        $result = $link->query($query);

        $prefixesArr = array();
        while ($row = $result->fetch_array()) {
            array_push($prefixesArr, $row['prefix']);
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $prefixesArr;
    }

    public function GetBruBpCycloPrefixes($fdrId)
    {
        $c = new DataBaseConnector;
        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `id` = ".$fdrId." LIMIT 1;";
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

    public function GetBruBpCycloPrefixOrganized($fdrId)
    {
        $c = new DataBaseConnector;

        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `id` = '".$fdrId."' LIMIT 1;";
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

    public function GetBruBpCycloPrefixFreq($fdrId)
    {
        $c = new DataBaseConnector;
        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `id` = ".$fdrId." LIMIT 1;";
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

    public function GetBruApHeaders($extBruType)
    {
        $bruType = $extBruType;
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

    public function GetBruApHeadersByRequest($extBruType, $request)
    {
        $bruType = $extBruType;
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloApTableName = $row['gradiApTableName'];
        $result->free();

        $apHeader = array();

        $query = "SELECT * FROM `".$cycloApTableName."` "
                . " WHERE `code` LIKE '%" . $request . "%' OR `name` LIKE '%" . $request . "%' OR `channel` LIKE '%" . $request . "%';";

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
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiBpTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
        $result = $link->query($query);
        $row = $result->fetch_array();

        $cycloBpTableName = $row['gradiBpTableName'];
        $result->free();

        $bpHeader = array();

        $query = "SELECT * FROM `".$cycloBpTableName."` ".
                "WHERE `code` LIKE '%" . $request . "%' OR  `name` LIKE '%" . $request . "%' OR `channel` LIKE '%" . $request . "%';";

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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `gradiApTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

    public function GetParamType($paramCode,
        $cycloApTableName, $cycloBpTableName)
    {
        $paramType = "null";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $query = "SELECT `id` FROM `".$cycloApTableName."` WHERE (`code` = ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param('s', $paramCode);
        $stmt->execute();
        $stmt->bind_result($id);
        while ($stmt->fetch()) {
            $paramType = PARAM_TYPE_AP;
        }
        $stmt->close();

        //if param not in Ap look in gradiBpTable
        if($paramType != PARAM_TYPE_AP) {
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

        $c = new DataBaseConnector;
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

    public function GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode, $paramType = "")
    {
        $c = new DataBaseConnector;
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

            if(strpos($row['channel'], ',')) {
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

    public function GetParamInfoById($tableName, $paramId)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();
        $paramInfo = array();

        $query = "SELECT * FROM `".$tableName."` WHERE `id` = ? LIMIT 1;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_array()) {
            $paramInfo = $row;
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

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $paramInfo = array();

        $query = "SELECT `gradiApTableName`, `gradiBpTableName` FROM `".$this->table."` WHERE `name` = '".$bruType."' LIMIT 1;";
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

        $c = new DataBaseConnector;
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

        $c = new DataBaseConnector;
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id` FROM `".$this->table."` WHERE `author` = '".$author."';";
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

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `".$this->table."` WHERE `author` = '".$author."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function checkCalibrationParamsExist($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $cyclo = $this->getFDRapCyclo($fdrId, null);

        for ($ii=0; $ii < count($cyclo); $ii++) {
            if ($cyclo[$ii]['xy'] != '') {
                return true;
            }
        }

        return false;
    }

    public function getCalibratedParams ($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        $cyclo = $this->getFDRapCyclo($fdrId, null);

        $calibratedParams = [];
        for ($ii=0; $ii < count($cyclo); $ii++) {
            if ($cyclo[$ii]['xy'] != null) {
                $cyclo[$ii]['xy'] = json_decode($cyclo[$ii]['xy'], true);
                $calibratedParams[] = $cyclo[$ii];
            }
        }

        return $calibratedParams;
    }

}
