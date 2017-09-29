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

    public function getCalibrationsList()
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

    public function getCalibrationsPage($args)
    {
        $userId = intval($this->_user->userInfo['id']);

        if (!is_int($userId)) {
            throw new UnauthorizedException('user id - ' . strval($userId));
        }

        if (!isset($args['page'])
            || empty($args['page'])
            || !is_int(intval($args['page']))
            || !isset($args['pageSize'])
            || empty($args['pageSize'])
            || !is_int(intval($args['pageSize']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $fdrId = null;

        if (isset($args['fdrId'])
            && !empty($args['fdrId'])
            && is_int(intval($args['fdrId']))
        ) {
            $fdrId = $args['fdrId'];
        }

        $page = $args['page'];
        $pageSize = $args['pageSize'];

        $em = EM::get();

        $criteria = ['userId' => $userId];

        if ($fdrId !== null) {
            $criteria = array_merge($criteria, ['fdrId' => $fdrId]);
        }

        $calibrationResult = $em->getRepository('Entity\Calibration')
            ->findBy(
                $criteria,
                ['dtUpdated' => 'DESC'],
                $pageSize,
                ($page - 1) * $pageSize
            );

        $activity = [];
        foreach($calibrationResult as $item) {
            $activity[] = $item->get();
        }

        $qb = $em->getRepository('Entity\Calibration')
            ->createQueryBuilder('calibration')
            ->select('count(calibration.id)')
            ->where('calibration.userId = :userId')
            ->setParameter('userId', $userId);

        if ($fdrId !== null) {
            $qb
                ->andWhere('calibration.fdrId = :fdrId')
                ->setParameter('fdrId', $fdrId);
        }

        $total = $qb
            ->getQuery()
            ->getSingleScalarResult();

        return json_encode([
            'rows' => $activity,
            'pages' => round($total / $pageSize)
        ]);
    }

    public function getCalibrationById($args)
    {
        $userId = intval($this->_user->userInfo['id']);

        if (!is_int($userId)) {
            throw new UnauthorizedException('user id - ' . strval($userId));
        }

        if (!isset($args['id'])
            || empty($args['id'])
            || !is_int(intval($args['id']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $id = intval($args['id']);

        $em = EM::get();

        $calibration = $em->getRepository('Entity\Calibration')
            ->findOneBy([
                'userId' => $userId,
                'id' => $id
            ]);

        if (empty($calibration)) {
            throw new NotFoundException("requested calibration not found. Calibration id: ". $id);
        }

        return json_encode(
            $em->getRepository('Entity\Calibration')->getCalibration($id)
        );
    }

    public function getCalibrationParams($args)
    {
        $userId = intval($this->_user->userInfo['id']);

        if (!is_int($userId)) {
            throw new UnauthorizedException('user id - ' . strval($userId));
        }

        if (!isset($args['fdrId'])
            || empty($args['fdrId'])
            || !is_int(intval($args['fdrId']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $fdrId = intval($args['fdrId']);

        $em = EM::get();
        $fdr = $em->find('Entity\Fdr', $fdrId);

        if (empty($fdr)) {
            throw new NotFoundException("requested FDR not found. Received id: ". $fdrId);
        }

        $params = $em->getRepository('Entity\Calibration')
            ->getCalibratedParams($fdrId);

        $calibrationParams = [];

        foreach ($params as $param) {
            $calibrationParams[] = [
                'id' => null,
                'calibrationId' => null,
                'description' => $param,
                'paramId' => $param['id'],
                'xy' => []
            ];
        }

        return json_encode([
            'fdrId' => $fdrId,
            'fdrName' => $fdr->getName(),
            'params' => $calibrationParams
        ]);
    }
}
