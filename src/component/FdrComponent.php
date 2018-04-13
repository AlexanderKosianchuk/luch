<?php

namespace Component;

use ComponentTraits\dynamicInjectedEntityTable;

use Exception;

class FdrComponent extends BaseComponent
{
  use dynamicInjectedEntityTable;

  const PARAM_TYPE_AP = 'ap';
  const PARAM_TYPE_BP = 'bp';

  private static $_codeToTable = [];

  /**
   * @Inject
   * @var Entity\FdrAnalogParam
   */
  private $FdrAnalogParam;

  /**
   * @Inject
   * @var Entity\FdrBinaryParam
   */
  private $FdrBinaryParam;

  public static function getApType()
  {
    return self::PARAM_TYPE_AP;
  }

  public static function getBpType()
  {
    return self::PARAM_TYPE_BP;
  }

  public function getFdrs()
  {
    $userId = $this->user()->getId();

    $fdrs = $this->em()
      ->getRepository('Entity\FdrToUser')
      ->getAvaliableFdrs($userId);

    $fdrsAndCalibrations = [];
    foreach ($fdrs as $fdr) {
      $fdrCalibrations = $this->em()->getRepository('Entity\Calibration')
        ->findBy([
          'userId' => $userId,
          'fdrId' => $fdr->getId()
        ]);

      $calibrations = [];
      foreach ($fdrCalibrations as $item) {
        $calibrations[] = $item->get();
      }

      $fdrsAndCalibrations[] = [
        'id' => $fdr->getId(),
        'name' => $fdr->getName(),
        'calibrations' => $calibrations
      ];
    }

    return $fdrsAndCalibrations;
  }

  public function getParams($fdrId, $isArray = false)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    $params = $this->em('fdrs')
      ->getRepository('Entity\FdrAnalogParam')
      ->findAll();

    if (!$isArray) {
      return $params;
    }

    $array = [];
    foreach ($params as $param) {
      $array[] = $param->get(true);
    }

