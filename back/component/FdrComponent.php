<?php

namespace Component;

use Model\Fdr;
use Model\Calibration;
use Model\User;

use Component\EntityManagerComponent as EM;

use Exception;

class FdrComponent
{
    public static function getAvaliableFdrs($userId)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Integer is required. Passed: "
                . json_encode($userId), 1);
        }

        $user = new User;
        $avaliablefdrIds = $user->getAvailableFdrs($userId);

        $fdr = new Fdr;
        $fdrInfoList = $fdr->getFdrList($avaliablefdrIds);

        $fdrsWithCalibration = [];
        foreach ($fdrInfoList as $fdrInfo) {
            $calibrationParamsExist = $fdr->checkCalibrationParamsExist(intval($fdrInfo['id']));

            if ($calibrationParamsExist) {
                $fdrsWithCalibration[] = $fdrInfo;
            }
        }

        $fdrsAndCalibrations = [];
        $calibration = new Calibration;
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
                'name' => $fdrInfo['name'],
                'calibrations' => $fdrCalibrations,
                'calibratedParams' => $calibratedParams
            ];
        }

        return $fdrsAndCalibrations;
    }
}
