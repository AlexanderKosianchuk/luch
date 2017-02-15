<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class CalibrationController extends CController
{
    public $curPage = 'calibrationPage';

    public function getAvaliableFdrs($data)
    {
        $userId = intval($this->_user->userInfo['id']);
        $avaliablefdrIds = $this->_user->getAvailableFdrs($userId);

        $fdr = new Bru();
        $fdrInfoList = $fdr->getFdrList($avaliablefdrIds);

        $fdrsWithCalibration = [];
        foreach ($fdrInfoList as $fdrInfo) {
            $calibrationParamsExist = $fdr->checkCalibrationParamsExist(intval($fdrInfo['id']));

            if ($calibrationParamsExist) {
                $fdrsWithCalibration[] = $fdrInfo;
            }
        }

        $fdrsAndCalibrations = [];
        $calibration = new Calibration();
        foreach ($fdrsWithCalibration as $fdrInfo) {
            $fdrId = intval($fdrInfo['id']);
            $fdrCode = $fdrInfo['code'];
            $calibrationDynamicTable = $calibration->getTableName($fdrCode);
            $fdrCalibrations = $calibration->getCalibrations($fdrId, $userId);
            $calibratedParams = $fdr->getCalibratedParams($fdrId);

            foreach ($fdrCalibrations as &$fdrCalibration) {
                $calibrationCalibratedParams = [];
                $calibrationId = intval($fdrCalibration['id']);

                $params = [];
                foreach ($calibratedParams as $param) {
                    $paramId = $param['id'];
                    $paramCalibration = $calibration->getCalibrationParam ($calibrationDynamicTable, $calibrationId, $paramId);
                    $paramInfo = $fdr->GetParamInfoById($fdr->getApTableName($fdrId), $paramId);
                    $calibrationCalibratedParams[] = array_merge(
                        $paramInfo, $paramCalibration
                    );
                }

                $fdrCalibration['calibratedParams'] = $calibrationCalibratedParams;
            }

            $fdrsAndCalibrations[] = [
                'id' => intval($fdrInfo['id']),
                'name' => $fdrInfo['bruType'],
                'calibrations' => $fdrCalibrations,
                'calibratedParams' => $calibratedParams
            ];
        }

        echo json_encode($fdrsAndCalibrations);
    }

    public function saveCalibration($data)
    {
        $userId = intval($this->_user->userInfo['id']);
        $fdrId = intval($data['fdrId']);
        $calibrationsName = $data['name'];
        $calibrations = $data['calibrations'];

        $calibrationId = null;
        if (isset($data['calibrationId'])
            && !empty($data['calibrationId'])
            && is_int(intval($data['calibrationId']))
        ) {
            $calibrationId = intval($data['calibrationId']);
        }

        $isAvaliable = $this->_user->checkFdrAvailable($fdrId, $userId);

        if (!$isAvaliable) {
            header('HTTP/1.0 403 Forbidden');
            echo 'FDR is not avaliable for current user.';
            exit;
        }

        $fdr = new Bru();
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $fdrCode = $fdrInfo['code'];
        unset($fdr);

        $calibration = new Calibration();
        $calibrationDynamicTable = $calibration->createTable($fdrCode);

        if ($calibrationId === null) {
            $calibration->createCalibration($calibrationDynamicTable,
                $fdrId,
                $userId,
                $calibrationsName,
                $calibrations
            );
        } else {
            $calibration->updateCalibration($calibrationDynamicTable,
                $calibrationId,
                $userId,
                $calibrationsName,
                $calibrations
            );
        }

        unset($calibration);

        echo true;
    }

    public function deleteCalibration($data)
    {
        $userId = intval($this->_user->userInfo['id']);
        $calibrationId = intval($data['calibrationId']);

        $calibration = new Calibration();
        $calibrationInfo = $calibration->getCalibrationById ($calibrationId, $userId);

        if (empty($calibrationInfo)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Calibration unexist.';
            exit;
        }

        $fdrId = intval($calibrationInfo['id_fdr']);
        $calibrationId = intval($calibrationInfo['id']);

        $isAvaliable = $this->_user->checkFdrAvailable($fdrId, $userId);

        if (!$isAvaliable) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Trying to remove calibration for FDR that is not avaliable for current user.';
            exit;
        }

        $calibration->deleteCalibration ($calibrationId, $userId);

        $fdr = new Bru();
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $fdrCode = $fdrInfo['code'];
        unset($fdr);

        $calibrationDynamicTable = $calibration->getTableName($fdrCode);

        if($calibration->checkTableExist ($calibrationDynamicTable)) {
            $calibration->deleteCalibrationParams ($calibrationDynamicTable, $calibrationId);
        }

        echo true;
    }
}
