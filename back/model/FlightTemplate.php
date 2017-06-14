<?php

namespace Model;

class FlightTemplate
{
    public static $LAST_TPL_NAME = 'last';
    public static $EVENTS_TPL_NAME = 'events';
    public static $TPL_DEFAULT =  'default';

    public static $TABLE_PREFIX = '_pst';

    public function CreatePSTTable($PSTTableName)
    {
        $query = "SHOW TABLES LIKE '".$PSTTableName."';";
        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE IF NOT EXISTS `".$PSTTableName."` (
                `id` MEDIUMINT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(20),
                `paramCode` VARCHAR(20),
                `isDefault` BOOLEAN NOT NULL DEFAULT 0,
                `minYaxis` FLOAT DEFAULT NULL,
                `maxYaxis` FLOAT DEFAULT NULL,
                `user` VARCHAR(200) DEFAULT '',
                PRIMARY KEY (id))";

            $stmt = $link->prepare($query);
            $stmt->execute();
            $stmt->close();
        }
        $c->Disconnect();
        unset($c);
    }

    public function AddPSTTable($fdrId, $PSTTableName)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `fdrs`
            SET paramSetTemplateListTableName = '".
            $PSTTableName."' WHERE id='".$fdrId."';";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();
        $c->Disconnect();

        unset($c);
    }

    public function GetPSTList($PSTListTableName, $user)
    {
        $PSTList = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $link2 = $c->Connect();

        $query = "SELECT DISTINCT `name` FROM `".$PSTListTableName."` WHERE `user` = '".$user."';";
        $result = $link->query($query);
        while ($row = $result->fetch_array()) {
            $templateName = $row['name'];
            $query = "SELECT `isDefault`, `paramCode` FROM `".$PSTListTableName."` WHERE (`name` = ?) AND (`user` = ?);";
            $stmt = $link2->prepare($query);
            $stmt->bind_param('ss', $templateName, $user);

            $stmt->execute();
            $stmt->bind_result($isDefault, $paramCode);
            $paramCodeList = array();
            while ($stmt->fetch()) {
                array_push($paramCodeList, $paramCode);
            }
            $PSTRow = [
                0 => $templateName,
                1 => $paramCodeList,
                2 => boolval($isDefault),
                'name' => $templateName,
                'params' => $paramCodeList,
                'isDefault' => boolval($isDefault)
            ];
            array_push($PSTList, $PSTRow);
        }

        $c->Disconnect();
        unset($c);

        return $PSTList;
    }

    public function GetDefaultPST($extPSTListTableName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $user = $extUser;
        $PSTList = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT DISTINCT `name` FROM `".$PSTListTableName."` WHERE `isDefault` = 1 " .
            "AND `user` = '".$user."';";
        $result = $link->query($query);
        $templateName = '';
        if($row = $result->fetch_array())
        {
            $templateName = $row['name'];
        }
        $c->Disconnect();
        unset($c);

        return $templateName;
    }


    public function AddParamToTemplate($extPSTTableName,
        $extTemplateName,$extParamName, $extUser)
    {
        $PSTTableName = $extPSTTableName;
        $templateName = $extTemplateName;
        $paramName = $extParamName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `name`, `paramCode`
            FROM `".$PSTTableName."`
            WHERE ((`name` = '".$templateName."')
            AND (`paramCode` = '".$paramName."')
            AND (`user` = '".$user."'));";
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "INSERT INTO `".$PSTTableName."`
            (`name`, `paramCode`, `user`)
            VALUES ('".$templateName."','".$paramName."','".$user."');";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }
        $c->Disconnect();
        unset($c);
    }

    public function AddParamToTemplateWithMinMax($extPSTTableName,
            $extTemplateName, $extParamName, $extAxisMin, $extAxisMax, $extUser)
    {
        $PSTTableName = $extPSTTableName;
        $templateName = $extTemplateName;
        $paramName = $extParamName;
        $axisMin = $extAxisMin;
        $axisMax = $extAxisMax;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `name`, `paramCode`
            FROM `".$PSTTableName."`
            WHERE ((`name` = '".$templateName."')
            AND (`paramCode` = '".$paramName."')
            AND (`minYaxis` = '".$axisMin."')
            AND (`maxYaxis` = '".$axisMax."')
            AND (`user` = '".$user."'));";
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "INSERT INTO `".$PSTTableName."`
            (`name`, `paramCode`, `minYaxis`, `maxYaxis`, `user`)
            VALUES ('".$templateName."','".$paramName."', '".$axisMin."', '".$axisMax."', '".$user."');";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }
        $c->Disconnect();
        unset($c);
    }

    public function GetPSTByName($tableName, $templateName, $user)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `paramCode` FROM `".$tableName."` WHERE (`name` = ? AND `user` = ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param('ss', $templateName, $user);
        $stmt->execute();
        $stmt->bind_result($paramCode);
        $paramCodeList = array();
        while ($stmt->fetch())
        {
            array_push($paramCodeList, $paramCode);
        }

        $c->Disconnect();
        unset($c);

        return $paramCodeList;
    }

    public function getTemplate($tableName, $templateName, $user)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id`, `name`, `paramCode`, `isDefault`, `minYaxis`, `maxYaxis`, `user` "
            . "FROM `".$tableName."` WHERE (`name` = ? AND `user` = ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param('ss', $templateName, $user);
        $stmt->execute();
        $stmt->bind_result($id, $name, $paramCode, $isDefault, $minYaxis, $maxYaxis, $user);
        $template = array();
        while ($stmt->fetch())
        {
            array_push($template, [
                'id' => $id,
                'name' => $name,
                'paramCode' => $paramCode,
                'isDefault' => $isDefault,
                'minYaxis' => $minYaxis,
                'maxYaxis' => $maxYaxis,
                'user' => $user
            ]);
        }

        $c->Disconnect();
        unset($c);

        return $template;
    }

    public function GetPSTParams($PSTListTableName, $PSTName, $user)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE (`name` = ? AND `user` = ?);";
        $stmt = $link->prepare($query);
        $stmt->bind_param('ss', $PSTName, $user);
        $stmt->execute();
        $stmt->bind_result($paramCode);
        $paramCodeList = array();
        while ($stmt->fetch())
        {
            array_push($paramCodeList, $paramCode);
        }

        $c->Disconnect();
        unset($c);

        return $paramCodeList;
    }

    public function GetAllPSTParams($extPSTListTableName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT DISTINCT `paramCode` FROM `".
            $PSTListTableName."` WHERE `user` = '".$user."';";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->bind_result($paramCode);
        $paramCodeList = array();
        while ($stmt->fetch())
        {
            array_push($paramCodeList, $paramCode);
        }

        $c->Disconnect();
        unset($c);

        return $paramCodeList;
    }

    public function DeleteTemplate($tableName, $tplName, $user)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `".$tableName."` WHERE `name` = '".$tplName."' AND `user` = '".$user."';";
        $link->query($query);
        $c->Disconnect();
        unset($c);
    }

    public function SetDefaultTemplate($extPSTListTableName, $extTemplateName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $tplName = $extTemplateName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$PSTListTableName."` SET `isDefault` = '0' WHERE `user` = '".$user."';";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $query = "UPDATE `".$PSTListTableName."` SET `isDefault` = '1'
            WHERE `name` ='".$tplName."' AND `user` = '".$user."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function GetDefaultTemplateParams($extPSTListTableName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE `isDefault` = '1' ".
                "AND `user` = '".$user."';";
        $result = $link->query($query);
        $paramCodeList = array();
        while($row = $result->fetch_array())
        {
            array_push($paramCodeList, $row['paramCode']);
        }

        $c->Disconnect();
        unset($c);

        return $paramCodeList;
    }

    public function GetDefaultTemplateName($extPSTListTableName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT DISTINCT (`name`) FROM `".$PSTListTableName."` WHERE `isDefault` = '1' AND `user` = '".$user."';";
        $result = $link->query($query);
        $name = ""; //if no default try to use last
        if($row = $result->fetch_array())
        {
            $name =  $row['name'];
        }

        $c->Disconnect();
        unset($c);

        return $name;
    }

    public function GetLastTemplateName($extPSTListTableName, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT DISTINCT (`name`) FROM `".$PSTListTableName."` WHERE `name` = 'last' AND `user` = '".$user."';";
        $result = $link->query($query);
        $name = ""; //if no default try to use last
        if($row = $result->fetch_array())
        {
            $name =  $row['name'];
        }

        $c->Disconnect();
        unset($c);

        return $name;
    }

    public function UpdateParamMinMax($extPSTListTableName, $extTemplateName, $extParamCode,
            $extMin, $extMax, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $tplName = $extTemplateName;
        $paramCode = $extParamCode;
        $min = $extMin;
        $max = $extMax;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$PSTListTableName."`SET `minYaxis` = '".$min."', `maxYaxis` = '".$max."'
                WHERE `name` = '".$tplName."' AND  `paramCode` = '".$paramCode."' AND `user` = '".$user."';";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function GetParamMinMax($extPSTListTableName, $extTemplateName, $extParamCode, $extUser)
    {
        $PSTListTableName = $extPSTListTableName;
        $tplName = $extTemplateName;
        $paramCode = $extParamCode;
        $user = $extUser;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `minYaxis`, `maxYaxis` FROM `".$PSTListTableName."` " .
                "WHERE `name` = '".$tplName."' AND  `paramCode` = '".$paramCode."' ".
                "AND `user` = '".$user."';";

        $result = $link->query($query);
        $minMax = "";
        if($row = $result->fetch_array())
        {
            $minMax = array(
                'min' => $row['minYaxis'],
                'max' => $row['maxYaxis']
                );
        }

        $c->Disconnect();
        unset($c);

        return $minMax;
    }

    public function CreateTplWithDistributedParams(
        $PSTTableName,
        $tplName,
        $paramsWithType,
        $username
    ) {
        $apCount = count($paramsWithType[PARAM_TYPE_AP]);

        for($i = 0; $i < count($paramsWithType[PARAM_TYPE_AP]); $i++)
        {
            $paramCode = $paramsWithType[PARAM_TYPE_AP][$i]['code'];
            $yMax = $paramsWithType[PARAM_TYPE_AP][$i]['max'];
            $yMin = $paramsWithType[PARAM_TYPE_AP][$i]['min'];
            $curCorridor = 0;

            if(($i == 0) && ($yMax > 1)){
                $yMax += $yMax * 0.15;//prevent first(top) param out chart boundary
            }

            if($yMax == $yMin) {
                $yMax += 0.001; //if $yMax == $yMin parameter builds as straight line in bottom of chart
            }

            if($yMax > 0) {
                $curCorridor = (($yMax - $yMin) * 1.05);
            } else {
                $curCorridor = -(($yMin - $yMax) * 1.05);
            }

            $axisMax = $yMax + ($i * $curCorridor);
            $axisMin = $yMin - (($apCount - $i) * $curCorridor);

            $this->AddParamToTemplateWithMinMax($PSTTableName,
                $tplName, $paramCode, $axisMin, $axisMax, $username);
        }

        if(isset($paramsWithType[PARAM_TYPE_BP]))
        {
            $busyCorridor = (($apCount -1) / $apCount * 100);
            $freeCorridor = 100 - $busyCorridor;//100%

            $bpCount = count($paramsWithType[PARAM_TYPE_BP]);
            $curCorridor = $freeCorridor / $bpCount;
            $j = 0;

            for($i = $apCount; $i < $apCount + $bpCount; $i++)
            {
                $axisMax = 100 - ($curCorridor * $j);
                $axisMin = 0 - ($curCorridor * $j);

                $this->AddParamToTemplateWithMinMax($PSTTableName,
                    $tplName, $paramsWithType[PARAM_TYPE_BP][$j]['code'],
                    $axisMin, $axisMax, $username);
                $j++;
            }
        }

        return false;
    }

    public function createTemplate($templateName, $templateItems, $tableName, $username)
    {
        $this->DeleteTemplate($tableName, $templateName, $username);

        $c = new DataBaseConnector;
        $link = $c->Connect();

        foreach ($templateItems as $item) {
            $query = "INSERT INTO `".$tableName."` "
                . "(`name`, `paramCode`, `minYaxis`, `maxYaxis`, `user`) "
                . "VALUES ('".$templateName."','".$item['paramCode']."', '".$item['minYaxis']."', '".$item['maxYaxis']."', '".$username."');";

            $stmt = $link->prepare($query);
            $stmt->execute();
        }

        $c->Disconnect();
        unset($c);
    }
}
