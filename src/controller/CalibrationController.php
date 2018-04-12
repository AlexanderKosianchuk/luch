<?php

namespace Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use \L;

class CalibrationController extends BaseController
{
  public function getAllAction()
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

  public function saveAction($name, $fdrId, $calibrations, $calibrationId = null)
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
      $calibration = $this->dic('calibration')
        ->createCalibration(
          intval($fdrId),
          $name,
          $calibrations
        );
    } else {
      $calibration = $this->dic('calibration')
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

  public function deleteAction($calibrationId)
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

  public function getPageAction($page, $pageSize, $fdrId = null)
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

  public function getAction($id)
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

  public function getParamsAction($fdrId)
  {
    $fdrId = intval($fdrId);
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    if (empty($fdr)) {
      throw new NotFoundException("requested FDR not found. Received id: ". $fdrId);
    }

    $params = $this->dic('calibration')
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

  public function exportAction($id, $fdrId)
  {
    $id = intval($id);
    $fdrId = intval($fdrId);
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    if (empty($fdr)) {
      throw new NotFoundException("requested FDR not found. Received id: ". $fdrId);
    }

    $calibration = $this->em()->getRepository('Entity\Calibration')
      ->findOneBy([
        'userId' => $this->user()->getId(),
        'id' => $id
      ]);

    if (empty($calibration)) {
      throw new NotFoundException("requested calibration not found. Calibration id: ". $id);
    }

    $calibration = $this->dic('calibration')->getCalibration($id);

    $spreadsheet = new Spreadsheet();

    $headerData = [
        [L::calibrations_name.':', $calibration['name']],
        [L::calibrations_fdr.':', $calibration['fdrName']],
        [L::calibrations_dtCreated.':', $calibration['dtCreated']],
        [L::calibrations_dtUpdated.':', $calibration['dtUpdated']],
    ];

    $counter = 1;

    $spreadsheet->getActiveSheet()
      ->fromArray(
        $headerData,// The data to set
        NULL, // Array values with this value will not be set
        'A'. $counter // Top left coordinate of the worksheet range where
      );

    $counter += count($headerData) + 5;

    $params = $calibration['params'];

    foreach ($params as $param) {
      $description = $param['description'];

      $paramDesc = [
          [L::calibrations_paramCode.':', $description['code']],
          [L::calibrations_paramName.':', $description['name']],
          [L::calibrations_paramChannels.':', implode(',', $description['channel'])],
          [L::calibrations_paramMinValue.':', $description['minValue']],
          [L::calibrations_paramMaxValue.':', $description['maxValue']],
      ];

      $spreadsheet->getActiveSheet()
        ->fromArray(
          $paramDesc,
          NULL,
          'A' . $counter
        );

      $counter += count($paramDesc) + 1;

      $xy = $param['xy'];
      $array = [];
      $array[0] = [L::calibrations_code];
      $array[1] = [L::calibrations_physics];

      foreach ($xy as $item) {
        $array[0][] = $item->y;
        $array[1][] = $item->x;
      }

      $spreadsheet->getActiveSheet()
        ->fromArray(
          $array,
          NULL,
          'A' . $counter
        );

      $counter += count($array) + 3;
    }

    $writer = new Xlsx($spreadsheet);

    $file = $calibration['name'].'_'.date('Y-m-d').'.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="'.$file.'"');
    $writer->save("php://output");
  }
}
