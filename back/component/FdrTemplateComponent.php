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

  public function getTemplateById($fdrId, $templateId, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    $this->setFdrTemplateTable($fdr->getCode());

    $fdrTemplateParam = $this->em('fdrs')
      ->getRepository('Entity\FdrTemplate')
      ->findOneBy(['id' => $templateId]);

    $fdrTemplateParams = $this->em('fdrs')
      ->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $fdrTemplateParam->getName()]);

    $templateName = $fdrTemplateParam->getName();

    $template = [
      'id' => $templateId,
      'name' => $templateName,
      'paramCodes' => [],
      'params' => [],
      'userId' => $fdrTemplateParam->getUserId(),
      'servicePurpose' => $this->getServicePurpose($templateName)
    ];

    foreach ($fdrTemplateParams as $templateParam) {

      $template['paramCodes'][] = $templateParam->getParamCode();
      $template['userId'] = $templateParam->getUserId();

      $param = $this->fdrComponent->getParamByCode(
        $fdr->getId(),
        $templateParam->getParamCode()
      );

      if (($param === null) || empty($param)) {
        continue;
      }

      $template['params'][] = $param;
    }

    return $template;
  }

  public function getTemplateByName($fdrCode, $templateName, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $fdrTemplateParams = $this->em('fdrs')
      ->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $templateName]);

    $template = [
      'id' => null,
      'name' => $templateName,
      'paramCodes' => [],
      'params' => [],
      'userId' => null,
      'servicePurpose' => $this->getServicePurpose($templateName)
    ];

    $fdr = $this->em()
      ->getRepository('Entity\Fdr')
      ->findOneBy(['code' => $fdrCode]);

    foreach ($fdrTemplateParams as $templateParam) {
      if ($template['id'] === null) {
        $template['id'] = $templateParam->getId();
      }

      $template['paramCodes'][] = $templateParam->getParamCode();
      $template['userId'] = $templateParam->getUserId();

      $param = $this->fdrComponent->getParamByCode(
        $fdr->getId(),
        $templateParam->getParamCode()
      );

      if (($param === null) || empty($param)) {
        continue;
      }

      $template['params'][] = $param;
    }

    return $template;
  }

  private function getServicePurpose($name)
  {
    $servicePurpose = [
      'isLast' => false,
      'isEvents' => false,
      'isDefault' => false
    ];

    if ($this->isLast($name)) {
      $servicePurpose['isLast'] = true;
    } else if ($this->isEvents($name)) {
      $servicePurpose['isEvents'] = true;
    } else if ($this->isDefault($name)) {
      $servicePurpose['isDefault'] = true;
    }

    return $servicePurpose;
  }

  public function getFlightTemplates($flightId, $ignoreEventsTemplate = false, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new Exception('Flight not found. Id: '.$flightId, 1);
    }

    return $this->getFdrTemplates($flight->getFdr()->getId());
  }


  public function getFdrTemplates($fdrId, $ignoreEventsTemplate = false, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    if (!$fdr) {
      throw new Exception('FDR not found. Id: '.$fdrId, 1);
    }

    $this->setFdrTemplateTable($fdr->getCode());

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
      $id = null;
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
        // workaround. Setting template id by its first row id
        if ($id === null) {
          $id = $templateRow->getId();
        }

        $paramDesc = $this->fdrComponent->getParamByCode(
          $fdr->getId(),
          $templateRow->getParamCode()
        );

        if (!empty($paramDesc)
          && !in_array($templateRow->getParamCode(), $paramCodes)
        ) {
          $paramCodes[] = $templateRow->getParamCode();
          $params[] = $paramDesc;
        }
      }

      $servicePurpose = $this->getServicePurpose($name);

      if ($ignoreEventsTemplate && $this->isEvents($name)) {
        continue;
      }

      $templates[] = [
        'id' => $id,
        'name' => $name,
        'paramCodes' => $paramCodes,
        'params' => $params,
        'servicePurpose' => $servicePurpose,
        'userId' => $userId
      ];
    }

    return $templates;
  }

  public function delete($fdrCode, $templateName, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $templates = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $templateName]);

    foreach ($templates as $template) {
      $this->em('fdrs')->remove($template);
    }

    $this->em('fdrs')->flush();
  }

  public function deleteById($fdrCode, $templateId, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);

    $templateParam = $this->em('fdrs')
      ->getRepository('Entity\FdrTemplate')
      ->findOneBy(['id' => $templateId]);

    $templates = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findBy(['name' => $templateParam->getName()]);

    foreach ($templates as $template) {
      $this->em('fdrs')->remove($template);
    }

    $this->em('fdrs')->flush();
  }

  public function getParamMinMax($fdrCode, $templateId, $code, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);
    $fdr = $this->em()->getRepository('Entity\Fdr')->findOneBy(['code' => $fdrCode]);
    $template = $this->getTemplateById($fdr->getId(), $templateId, $userId);

    $templateParam = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findOneBy([
        'name' => $template['name'],
        'paramCode' => $code,
        'userId' => $userId
      ]);

    return [
      'min' => $templateParam->getMinYaxis(),
      'max' => $templateParam->getMaxYaxis()
    ];
  }

  public function setParamMinMax(
    $fdrCode,
    $templateId,
    $code,
    $range,
    $userId = null
  ) {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $this->setFdrTemplateTable($fdrCode);
    $fdr = $this->em()->getRepository('Entity\Fdr')->findOneBy(['code' => $fdrCode]);
    $template = $this->getTemplateById($fdr->getId(), $templateId, $userId);

    $templateParam = $this->em('fdrs')->getRepository('Entity\FdrTemplate')
      ->findOneBy([
        'name' => $template['name'],
        'paramCode' => $code,
        'userId' => $userId
      ]);

    $templateParam->setMinYaxis($range->min);
    $templateParam->setMaxYaxis($range->max);

    $this->em('fdrs')->persist($templateParam);
    $this->em('fdrs')->flush();
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

    return $this->getTemplateByName($fdrCode, $name, $userId);
  }

  public function createWithDistributedParams(
    $fdrCode,
    $tplName,
    $params,
    $userId = null
  ) {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $apParams = [];
    foreach ($params as $param) {
      if ($param['type'] === $this->fdrComponent->getApType()) {
        $apParams[] = $param;
      }
    }
    $apCount = count($apParams);

    $bpParams = [];
    foreach ($params as $param) {
      if ($param['type'] === $this->fdrComponent->getBpType()) {
        $bpParams[] = $param;
      }
    }

    $bpCount = count($bpParams);

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

    if ($bpCount > 0) {
      $busyCorridor = (($apCount -1) / $apCount * 100);
      $freeCorridor = 100 - $busyCorridor;//100%

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

    return $this->getTemplateByName($fdrCode, $tplName, $userId);
  }
}
