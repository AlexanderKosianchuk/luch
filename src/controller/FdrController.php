<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use \Exception;

class FdrController extends BaseController
{
  public function getFdrsAction()
  {
    return json_encode(
      $this->dic()
        ->get('fdr')
        ->getFdrs()
    );
  }

  public function getCycloAction($flightId, $fdrId = null)
  {
    if ($fdrId === null) {
      $flight = $this->em()->find('Entity\Flight', $flightId);
      $fdrId = $flight->getFdrId();
    }

    return json_encode([
      'fdrId' => $fdrId,
      'analogParams' => $this->dic('fdr')->getParams($fdrId, true),
      'binaryParams' => $this->dic('fdr')->getBinaryParams($fdrId, true)
    ]);
  }

  public function getCycloByFdrIdAction($fdrId)
  {
    return json_encode([
      'fdrId' => $fdrId,
      'analogParams' => $this->dic('fdr')->getParams($fdrId, true),
      'binaryParams' => $this->dic('fdr')->getBinaryParams($fdrId, true)
    ]);
  }

  public function setParamColorAction($flightId, $paramCode, $color, $fdrId = null)
  {
    if ($fdrId === null) {
      $flight = $this->em()->find('Entity\Flight', $flightId);
      $fdrId = $flight->getFdrId();
    }

    $paramInfo = $this->dic('fdr')->getParamByCode($fdrId, $paramCode);
    $param = null;
    if ($paramInfo['type'] == $this->dic('fdr')::getApType()) {
      $param = $this->dic('fdr')->getAnalogById($fdrId, intval($paramInfo['id']));
    } else if ($paramInfo['type'] == $this->dic('fdr')::getBpType()) {
      $param = $this->dic('fdr')->getBinaryById($fdrId, intval($paramInfo['id']));
    }

    $param->setColor($color);
    $this->em('fdrs')->merge($param);
    $this->em('fdrs')->flush();

    return json_encode('ok');
  }

}
