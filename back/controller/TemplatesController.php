<?php

namespace Controller;

use Entity\FdrTemplate;
use Entity\FlightEvent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;

class TemplatesController extends BaseController
{
  public function getFlightTemplatesAction($flightId)
  {
    $flightId = intval($flightId);
    $userId = $this->user()->getId();

    $flight = $this->em()->find('Entity\Flight', $flightId);
    $fdr = $flight->getFdr();

    $isExist = $this->connection()->isExist($fdr->getCode().FdrTemplate::getPrefix());

    if (!$isExist) {
      $this->dic()->get('fdrTemplate')->createFdrTemplateTable($fdr->getCode());
    }

    $templatesToSend = $this->dic()->get('fdrTemplate')->getTemplates($flightId, true);
    $flightEvents = $this->dic()->get('event')->getFlightEvents($flightId);

    //if performed exception search and isset events
    if (count($flightEvents) > 0) {
      $codesArr = [];
      $params = [];
      foreach ($flightEvents as $event) {
        $paramDesc = $this->dic()->get('fdr')
          ->getParamByCode(
            $fdr->getId(),
            $event['refParam']
          );

        if (!empty($paramDesc) && !in_array($event['refParam'], $codesArr)) {
          $codesArr[] = $event['refParam'];
          $params[] = $paramDesc;
        }
      }

      $this->dic()->get('fdrTemplate')->create(
        $fdr->getCode(),
        $this->dic()->get('fdrTemplate')::getEventsName(),
        $params
      );

      $templatesToSend[] = [
        'name' =>  $this->dic()->get('fdrTemplate')::getEventsName(),
        'paramCodes' => $codesArr,
        'params' => $params,
        'servicePurpose' => [
          'isEvents' => true
        ]
      ];
    }

    return json_encode($templatesToSend);
  }

  public function getTemplateAction($flightId, $templateName)
  {
    $flightId = intval($flightId);

    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $fdrTemplateParams = $this->dic()->get('fdrTemplate')->getTemplateByName(
      $flight->getFdr()->getCode(),
      $templateName
    );

    $analogParams = [];
    $binaryParams = [];
    foreach ($fdrTemplateParams as $templateParam) {
      $param = $this->dic()->get('fdr')->getParamByCode(
        $flight->getFdrId(),
        $templateParam->getParamCode()
      );

      if ($param['type'] === $this->dic()->get('fdr')->getApType()) {
        $analogParams[] = $param;
      }

      if ($param['type'] === $this->dic()->get('fdr')->getBpType()) {
        $binaryParams[] = $param;
      }
    }

    return json_encode([
      'name' => $templateName,
      'ap' => $analogParams,
      'bp' => $binaryParams,
      'servisePurpose' => (($this->dic()->get('fdrTemplate')->isDefault($templateName))
        ? ['isDefault' => true]
        : false
      )
    ]);
  }

  public function setTemplateAction(
    $flightId,
    $templateName,
    $analogParams,
    $binaryParams = []
  ) {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $paramsWithType = [];
    foreach ($analogParams as $item) {
      $param = $this->dic()
        ->get('fdr')
        ->getAnalogById(
          $flight->getFdrId(),
          intval($item['id'])
        );

      $table = $flight->getGuid().'_'.$this->dic()->get('fdr')->getApType().'_'.$param->getPrefix();
      $minMax = $this->dic()
        ->get('channel')
        ->getParamMinMax(
          $table,
          $param->getCode(),
          $this->dic()->get('fdr')->getApType()
        );

      $paramsWithType[$this->dic()->get('fdr')->getApType()][] = [
        'code' => $param->getCode(),
        'min' => $minMax['min'],
        'max' => $minMax['max']
      ];
    }

    foreach ($binaryParams as $item) {
      if (isset($item['id'])) {
        $param = $this->dic()
          ->get('fdr')
          ->getBinaryById(
            $flight->getFdrId(),
            intval($item['id'])
          );

        $paramsWithType[$this->dic()->get('fdr')->getBpType()][] = [
          'code' => $param->getCode(),
          'min' => 0,
          'max' => 1
        ];
      }
    }

    $link = $this->connection()->create('fdrs');
    $table = \Entity\FdrTemplate::getTable($link, $flight->getFdrCode());
    $this->connection()->destroy($link);

    //if no template table - create it
    if ($table === null) {
      $this->dic()
        ->get('fdrTemplate')
        ->createFdrTemplateTable($flight->getFdrCode());
    }

    $this->dic()
      ->get('fdrTemplate')
      ->delete(
        $flight->getFdrCode(),
        $templateName,
        $this->user()->getId()
    );

    $this->dic()
      ->get('fdrTemplate')
      ->createWithDistributedParams(
        $flight->getFdrCode(),
        $templateName,
        $paramsWithType
      );

    return json_encode('ok');
  }

  public function removeTemplateAction($flightId, $templateName)
  {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $this->dic()
      ->get('fdrTemplate')
      ->delete($flight->getFdrCode(), $templateName);

    return json_encode('ok');
  }

  public function mergeTemplatesAction(
    $flightId,
    $resultTemplateName,
    $templatesToMerge
  ) {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $templatesToMerge = json_decode(html_entity_decode($templatesToMerge));

    $paramCodes = [];
    foreach ($templatesToMerge as $templateName) {
      $templateRows = $this->dic()
        ->get('fdrTemplate')
        ->getTemplateByName($flight->getFdrCode(), $templateName);

      foreach ($templateRows as $row) {
        if (!in_array($row->getParamCode(), $paramCodes)) {
          $paramCodes[] = $row->getParamCode();
        }
      }
    }

    $templatesParams = [];
    foreach ($paramCodes as $code) {
      $paramForTemplate = [
        'code' => $code,
        'min' => 0,
        'max' => 1
      ];

      $param = $this->dic()->get('fdr')->getParamByCode(
        $flight->getFdrId(),
        $code
      );

      if ($param['type'] === $this->dic()->get('fdr')->getApType()) {
        $table = $flight->getGuid().'_'.$this->dic()->get('fdr')->getApType().'_'.$param['prefix'];
        $minMax = $this->dic()
          ->get('channel')
          ->getParamMinMax(
            $table,
            $code
          );

        $paramForTemplate['min'] = $minMax['min'];
        $paramForTemplate['max'] = $minMax['max'];
      }

      $templatesParams[$param['type']][] = $paramForTemplate;
    }

    $this->dic()
      ->get('fdrTemplate')
      ->delete(
        $flight->getFdrCode(),
        $resultTemplateName,
        $this->user()->getId()
    );

    $this->dic()
      ->get('fdrTemplate')
      ->createWithDistributedParams(
        $flight->getFdrCode(),
        $resultTemplateName,
        $templatesParams
      );

    return json_encode('ok');
  }
}
