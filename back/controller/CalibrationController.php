<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class CalibrationController extends BaseController
{
    public function getCalibrationsListAction()
    {
        $userId = $this->user()->getId();

        $calibrations = $this->em()
            ->getRepository('Entity\Calibration')
            ->findBy(['userId' => $userId]);

        $response = [];
        foreach ($calibrations as $item) {
            $response[] = $item->get();
        }

        return json_encode($response);
    }

    public function saveCalibrationAction($name, $fdrId, $calibrations, $calibrationId = null)
    {
        $fdrToUser = $this->em()->getRepository('Entity\FdrToUser')
            ->findBy(['fdrId' => $fdrId, 'userId' => $this->user()->getId()]);

        $fdr = $this->em()->find('Entity\Fdr', $fdrId);

        if (!$fdrToUser || !$fdr) {
            throw new ForbiddenException('requested FDR not avaliable for current user. FDR id: '. $fdrId);
        }

        $userId = $this->user()->getId();

        $calibration = null;
        if (($calibrationId === null) || ($calibrationId === '')) {
            $calibration = $this->dic()->get('calibration')
                ->createCalibration(
                    intval($fdrId),
                    $name,
                    $calibrations
                );
        } else {
            $calibration = $this->dic()->get('calibration')
                ->updateCalibration(
                    intval($calibrationId),
                    $name,
                    $calibrations
                );
        }

        if (empty($calibration)) {
            throw new NotFoundException("saved calibration error. Cant find by id. Calibration id: ". $id);
        }

        return json_encode($calibration->get(true));
    }

    public function deleteCalibrationAction($calibrationId)
    {
        $userId = $this->user()->getId();
        $calibrationId = intval($calibrationId);

        $calibration = $this->em()->find('Entity\Calibration', $calibrationId);

        if (empty($calibration)) {
            throw new NotFoundException("requested calibration not found. Calibration id: ". $calibrationId);
        }

        $fdrToUser = $this->em()->getRepository('Entity\FdrToUser')
            ->findBy([
                'fdrId' => $calibration->getFdrId(),
                'userId' => $this->user()->getId()
            ]);

        if (!$fdrToUser) {
            throw new ForbiddenException('requested FDR not avaliable for current user. FDR id: '. $fdrId);
        }

        $this->dic()
            ->get('calibration')
            ->deleteCalibration ($calibrationId);

        return json_encode('ok');
    }

    public function getCalibrationsPageAction($page, $pageSize, $fdrId = null)
    {
        $criteria = ['userId' => $this->user()->getId()];

        if ($fdrId !== null) {
            $criteria = array_merge($criteria, ['fdrId' => $fdrId]);
        }

        $calibrationResult = $this->em()->getRepository('Entity\Calibration')
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

        $qb = $this->em()->getRepository('Entity\Calibration')
            ->createQueryBuilder('calibration')
            ->select('count(calibration.id)')
            ->where('calibration.userId = :userId')
            ->setParameter('userId', $this->user()->getId());

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

    public function getCalibrationByIdAction($id)
    {

        $id = intval($id);

        $calibration = $this->em()->getRepository('Entity\Calibration')
            ->findOneBy([
                'userId' => $this->user()->getId(),
                'id' => $id
            ]);

        if (empty($calibration)) {
            throw new NotFoundException("requested calibration not found. Calibration id: ". $id);
        }

        $calibration = $this->dic()
            ->get('calibration')
            ->getCalibration($id);

        return json_encode($calibration);
    }

    public function getCalibrationParamsAction($fdrId)
    {
        $fdrId = intval($fdrId);
        $fdr = $this->em()->find('Entity\Fdr', $fdrId);

        if (empty($fdr)) {
            throw new NotFoundException("requested FDR not found. Received id: ". $fdrId);
        }

        $params = $this->dic()->get('calibration')
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
