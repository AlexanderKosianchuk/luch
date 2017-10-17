<?php

namespace Component;

use Model\Fdr;
use Model\Calibration;
use Model\User;

use Exception;

class FdrComponent extends BaseComponent
{
    public function getAvaliableFdrs()
    {
        $userId = $this->user()->getId();
        return $userId;

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

        $em = EM::get();
        $fdrsAndCalibrations = [];
        $calibration = new Calibration;
        foreach ($fdrsWithCalibration as $fdrInfo) {
            $fdrId = intval($fdrInfo['id']);
            $fdrCode = $fdrInfo['code'];
            $calibrationDynamicTable = $calibration->getTableName($fdrCode);

            $fdrCalibrations = $em->getRepository('Entity\Calibration')
                ->findBy([
                    'userId' => $userId,
                    'fdrId' => $fdrId
                ]);

            $calibratedParams = $fdr->getCalibratedParams($fdrId);

            foreach ($fdrCalibrations as &$fdrCalibration) {
                $fdrCalibration = $fdrCalibration->get();
            }

            $fdrsAndCalibrations[] = [
                'id' => intval($fdrInfo['id']),
                'name' => $fdrInfo['name'],
                'calibrations' => $fdrCalibrations
            ];
        }

        return $fdrsAndCalibrations;
    }
}
