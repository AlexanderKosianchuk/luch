<?php

namespace Controller;

use Entity\FdrTemplate;
use Entity\FlightEvent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;

class FdrTemplateController extends BaseController
{
  public function getAllAction($fdrId)
  {
    $fdrId = intval($fdrId);
    $userId = $this->user()->getId();

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    $isExist = $this->connection()->isExist($fdr->getCode().FdrTemplate::getPrefix());

    if (!$isExist) {
      $this->dic('fdrTemplate')->createFdrTemplateTable($fdr->getCode());
    }

    return json_encode(
      $this->dic()
      ->get('fdrTemplate')
      ->getFdrTemplates($fdrId, true)
    );
  }
}
