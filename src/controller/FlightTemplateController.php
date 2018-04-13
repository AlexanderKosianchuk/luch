<?php

namespace Controller;

use Entity\FdrTemplate;
use Entity\FlightEvent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;

class FlightTemplateController extends BaseController
{
  public function getAllAction($flightId)
  {
    $flightId = intval($flightId);
    $userId = $this->user()->getId();

    $flight = $this->em()->find('Entity\Flight', $flightId);
    $fdr = $flight->getFdr();

    $isExist = $this->connection()->isExist($fdr->getCode().FdrTemplate::getPrefix());

    if (!$isExist) {
      $this->dic('fdrTemplate')->createFdrTemplateTable($fdr->getCode());
    }

    $templatesToSend = $this->dic('fdrTemplate')
      ->getFdrTemplates($flight->getFdr()->getId(), true);

    $flightEvents = $this->dic('event')->getFlightEvents($flight);

    $codesArr = [];
    foreach ($flightEvents as $event) {
      if (!in_array($event['refParam'], $codesArr)) {
        $codesArr[] = $event['refParam'];
      }
    }

    $params = $this->dic('fdr')
      ->getParamsByCodes(
        $fdr->getId(),
        $codesArr
      );

    if (count($params) > 0) {
      $paramsWithMeasure = $this->dic()
        ->get('channel')
        ->getParamsMinMax(
          $flight->getGuid().'_'.$this->dic('fdr')->getApType(),
          $params,
          $this->dic('fdr')->getApType()
        );

      $this->dic()
        ->get('fdrTemplate')
        ->delete(
          $fdr->getCode(),
          $this->dic('fdrTemplate')::getEventsName(),
          $this->user()->getId()
      );

      $createdEventsTemplate = $this->dic()
        ->get('fdrTemplate')
        ->createWithDistributedParams(
          $fdr->getCode(),
          $this->dic('fdrTemplate')::getEventsName(),
          $paramsWithMeasure
        );

      $templatesToSend[] = $createdEventsTemplate;
    }

    return json_encode($templatesToSend);
  }

  public function getAction($flightId, $templateId)
  {
    $flightId = intval($flightId);

    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $template = $this->dic('fdrTemplate')->getTemplateById(
      $flight->getFdr()->getId(),
      $templateId
    );

    return json_encode($template);
  }

  public function setAction(
    $flightId,
    $templateName,
    $analogParams,
    $binaryParams = [],
    $templateId = null
  ) {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $link = $this->connection()->create('fdrs');
    $table = \Entity\FdrTemplate::getTable($link, $flight->getFdrCode());
    $this->connection()->destroy($link);

    //if no template table - create it
    if ($table === null) {
      $this->dic()
        ->get('fdrTemplate')
        ->createFdrTemplateTable($flight->getFdrCode());
    } else {
      if ($templateId !== null) {
        $this->dic()
          ->get('fdrTemplate')
          ->deleteById(
            $flight->getFdrCode(),
            $templateId,
            $this->user()->getId()
          );
      }
    }

    $fdrId = $flight->getFdr()->getId();
    $paramsWithType = [];
    foreach ($analogParams as $analogParam) {
      $paramsWithType[] = $this->dic()
        ->get('fdr')
        ->getAnalogById($fdrId, $analogParam['id'])
        ->get(true);
    }

    foreach ($binaryParams as $binaryParam) {
      $paramsWithType[] = $this->dic()
        ->get('fdr')
        ->getBinaryById($fdrId, $binaryParam['id'])
        ->get(true);
    }

    $paramsWithMeasure = $this->dic()
      ->get('channel')
      ->getParamsMinMax(
        $flight->getGuid().'_'.$this->dic('fdr')->getApType(),
        $paramsWithType,
        $this->dic('fdr')->getApType()
      );

    $this->dic()
      ->get('fdrTemplate')
      ->delete(
        $flight->getFdrCode(),
        $templateName,
        $this->user()->getId()
      );

    $template = $this->dic()
      ->get('fdrTemplate')
      ->createWithDistributedParams(
        $flight->getFdrCode(),
        $templateName,
        $paramsWithMeasure
      );

    return json_encode($template);
  }

  public function deleteAction($flightId, $templateId)
  {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $this->dic()
      ->get('fdrTemplate')
      ->deleteById($flight->getFdrCode(), $templateId);

    return json_encode([
      'id' => $templateId
    ]);
  }

  public function mergeAction(
    $flightId,
    $resultTemplateName,
    $templatesToMerge
  ) {
    $flight = $this->em()->find('Entity\Flight', intval($flightId));

    if (!$flight) {
      throw new NotFoundException('fligth id: '.$flightId);
    }

    $templatesToMerge = json_decode(html_entity_decode($templatesToMerge), true);

    $paramCodes = [];
    foreach ($templatesToMerge as $template) {
      $templateWithParams = $this->dic()
        ->get('fdrTemplate')
        ->getTemplateById(
          $flight->getFdr()->getId(),
          $template['id']
        );

      foreach ($templateWithParams['params'] as $param) {
        if (!in_array($param['code'], $paramCodes)) {
          $paramCodes[] = $param['code'];
        }
      }
    }

    $params = $this->dic('fdr')
      ->getParamsByCodes(
        $flight->getFdr()->getId(),
        $paramCodes
      );

    $paramsWithMeasure = $this->dic()
      ->get('channel')
      ->getParamsMinMax(
        $flight->getGuid().'_'.$this->dic('fdr')->getApType(),
        $params,
        $this->dic('fdr')->getApType()
      );

    $this->dic()
      ->get('fdrTemplate')
      ->delete(
        $flight->getFdrCode(),
        $resultTemplateName,
        $this->user()->getId()
    );

    $resultTemplate = $this->dic()
      ->get('fdrTemplate')
      ->createWithDistributedParams(
        $flight->getFdrCode(),
        $resultTemplateName,
        $paramsWithMeasure
      );

    return json_encode($resultTemplate);
  }
}
