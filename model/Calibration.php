<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Calibration
{
    private $table = 'calibration';

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

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $q = "SELECT `id`, `name`, `id_fdr`, `id_user`, `dt_created`, `dt_updated`"
            ." FROM `".$this->table."`"
            ." WHERE `id_fdr` = ? AND `id_user` = ?;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("ii", $fdrId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $calibrations = [];
        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $calibrationId[$key] = $value;
            }
        }

        return $calibrations;
    }
}
