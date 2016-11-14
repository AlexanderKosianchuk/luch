<?php

require_once(@SITE_ROOT_DIR ."/includes.php");

class UserOptions
{

    private static $defaultOptions = [
            'printTableStep' => 1,
            'mainChartColor' => 'fff',
            'lineWidth' => 1,
    ];

    public function CreateUserOptionssTables()
    {
        $query = "SHOW TABLES LIKE 'user_settings';";
        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `user_settings` (
                `id` BIGINT NOT NULL AUTO_INCREMENT,
                `user_id` INT,
                `name` VARCHAR(200),
                `value` VARCHAR(200),
                `dt_cr` DATETIME DEFAULT CURRENT_TIMESTAMP
                `dt_up` DATETIME ON UPDATE CURRENT_TIMESTAMP
                PRIMARY KEY (`id`));";
            $stmt = $link->prepare($query);
            if (!$stmt->execute())
            {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }

        $c->Disconnect();
        unset($c);

        return;
    }

    public function InsertOption($key, $val, $userId)
    {
        $query = "INSERT INTO `user_settings` (`user_id`, `name`, `value`)" .
                "VALUES ('".$userId."', '".$key."', '".$val."');";

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        return;
    }

    public function InsertDefaultOptions($userId)
    {
        foreach (self::$defaultOptions as $key => $val) {
            $this->InsertOption($key, $val, $userId);
        }

        return;
    }

    public function GetOptions($userId)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `name`,`value` FROM `user_settings` WHERE `user_id`=".$userId.";");

        $arr = [];
        while($row = $result->fetch_array()) {
            $arr[$row['name']] = $row['value'];
        }

        $c->Disconnect();
        unset($c);

        if(count($arr) == 0) {
            $this->InsertDefaultOptions($userId);
            $arr = self::$defaultOptions;
        }

        return $arr;
    }

    public function GetOptionValue($userId, $optionName)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `value` FROM `user_settings` WHERE `user_id`=".$userId." AND `name`='".$optionName."' LIMIT 1;");

        $value = null;
        if($row = $result->fetch_array()) {
            $value = $row['value'];
        } else {
            $value = self::$defaultOptions[$optionName];
        }

        $c->Disconnect();
        unset($c);

        return $value;
    }

    public function UpdateOption($optionsKey, $optionsVal, $userId)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "UPDATE `user_settings` SET `value` = '".$optionsVal."' WHERE `name` = '".$optionsKey."' AND `user_id` = ".$userId.";";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return;
    }

    public function UpdateOptions($options, $userId)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $oldOptions = $this->GetOptions($userId);

        foreach($options as $key => $val) {
            if(isset($oldOptions[$key])) {
                if($oldOptions[$key] != $val) {
                    $this->UpdateOption($key, $val, $userId);
                }
            }
        }

        return;
    }
}