    return $array;
  }

  public function getPrefixGroupedParams($fdrId)
  {
    $params = $this->getParams($fdrId);

    $grouped = [];
    foreach ($params as $param) {
      if (!isset($grouped[$param->getPrefix()])) {
        $grouped[$param->getPrefix()] = [];
      }

      $grouped[$param->getPrefix()][] = $param->get(true);
    }

    return $grouped;
  }

  public function getBinaryParams($fdrId, $isArray = false)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->FdrBinaryParam, $fdr->getCode());

    $params = $this->em('fdrs')
      ->getRepository('Entity\FdrBinaryParam')
      ->findAll();

    if (!$isArray) {
      return $params;
    }

    $array = [];
    foreach ($params as $param) {
      $array[] = $param->get(true);
    }

    return $array;
  }

  public function getPrefixGroupedBinaryParams($fdrId)
  {
    $params = $this->getBinaryParams($fdrId);

    $grouped = [];
    foreach ($params as $param) {
      if (!isset($grouped[$param->getPrefix()])) {
        $grouped[$param->getPrefix()] = [];
      }

      $grouped[$param->getPrefix()][] = $param->get(true);
    }

    return $grouped;
  }

  public function getAnalogPrefixes($fdrId)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    $params = $this->em('fdrs')
      ->getRepository('Entity\FdrAnalogParam')
      ->findAll('Entity\FdrAnalogParam');
    $prefixes = [];

    foreach ($params as $item) {
      $prefixes[$item->getPrefix()] = 0;
    }

    return array_keys($prefixes);
  }

  public function getBinaryPrefixes($fdrId)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->FdrBinaryParam, $fdr->getCode());

    $params = $this->em('fdrs')
      ->getRepository('Entity\FdrBinaryParam')
      ->findAll('Entity\FdrBinaryParam');

    $prefixes = [];

    foreach ($params as $item) {
      $prefixes[$item->getPrefix()] = 0;
    }

    return array_keys($prefixes);
  }

  public function getPrefixFrequency($paramsCyclo)
  {
    $freq = [];
    foreach ($paramsCyclo as $prefix => $params) {
      foreach ($params as $param) {
        if (!isset($freq[$param['prefix']])) {
          $freq[$param['prefix']] = [];
        }

        $freq[strval($param['prefix'])] = count($param['channel']);
      }
    }

    return $freq;
  }

  public function getCodeToTableArray($fdrId, $flightTable)
  {
    if (!is_int($fdrId)) {
      throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
        . json_encode($fdrId), 1);
    }

    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());
    $this->setEntityTable('fdrs', $this->FdrBinaryParam, $fdr->getCode());

    $params = $this->getParams($fdrId);
    $binary = $this->getBinaryParams($fdrId);

    if (count(self::$_codeToTable) > 0) {
      return self::$_codeToTable;
    }

    foreach ($params as $param) {
      self::$_codeToTable[$param->getCode()] = $flightTable
        .$this->FdrAnalogParam->getTablePrefix()
        .'_'.$param->getPrefix();
    }

    foreach ($binary as $param) {
      self::$_codeToTable[$param->getCode()] = $flightTable
        .$this->FdrBinaryParam->getTablePrefix()
        .'_'.$param->getPrefix();
    }

    return self::$_codeToTable;
  }

  public function isAvaliable($fdrId, $userId = null)
  {
    if (!is_int($fdrId)) {
      throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
        . json_encode($fdrId), 1);
    }

    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    if ($this->member()->isAdmin()
      || $this->member()->isLocal()
    ) {
      return true;
    }

    $fdr = $this->em()->find('Entity\Fdr', [
      'id' => $fdrId,
      'userId' => $userId
    ]);

    if ($fdr) {
      return true;
    }

    if ($this->member()->isUser()) {
      return false;
    }

    $users = $this->em()->find('Entity\User', [
      'creatorId' => $userId
    ]);

    foreach ($users as $user) {
      if ($this->isAvaliable($fdrId, $userId = null)) {
        return true;
      }
    }

    return false;
  }

  public function getAnalogByCode($fdrId, $code)
  {
    $userId = $this->user()->getId();

    $fdr = $this->em()->find('Entity\Fdr', [
      'id' => $fdrId,
      'userId' => $userId
    ]);

    if (!$fdr) {
      throw new Exception('FDR not found. Id: '.$fdrId, 1);
    }

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    return $this->em('fdrs')
      ->getRepository('Entity\FdrAnalogParam')
      ->findOneBy([
        'code' => $code
      ]);
  }

  public function getAnalogById($fdrId, $id)
  {
    $userId = $this->user()->getId();

    $fdr = $this->em()->find('Entity\Fdr', [
      'id' => $fdrId,
      'userId' => $userId
    ]);

    if (!$fdr) {
      throw new Exception('FDR not found. Id: '.$fdrId, 1);
    }

    $this->setEntityTable('fdrs', $this->FdrAnalogParam, $fdr->getCode());

    return $this
      ->em('fdrs')
      ->find('Entity\FdrAnalogParam', $id);
  }

  public function getBinaryByCode($fdrId, $code)
  {
    $userId = $this->user()->getId();

    $fdr = $this->em()->find('Entity\Fdr', [
      'id' => $fdrId,
      'userId' => $userId
    ]);

    if (!$fdr) {
      throw new Exception('FDR not found. Id: '.$fdrId, 1);
    }

    $this->setEntityTable('fdrs', $this->FdrBinaryParam, $fdr->getCode());

    return $this->em('fdrs')
      ->getRepository('Entity\FdrBinaryParam')
      ->findOneBy([
        'code' => $code
      ]);
  }

  public function getBinaryById($fdrId, $id)
  {
    $userId = $this->user()->getId();

    $fdr = $this->em()->find('Entity\Fdr', [
      'id' => $fdrId,
      'userId' => $userId
    ]);

    if (!$fdr) {
      throw new Exception('FDR not found. Id: '.$fdrId, 1);
    }

    $this->setEntityTable('fdrs', $this->FdrBinaryParam, $fdr->getCode());

    return $this
      ->em('fdrs')
      ->find('Entity\FdrBinaryParam', $id);
  }

  public function getParamByCode($fdrId, $code)
  {
    $a = $this->getAnalogByCode(
      $fdrId,
      $code
    );

    if ($a) {
      return array_merge(
        $a->get(true),
        ['type' => self::getApType()]
      );
    }

    $b = $this->getBinaryByCode(
      $fdrId,
      $code
    );

    if ($b) {
      return array_merge(
        $b->get(true),
        ['type' => self::getBpType()]
      );
    }

    return [];
  }

  public function getParamsByCodes($fdrId, $codesArr)
  {
    $params = [];
    $fetchedCodes = [];
    foreach ($codesArr as $code) {
      $paramDesc = $this->getParamByCode(
          $fdrId,
          $code
        );

      if (!empty($paramDesc) && !in_array($code, $fetchedCodes)) {
        $params[] = $paramDesc;
        $fetchedCodes[] = $code;
      }
    }

    return $params;
  }

  public function getAnalogTable($base = '', $appendix = '')
  {
    $table = $base.$this->FdrAnalogParam::getTablePrefix();

    if ($appendix === '') {
      return $table;
    }

    return $table.'_'.$appendix;
  }

  public function getBinaryTable($base = '', $appendix = '')
  {
    $table = $base.$this->FdrBinaryParam::getTablePrefix();

    if ($appendix === '') {
      return $table;
    }

    return $table.'_'.$appendix;
  }
}
