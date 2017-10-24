<?php

namespace Model;

class Flight
{
    public function PrepareFlightsList($extAvailableFlightIds)
    {
        $availableFlightIds = $extAvailableFlightIds;

        $listFlights = (array)$this->GetFlights($availableFlightIds);
        $i = 0;
        $flightsListInfo = array();

        while($i < count($listFlights))
        {
            $flight = (array)$listFlights[$i];
            $flightInfo = $flight;

            $flightInfo['exceptionsSearchPerformed'] = false;
            if($flight['exTableName'] != "")
            {
                $flightInfo['exceptionsSearchPerformed'] = true;
            }

            $flightInfo['cellNum'] = $flight['id'];
            $flightInfo['uploadDate'] = date('H:i:s Y-m-d', $flight['uploadingCopyTime']);
            $flightInfo['flightDate'] = date('H:i:s Y-m-d', $flight['startCopyTime']);

            $i++;
            array_push($flightsListInfo, $flightInfo);
        }
        return $flightsListInfo;
    }

    public function CreateFlightParamTables($extFlightId, $extCycloAp, $extCycloBp)
    {
        $flightId = $extFlightId;
        $cycloAp = $extCycloAp;
        $cycloBp = $extCycloBp;

        $flightInfo = $this->GetFlightInfo($flightId);
        $tableNameAp = $flightInfo["apTableName"];
        $tableNameBp = $flightInfo["bpTableName"];
        $apTables = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();
        foreach($cycloAp as $prefix => $prefixCyclo)
        {
            array_push($apTables, $tableNameAp."_".$prefix);
            $query = "CREATE TABLE `".$tableNameAp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT";

            for($i = 0; $i < count($prefixCyclo); $i++) {
                $query .= ", `".$prefixCyclo[$i]["code"]."` FLOAT(7,2)";
            }

            $query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
                    "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }

        foreach($cycloBp as $prefix => $prefixCyclo)
        {
            $query = "CREATE TABLE `".$tableNameBp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT, `code` varchar(255)) " .
                "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $apTables;
    }

    public function UpdateFlightInfo($extFlightId, $extFlightInfo)
    {
        $flightId = $extFlightId;
        $flightInfo = $extFlightInfo;
        foreach($flightInfo as $key => $value) {
            $c = new DataBaseConnector;
            $link = $c->Connect();

            $query = "UPDATE `flights` SET `".
                            $key."`='".$value."'
                            WHERE id='".$flightId."';";
            $stmt = $link->prepare($query);
            $stmt->execute();
            $stmt->close();

            $c->Disconnect();
            unset($c);
        }
    }

    public function GetFlightsByFilter($filter)
    {
        $query = "SELECT `id` FROM `flights` WHERE ";

        foreach ($filter as $key => $val) {
            if($key === 'from') {
                $query .= "(`startCopyTime` > ".$val.") AND ";
            } else if($key === 'to') {
                $query .= "(`startCopyTime` < ".$val.") AND ";
            } else {
                $query .= "(`".$key."` LIKE '%".$val."%') AND ";
            }
        }

        $query = substr($query, 0, -4);
        $query .= ";";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $arr = [];
        while($row = $result->fetch_array()) {
            $arr[] = $row['id'];
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $arr;
    }

    public function DeleteFlight($flightId, $prefixes)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($flightId), 1);
        }

        $flightInfo = $this->GetFlightInfo($flightId);
        $file = $flightInfo['fileName'];
        $guid = $flightInfo['guid'];

        $result = array();
        $result['status'] = array();
        $result['query'] = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `flights` WHERE id=".$flightId.";";
        $result['query'][] = $query;
        $stmt = $link->prepare($query);
        $result['status'][] = $stmt->execute();
        $stmt->close();

        foreach($prefixes as $item => $prefix)
        {
            $tableName =  $guid . $prefix;
            $query = "SHOW TABLES LIKE '". $tableName ."';";
            $res = $link->query($query);
            if (count($res->fetch_array()))
            {
                $query = "DROP TABLE `". $tableName ."`;";
                $result['query'][] = $query;
                $stmt = $link->prepare($query);
                $result['status'][] = $stmt->execute();
                $stmt->close();
            }
        }

        $c->Disconnect();

        unset($c);

        if(file_exists($file))
        {
            unlink($file);
        }

        if(in_array(false, $result['status']))
        {
            $result['status'] = false;
        }
        else
        {
            $result['status'] = true;
        }

        return $result;
    }

    public function DropTable($extTableName)
    {
        $tableName = $extTableName;

        $c = new DataBaseConnector;

        $query = "DROP TABLE `". $tableName ."`;";

        $link = $c->Connect();
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function GetMaxFlightId()
    {
        $query = "SELECT MAX(`id`) FROM `flights` WHERE 1;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);
        $maxId = 1;
        if($row = $result->fetch_array())
        {
            $maxId = $row['MAX(`id`)'];
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $maxId;

    }
}

?>
