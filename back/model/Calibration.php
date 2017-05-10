<?php

namespace Model;

use Exception;

class Calibration
{
    private $table = 'calibrations';
    private $prefix = '_c';

    public function getCalibrations ($fdrId, $userId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT `id`, `name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`"
            ." FROM `".$this->table."`"
            ." WHERE `id_fdr` = ? AND `id_user` = ?;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("ii", $fdrId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $calibrations = [];
        while ($row = $result->fetch_array()) {
            $calibrations[] = $row;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $calibrations;
    }

    public function getCalibrationsForFdrs ($fdrIds, $userId)
    {
        if (!is_array($fdrIds)) {
            throw new Exception("Incorrect fdrIds passed. Array expected. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $fdrIdsChecked = [];
        foreach ($fdrIds as $id) {
            if (is_int($id)) {
                $fdrIdsChecked[] = $id;
            }
        }

        $fdrIdsChecked = implode(',', $fdrIdsChecked);
        $calibrations = [];

        if (!is_array($fdrIdsChecked)) {
            return $calibrations;
        }

        $q = "SELECT `id`, `name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`"
            ." FROM `".$this->table."`"
            ." WHERE `id_fdr` IN (".$fdrIdsChecked.") AND `id_user` = ?;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_array()) {
            $calibrations[$row['id_fdr']][] = $row;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $calibrations;
    }

    public function getCalibrationById ($id, $userId)
    {
        if (!is_int($id)) {
            throw new Exception("Incorrect calibration id passed. Int expected. Passed: "
                . json_encode($id), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT `id`, `name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`"
            ." FROM `".$this->table."`"
            ." WHERE `id` = ? AND `id_user` = ? LIMIT 1;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $calibration = [];
        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $calibration[$key] = $value;
            }
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $calibration;
    }

    public function getCalibrationsByName ($name, $userId)
    {
        if (!is_string($name)) {
            throw new Exception("Incorrect calibration name passed. String expected. Passed: "
                . json_encode($name), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT `id`, `name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`"
            ." FROM `".$this->table."`"
            ." WHERE `name` = ? AND `id_user` = ? LIMIT 1;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("si", $name, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $calibration = [];
        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $calibration[$key] = $value;
            }
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $calibration;
    }

    public function setCalibration ($name, $fdrId, $userId)
    {
        if (!is_string($name)) {
            throw new Exception("Incorrect calibration name passed. String expected. Passed: "
                . json_encode($name), 1);
        }

        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }


        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "INSERT INTO  `".$this->table."` (`name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`)
                VALUES (?, ?, ?, NOW(), NOW());";

        $stmt = $link->prepare($query);
        $stmt->bind_param("sii", $name, $fdrId, $userId);
        $stmt->execute();
        $stmt->close();

        $query = "SELECT LAST_INSERT_ID() AS 'id';";
        $result = $link->query($query);
        $row = $result->fetch_array();
        $calibrationId = intval($row["id"]);

        $c->Disconnect();
        unset($c);

        return $calibrationId;
    }

    public function updateCalibrationTime ($calibrationId, $userId)
    {
        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$this->table."` SET `dt_updated` = NOW() "
            . " WHERE `id` = ?;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $calibrationId);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function updateCalibrationName ($calibrationId, $calibrationsName, $userId)
    {
        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_string($calibrationsName)) {
            throw new Exception("Incorrect calibrationsName passed. String expected. Passed: "
                . json_encode($calibrationsName), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `".$this->table."` SET `name` = ? "
            . " WHERE `id` = ?;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("si", $calibrationsName, $calibrationId);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function setCalibrationParam ($tableName, $calibrationId, $paramId, $xy)
    {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_int($paramId)) {
            throw new Exception("Incorrect paramId passed. Int expected. Passed: "
                . json_encode($paramId), 1);
        }

        if (!is_string($xy)) {
            throw new Exception("Incorrect calibration name passed. String expected. Passed: "
                . json_encode($name), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "INSERT INTO  `".$tableName."` (`id_calibration`, `id_param`, `xy`) "
            ." VALUES (?, ?, ?);";

        $stmt = $link->prepare($query);
        $stmt->bind_param("iis", $calibrationId, $paramId, $xy);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function getCalibrationParam ($tableName, $calibrationId, $paramId)
    {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_int($paramId)) {
            throw new Exception("Incorrect paramId passed. Int expected. Passed: "
                . json_encode($paramId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id_param`, `id_calibration`, `xy`"
            ." FROM `".$tableName."`"
            ." WHERE `id_calibration` = ? AND `id_param` = ? LIMIT 1;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("ii", $calibrationId, $paramId);

        $stmt->execute();
        $result = $stmt->get_result();

        $calibrationParam = [];
        if($row = $result->fetch_array()) {
            $calibrationParam = $row;

            if (isset($row['xy'])) {
                $calibrationParam['xy'] = json_decode($row['xy']);
            }
        }

        $c->Disconnect();
        unset($c);

        return $calibrationParam;
    }

    public function getCalibrationParams ($tableName, $calibrationId)
    {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id_param`, `id_calibration`, `xy`"
            ." FROM `".$tableName."`"
            ." WHERE `id_calibration` = ?;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $calibrationId);

        $stmt->execute();
        $result = $stmt->get_result();

        $calibrationParam = [];
        while ($row = $result->fetch_array()) {
            $calibrationParam[$row['id_param']] = $row;

            if (isset($row['xy'])) {
                $calibrationParam[$row['id_param']]['xy'] = json_decode($row['xy'], true);
            }
        }

        $c->Disconnect();
        unset($c);

        return $calibrationParam;
    }

    public function deleteCalibrationParams ($tableName, $calibrationId)
    {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `".$tableName."` WHERE "
            ." `id_calibration` = ?;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $calibrationId);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function deleteCalibration ($calibrationId, $userId)
    {
        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `".$this->table."` "
            ."WHERE `id` = ? AND `id_user` = ?;";

        $stmt = $link->prepare($query);
        $stmt->bind_param("ii", $calibrationId, $userId);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return true;
    }

    public function createTable ($fdrCode)
    {
        if (!is_string($fdrCode)) {
            throw new Exception("Incorrect fdrCode passed. String expected. Passed: "
                . json_encode($fdrCode), 1);
        }

        $dynamicCalibrationTable = $fdrCode . $this->prefix;
        $isExist = $this->checkTableExist ($dynamicCalibrationTable);

        if(!$isExist) {
            $c = new DataBaseConnector;
            $link = $c->Connect();
            $q = "CREATE TABLE `".$dynamicCalibrationTable."` ("
                ." `id` INT NOT NULL AUTO_INCREMENT ,"
                ." `id_calibration` INT NOT NULL ,"
                ." `id_param` INT NOT NULL ,"
                ." `xy` MEDIUMTEXT NOT NULL ,"
                ." PRIMARY KEY (`id`),"
                ." INDEX (`id_calibration`),"
                ." INDEX (`id_param`))"
                ." ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;";

            $stmt = $link->prepare($q);
            $stmt->execute();

            $c->Disconnect();
            unset($c);
        }

        return $dynamicCalibrationTable;
    }

    public function getTableName ($fdrCode)
    {
        if (!is_string($fdrCode)) {
            throw new Exception("Incorrect fdrCode passed. String expected. Passed: "
                . json_encode($fdrCode), 1);
        }

        return $fdrCode . $this->prefix;
    }

    public function checkTableExist ($tableName)
    {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SHOW TABLES LIKE '".$tableName."';";

        $stmt = $link->prepare($q);
        $stmt->execute();
        $result = $stmt->get_result();

        $isExist = false;
        if($result->fetch_array()) {
            $isExist = true;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $isExist;
    }

    public function createCalibration($tableName,
      $fdrId,
      $userId,
      $calibrationsName,
      $calibrations
    ) {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        if (!is_string($calibrationsName)) {
            throw new Exception("Incorrect calibrationsName passed. String expected. Passed: "
                . json_encode($calibrationsName), 1);
        }

        if (!is_array($calibrations)) {
            throw new Exception("Incorrect calibrations passed. String expected. Passed: "
                . json_encode($calibrations), 1);
        }

        $calibrationInfo = $this->getCalibrationsByName ($calibrationsName, $userId);

        if(empty($calibrationInfo)) {
            $id = $this->setCalibration($calibrationsName, $fdrId, $userId);
            $calibrationInfo = $this->getCalibrationById ($id, $userId);
        }

        $calibrationId = $calibrationInfo['id'];
        $this->deleteCalibrationParams ($tableName, $calibrationId);

        foreach ($calibrations as $calibration) {
            if(isset($calibration['paramId'])
                && is_int(intval($calibration['paramId']))
                && isset($calibration['points'])
                && is_array($calibration['points'])
            ) {
                $this->setCalibrationParam ($tableName,
                    $calibrationId,
                    intval($calibration['paramId']),
                    json_encode($calibration['points'])
                );
            }
        }

        return true;
    }

    public function updateCalibration($tableName,
      $calibrationId,
      $userId,
      $calibrationsName,
      $calibrations
    ) {
        if (!is_string($tableName)) {
            throw new Exception("Incorrect tableName passed. String expected. Passed: "
                . json_encode($tableName), 1);
        }

        $isExist = $this->checkTableExist ($tableName);

        if (!$isExist) {
            throw new Exception("Dynamic calibration table is not exist.", 1);
        }

        if (!is_int($calibrationId)) {
            throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
                . json_encode($calibrationId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        if (!is_string($calibrationsName)) {
            throw new Exception("Incorrect calibrationsName passed. String expected. Passed: "
                . json_encode($calibrationsName), 1);
        }

        if (!is_array($calibrations)) {
            throw new Exception("Incorrect calibrations passed. String expected. Passed: "
                . json_encode($calibrations), 1);
        }

        $calibrationInfo = $this->getCalibrationById ($calibrationId, $userId);

        if(empty($calibrationInfo)) {
            throw new Exception("Updating calibration is not exist.", 1);
        }

        $this->deleteCalibrationParams ($tableName, $calibrationId);

        foreach ($calibrations as $calibration) {
            if(isset($calibration['paramId'])
                && is_int(intval($calibration['paramId']))
                && isset($calibration['points'])
                && is_array($calibration['points'])
            ) {
                $this->setCalibrationParam ($tableName,
                    $calibrationId,
                    intval($calibration['paramId']),
                    json_encode($calibration['points'])
                );
            }
        }

        $this->updateCalibrationTime ($calibrationId, $userId);
        $this->updateCalibrationName ($calibrationId, $calibrationsName, $userId);

        return true;
    }
}
