<?php

namespace Component;

use Exception;

class FdrTemplateComponent extends BaseComponent
{
  const LAST_TPL_NAME = 'last';
  const EVENTS_TPL_NAME = 'events';
  const TPL_DEFAULT =  'default';

  /**
   * @Inject
   * @var Entity\FdrTemplate
   */
  private $FdrTemplate;

  /**
   * @Inject
   * @var Component\FdrComponent
   */
  private $fdrComponent;

  public static function getLastName()
  {
    return self::LAST_TPL_NAME;
  }

  public static function getEventsName()
  {
    return self::EVENTS_TPL_NAME;
  }

  public static function getDefaultName()
  {
    return self::TPL_DEFAULT;
  }

  public function isLast($template)
  {
    return $template === $this::LAST_TPL_NAME;
  }

  public function isEvents($template)
  {
    return $template === $this::EVENTS_TPL_NAME;
  }

  public function isDefault($template)
  {
    return $template === $this::TPL_DEFAULT;
  }

  public function createFdrTemplateTable($code)
  {
    $tableName = $code.$this->FdrTemplate::getPrefix();
    $link = $this->connection()->create('fdrs');
    $this->connection()->drop($tableName, null, $link);

    $query = 'CREATE TABLE `'.$tableName.'` ('
      . '`id` INT NOT NULL AUTO_INCREMENT, '
      . ' `frame_num` INT,'
      . ' `start_time` BIGINT,'
      . ' `end_frame_num` INT,'
      . ' `end_time` BIGINT,'
      . ' `ref_param` VARCHAR(255),'
      . ' `code` VARCHAR(255),'
      . ' `exc_aditional_info` TEXT,'
      . ' `false_alarm` BOOL DEFAULT 0,'
      . ' `user_comment` TEXT,'
      . ' PRIMARY KEY (`id`))'
      . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';

    $stmt = $link->prepare($query);
    $stmt->execute();
    $stmt->close();

    $this->connection()->destroy($link);

    return $tableName;
  }

  private function setFdrTemplateTable($fdrCode)
  {
    $link = $this->connection()->create('fdrs');
    $table = $this->FdrTemplate::getTable($link, $fdrCode);
    $this->connection()->destroy($link);

    if ($table === null) {
      return null;
    }

    $this->em('fdrs')
      ->getClassMetadata('Entity\FdrTemplate')
      ->setTableName($table);
  }

