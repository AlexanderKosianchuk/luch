<?php

namespace Controller;

use Model\Fdr;
use Model\Calibration;

use Component\EntityManagerComponent as EM;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Component\FdrComponent;

class CalibrationController extends CController
{
    public function getAvaliableFdrs($data)
    {
        $userId = intval($this->_user->userInfo['id']);
        $fdrsAndCalibrations = FdrComponent::getAvaliableFdrs($userId);

        return json_encode($fdrsAndCalibrations);
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
            throw new ForbiddenException('requested FDR not avaliable for current user. FDR id: '. $fdrId);
        }

        $fdr = new Fdr;
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

        return json_encode('ok');
    }

    public function deleteCalibration($data)
    {
        $userId = intval($this->_user->userInfo['id']);
        $calibrationId = intval($data['calibrationId']);

        $calibration = new Calibration();
        $calibrationInfo = $calibration->getCalibrationById ($calibrationId, $userId);

        if (empty($calibrationInfo)) {
            throw new NotFoundException("requested calibration not found. Calibration id: ". $calibrationId);
        }

        $fdrId = intval($calibrationInfo['id_fdr']);
        $calibrationId = intval($calibrationInfo['id']);

        $isAvaliable = $this->_user->checkFdrAvailable($fdrId, $userId);

        if (!$isAvaliable) {
            throw new ForbiddenException('requested FDR not avaliable for current user. FDR id: '. $fdrId);
        }

        $calibration->deleteCalibration ($calibrationId, $userId);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $fdrCode = $fdrInfo['code'];
        unset($fdr);

        $calibrationDynamicTable = $calibration->getTableName($fdrCode);

        if($calibration->checkTableExist ($calibrationDynamicTable)) {
            $calibration->deleteCalibrationParams ($calibrationDynamicTable, $calibrationId);
        }

        return json_encode('ok');
    }

    public function getCalibrations()
    {
        $userId = intval($this->_user->userInfo['id']);

        if (!is_int($userId)) {
            throw new UnauthorizedException('user id - ' . strval($userId));
        }

        $em = EM::get();

        $calibrations = $em->getRepository('Entity\Calibration')
            ->findBy(['userId' => $userId]);

        $response = [];
        foreach ($calibrations as $item) {
            $response[] = $item->get();
        }

        return json_encode($response);
    }
}
