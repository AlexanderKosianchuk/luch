<?php

namespace Component;

use ComponentTraits\dynamicInjectedEntityTable;

use Exception;

class CalibrationComponent extends BaseComponent
{
  use dynamicInjectedEntityTable;
  /**
   * @Inject
   * @var Entity\CalibrationParam
   */
  private $CalibrationParam;

  /**
   * @Inject
   * @var Entity\Calibration
   */
  private $Calibration;

  /**
   * @Inject
   * @var Entity\FdrAnalogParam
   */
  private $FdrAnalogParam;

  public function getCalibrationParams ($fdrId, $id)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->CalibrationParam, $fdr->getCode());

    return $this->em('fdrs')
        ->getRepository('Entity\CalibrationParam')
        ->findBy(['calibrationId' => $id]);
  }

  public function getCalibratedParams($fdrId)
  {
    if (!is_int($fdrId)) {
      throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
        . json_encode($fdrId), 1);
    }

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);
    if ($fdr === null) {
      return null;
    }

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    $fdrAnalogParam = $this->em('fdrs')
      ->getRepository('Entity\FdrAnalogParam')
      ->findAll();

    $params = [];
    foreach($fdrAnalogParam as $item) {
      if ($item->isCalibrated()) {
        $params[] = $item->get(true);
      }
    }
    return $params;
  }

  public function getCalibration($id)
  {
    if (!is_int($id)) {
      throw new Exception("Incorrect calibration id passed. Int is required. Passed: "
        . json_encode($id), 1);
    }

    $calibration = $this->em()->find('Entity\Calibration', $id);
    $fdr = $calibration->getFdr();

    if ($fdr === null) {
      return null;
    }

    $this->setEntityTable('fdrs', $this->CalibrationParam, $fdr->getCode());
    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    $calibrationParams = $this->em('fdrs')->getRepository('Entity\CalibrationParam')->findBy([
      'calibrationId' => $id
    ]);

    $params = [];
    foreach($calibrationParams as $item) {
      $cycloParam = $this->em('fdrs')->find('Entity\FdrAnalogParam', $item->getParamId());

      $params[] = array_merge($item->get(true), [
          'description' => $cycloParam->get(true) ?? null,
        ]
      );
    }

    return array_merge(
      $calibration->get(true),
      [ 'params' => $params ]
    );
  }


  public function createTable ($fdrCode)
  {
    if (!is_string($fdrCode)) {
      throw new Exception("Incorrect fdrCode passed. String expected. Passed: "
        . json_encode($fdrCode), 1);
    }

    $table = $fdrCode.$this->CalibrationParam->getPrefix();
    $isExist = $this->connection()->isExist($table);
    if(!$isExist) {
      $link = $this->connection()->create('fdrs');
      $q = "CREATE TABLE `".$table."` ("
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
      $this->connection()->destroy($link);
    }
    return $table;
  }

  public function createCalibration(
    $fdrId,
    $calibrationsName,
    $calibrations,
    $userId = null
  ) {
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    if (!$fdr) {
      throw new ForbiddenException('requested FDR not avaliable for current user. FDR id: '. $fdrId);
    }

    //create table if not exist
    $this->createTable ($fdr->getCode());

    $calibration = $this->getCalibrationsByName($calibrationsName, $userId);
    if (!$calibration) {
      $calibration = $this->setCalibration($calibrationsName, $fdrId, $userId);
    }

    $this->deleteCalibrationParams(
      $fdr->getCode(),
      $calibration->getId()
    );

    foreach ($calibrations as $paramId => $xy) {
      $this->setCalibrationParam(
        $fdr->getCode(),
        $calibration->getId(),
        intval($paramId),
        $xy
      );
    }

    return $calibration;
  }

  public function getCalibrationsByName ($name, $userId = null)
  {
    if (!is_string($name)) {
      throw new Exception("Incorrect calibration name passed. String expected. Passed: "
        . json_encode($name), 1);
    }
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    return $this->em()
      ->getRepository('Entity\Calibration')
      ->findOneBy(['name' => $name, 'userId' => $userId]);
  }

  public function getCalibrationById($id, $userId = null)
  {
    if (!is_int($id)) {
      throw new Exception("Incorrect calibration id passed. Int expected. Passed: "
        . json_encode($id), 1);
    }

    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    return $this->em()
      ->find('Entity\Calibration', $id);
  }

  public function setCalibration($name, $fdrId, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    $Calibration = $this->Calibration;
    $calibration = new $Calibration;
    $calibration->set([
      'name' => $name,
      'fdr' => $fdr,
      'userId' => $userId
    ]);

    $this->em()->persist($calibration);
    $this->em()->flush();

    return $calibration;
  }

  public function deleteCalibration($calibrationId)
  {
    $calibration = $this->em()->find('Entity\Calibration', $calibrationId);
    $fdr = $this->em()->find('Entity\Fdr', $calibration->getFdrId());

    $this->setEntityTable('fdrs', $this->CalibrationParam, $fdr->getCode());

    $calibrationParams = $this->em('fdrs')
      ->getRepository('Entity\CalibrationParam')
      ->findBy(['calibrationId' => $calibrationId]);

    foreach ($calibrationParams as $calibrationParam) {
      $this->em('fdrs')->remove($calibrationParam);
    }

    $this->em()->remove($calibration);
    $this->em()->flush();

  }

  public function deleteCalibrationParams($fdrCode, $calibrationId)
  {
    $this->setEntityTable('fdrs', $this->CalibrationParam, $fdrCode);

    $calibrations = $this->em('fdrs')
      ->getRepository('Entity\CalibrationParam')
      ->findBy(['calibrationId' => $calibrationId]);

    foreach ($calibrations as $calibration) {
      $this->em('fdrs')->remove($calibration);
    }

    $this->em('fdrs')->flush();
  }

  public function setCalibrationParam(
    $fdrCode,
    $calibrationId,
    $paramId,
    $xy
  ) {
    $this->setEntityTable('fdrs', $this->CalibrationParam, $fdrCode);

    $CalibrationParam = $this->CalibrationParam;
    $calibrationParam = new $CalibrationParam;
    $calibrationParam->set([
      'paramId' => $paramId,
      'calibrationId' => $calibrationId,
      'xy' => $xy
    ]);
    $this->em('fdrs')->persist($calibrationParam);
    $this->em('fdrs')->flush();

    return $calibrationParam;
  }

  public function updateCalibration(
    $calibrationId,
    $calibrationsName,
    $calibrations,
    $userId = null
  ) {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    if (!is_int($calibrationId)) {
      throw new Exception("Incorrect calibrationId passed. Int expected. Passed: "
        . json_encode($calibrationId), 1);
    }

    if (!is_string($calibrationsName)) {
      throw new Exception("Incorrect calibrationsName passed. String expected. Passed: "
        . json_encode($calibrationsName), 1);
    }

    if (!is_array($calibrations)) {
      throw new Exception("Incorrect calibrations passed. String expected. Passed: "
        . json_encode($calibrations), 1);
    }

    $calibration = $this->getCalibrationById ($calibrationId);
    if(empty($calibration)) {
      throw new Exception("Updating calibration is not exist.", 1);
    }

    $fdr = $this->em()->find('Entity\Fdr', $calibration->getFdrId());

    $this->deleteCalibrationParams(
      $fdr->getCode(),
      $calibration->getId()
    );

    foreach ($calibrations as $paramId => $xy) {
      $this->setCalibrationParam(
        $fdr->getCode(),
        $calibrationId,
        intval($paramId),
        $xy
      );
    }

    $calibration->setName($calibrationsName);
    $calibration->setDtUpdated(new \DateTime());

    $this->em()->merge($calibration);
    $this->em()->flush();

    return $this->getCalibrationById ($calibrationId);
  }
}
