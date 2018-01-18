<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use Exception;

class FdrTemplateRepository extends EntityRepository
{
  public function insert(
    $em,
    $templateName,
    $code,
    $min,
    $max,
    $userId
  ) {
    $fdrTemplate = new \Entity\FdrTemplate;
    $fdrTemplate->setName($templateName);
    $fdrTemplate->setParamCode($code);
    $fdrTemplate->setMinYaxis($min);
    $fdrTemplate->setMaxYaxis($max);
    $fdrTemplate->setUserId($userId);
    $em->persist($fdrTemplate);
  }
}