  public function getTemplateByName($fdrCode, $templateName, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    return $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $templateName]);
  }

  public function getTemplates($flightId, $ignoreEventsTemplate = false, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new Exception('Flight not found. Id: '.$flightId, 1);
    }

    $this->setFdrTemplateTable($flight->getFdr()->getCode());

    $names = $this->em('fdrs')
      ->getRepository('Entity\FdrTemplate')
      ->createQueryBuilder('fdrTemplate')
      ->select('fdrTemplate.name')
      ->where('fdrTemplate.userId = :userId')
      ->setParameter('userId', $userId)
      ->distinct()
      ->getQuery()
      ->getArrayResult();

    $templates = [];
    foreach ($names as $name) {
      $name = $name['name'];
      $rows = $this->em('fdrs')
        ->getRepository('Entity\FdrTemplate')
        ->findBy([
          'name' => $name,
          'userId' => $userId
        ]);

      $params = [];
      $paramCodes = [];

      foreach ($rows as $templateRow) {
        $paramDesc = $this->fdrComponent->getParamByCode(
          $flight->getFdrId(),
          $templateRow->getParamCode()
        );

        if (!empty($paramDesc)
          && !in_array($templateRow->getParamCode(), $paramCodes)
        ) {
          $paramCodes[] = $templateRow->getParamCode();
          $params[] = $paramDesc;
        }
      }

      $servicePurpose = [];
      if ($this->isLast($name)) {
        $servicePurpose['isLast'] = true;
      } else if ($this->isEvents($name)) {
        $servicePurpose['isEvents'] = true;
      } else if ($this->isDefault($name)) {
        $servicePurpose['isDefault'] = true;
      }

      if ($ignoreEventsTemplate && $this->isEvents($name)) {
        continue;
      }

      $templates[] = [
        'name' => $name,
        'paramCodes' => $paramCodes,
        'params' => $params,
        'servicePurpose' => $servicePurpose
      ];
    }

    return $templates;
  }

  public function create($fdrCode, $name, $params, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->delete($fdrCode, $name, $userId);

    foreach ($params as $param) {
      $fdrTemplate = new $this->FdrTemplate();
      $fdrTemplate->setName($name);
      $fdrTemplate->setParamCode($param['code']);
      $fdrTemplate->setMinYaxis($param['minYaxis'] ?? 0);
      $fdrTemplate->setMaxYaxis($param['maxYaxis'] ?? 1);
      $fdrTemplate->setUserId($userId);
      $this->em('fdrs')->persist($fdrTemplate);
    }

    $this->em('fdrs')->flush();
  }

  public function delete($fdrCode, $name, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $templates = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $name]);

    foreach ($templates as $template) {
      $this->em('fdrs')->remove($template);
    }

    $this->em('fdrs')->flush();
  }

  public function getParamMinMax($fdrCode, $templateName, $code, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $template = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findOneBy([
        'name' => $templateName,
        'paramCode' => $code,
        'userId' => $userId
      ]);

    return [
      'min' => $template->getMinYaxis(),
      'max' => $template->getMaxYaxis()
    ];
  }

  public function setParamMinMax(
    $fdrCode,
    $templateName,
    $code,
    $range,
    $userId = null
  ) {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $template = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findOneBy([
        'name' => $templateName,
        'paramCode' => $code,
        'userId' => $userId
      ]);

    $template->setMinYaxis($range->min);
    $template->setMaxYaxis($range->max);

    $this->em('fdrs')->persist($template);
    $this->em('fdrs')->flush();
  }

  public function createWithDistributedParams(
    $fdrCode,
    $tplName,
    $paramsWithType,
    $userId = null
  ) {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $apParams = $paramsWithType[$this->fdrComponent->getApType()];
    $apCount = count($apParams);

    // start from top
    for ($i = ($apCount - 1); $i >= 0; $i--) {
      $param = $apParams[$i];
      $paramCode = $param['code'];
      $yMax = $param['max'];
      $yMin = $param['min'];
      $curCorridor = 0;

      if(($i == 0) && ($yMax > 1)){
        $yMax += $yMax * 0.15;//prevent first(top) param out chart boundary
      }

      if($yMax == $yMin) {
        $yMax += 0.001; //if $yMax == $yMin parameter builds as straight line in bottom of chart
      }

      if($yMax > 0) {
        $curCorridor = (($yMax - $yMin) * 1.05);
      } else {
        $curCorridor = -(($yMin - $yMax) * 1.05);
      }

      $axisMax = $yMax + ($i * $curCorridor);
      $axisMin = $yMin - (($apCount - $i) * $curCorridor);

      $this->em('fdrs')
        ->getRepository('Entity\FdrTemplate')
        ->insert(
          $this->em('fdrs'),
          $tplName,
          $paramCode,
          $axisMin,
          $axisMax,
          $this->user()->getId()
        );
    }

    if (isset($paramsWithType[$this->fdrComponent->getBpType()])) {
      $bpParams = $paramsWithType[$this->fdrComponent->getBpType()];
      $busyCorridor = (($apCount -1) / $apCount * 100);
      $freeCorridor = 100 - $busyCorridor;//100%

      $bpCount = count($bpParams);
      $curCorridor = $freeCorridor / $bpCount;
      $j = 0;

      for ($i = $apCount; $i < $apCount + $bpCount; $i++) {
        $paramCode = $bpParams[$i - $apCount]['code'];
        $axisMax = 100 - ($curCorridor * $j);
        $axisMin = 0 - ($curCorridor * $j);

        $this->em('fdrs')
          ->getRepository('Entity\FdrTemplate')
          ->insert(
            $this->em('fdrs'),
            $tplName,
            $paramCode,
            $axisMin,
            $axisMax,
            $this->user()->getId()
          );
        $j++;
      }
    }

    $this->em('fdrs')->flush();
  }
}
