<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class FlightDataController extends BaseController
{
  const GEO_DATA_CUTTER = 100;

  public function getApParamDataAction(
    $flightId,
    $paramApCode,
    $startFrame,
    $endFrame,
    $totalSeriesCount,
    $noCompression = false
  ) {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('flightId: '.$flightId);
    }

    $startCopyTime = $flight->getStartCopyTime();
    $timing = $this->dic('flight')->getFlightTiming($flight->getId());
    $framesCount = $timing['framesCount'];

    if ($startFrame == null) {
      $startFrame = 0;
    }

    if ($startFrame == null) {
      $endFrame = $framesCount;
    }

    if ($endFrame > $framesCount) {
      $endFrame = $framesCount;
    }

    if ($totalSeriesCount == null) {
      $totalSeriesCount = 1;
    }

    $param = $this->dic('fdr')->getParamByCode(
      $flight->getFdrId(),
      $paramApCode
    );

    $pointsMaxCount = $this->dic('userSettings')->getSettingValue('pointsMaxCount');

    $compression = $this->dic('channel')::getNoCompressionType();
    if (!$noCompression && (($startFrame !== 0) || ($endFrame !== $framesCount))) {
      $compression = $this->dic('channel')::getAroundRangeCompressionType();
    } else if (!$noCompression && ($framesCount * $totalSeriesCount > $pointsMaxCount)) {
      $compression = $this->dic('channel')::getGeneralCompressionType();
    }

    $table = $this->dic('fdr')->getAnalogTable($flight->getGuid(), $param['prefix']);

    $syncParam = $this->dic('channel')->getRange(
      $table,
      $paramApCode,
      $startFrame,
      $endFrame,
      $totalSeriesCount,
      $framesCount,
      $pointsMaxCount,
      $compression
    );

    return json_encode($syncParam);
  }

  public function getBpParamDataAction($flightId, $code)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $param = $this->dic('fdr')->getParamByCode(
      $flight->getFdrId(),
      $code
    );

    $table = $this->dic('fdr')->getBinaryTable(
      $flight->getGuid(),
      $param['prefix']
    );

    return json_encode($this->dic('channel')->getBinary(
      $table,
      $code,
      $flight->getFdr()->getStepLength(),
      $param['frequency']
    ));
  }

  public function getGeoAction($flightId)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException("flightId: ".$flightId);
    }

    $stepDivider = $flight->getFdr()->getStepDivider();
    $startCopyTime = $flight->getStartCopyTime();
    $stepLength = $flight->getFdr()->getStepLength();
    $timing = $this->dic('flight')->getFlightTiming($flightId);
    $framesCount = $timing['framesCount'];
    $startFrame = 0;
    $endFrame = $framesCount;

    $timeline = $this->dic()
      ->get('channel')
      ->normalizeTimestamp(
        $stepDivider,
        $stepLength,
        $framesCount,
        $startCopyTime,
        $startFrame,
        $endFrame,
        self::GEO_DATA_CUTTER
      );

    $posParams = $this->dic('fdr')->getPosParams();
    $result = [];
    for ($ii = 0; $ii < count($posParams); $ii++) {
      $param = $this->dic('fdr')
        ->getParamByCode($flight->getFdrId(), $posParams[$ii]);

      $table = $flight->getGuid().'_'.$this->dic('fdr')->getApType().'_'.$param['prefix'];

      $result[$posParams[$ii]] = $this->dic('channel')
        ->getNormalizedApParam(
          $table,
          $stepDivider,
          $param['code'],
          $param['frequency'],
          $startFrame,
          $endFrame
        );
    }

    if (isset($result['LAT_DEG'])
      && isset($result['LAT_MIN'])
      && (count($result['LAT_DEG']) === count($result['LAT_MIN']))
    ) {
      $result['latitude'] = [];

      for ($ii = 0; $ii < count($result['LAT_DEG']); $ii+=self::GEO_DATA_CUTTER) {
        if (intval($result['LAT_NORTH'][$ii]) === 0) {
          $result['latitude'][] =
            $result['LAT_DEG'][$ii] - ($result['LAT_MIN'][$ii] / 60);
        } else {
          $result['latitude'][] =
            $result['LAT_DEG'][$ii] + ($result['LAT_MIN'][$ii] / 60);
        }
      }
    }

    if (isset($result['LONG_DEG'])
      && isset($result['LONG_MIN'])
      && (count($result['LONG_DEG']) === count($result['LONG_MIN']))
    ) {
      $result['longitude'] = [];

      for ($ii = 0; $ii < count($result['LONG_DEG']); $ii+=self::GEO_DATA_CUTTER) {
        if (intval($result['LONG_EAST'][$ii]) === 0) {
          $result['longitude'][] =
            $result['LONG_DEG'][$ii] - ($result['LONG_MIN'][$ii] / 60);
        } else {
          $result['longitude'][] =
            $result['LONG_DEG'][$ii] + ($result['LONG_MIN'][$ii] / 60);
        }
      }
    }

    if (isset($result['HG'])) {
      $result['altitude'] = [];

      for ($ii = 0; $ii < count($result['HG']); $ii+=self::GEO_DATA_CUTTER) {
        $result['altitude'][] = $result['HG'][$ii];
      }
    }

    if (isset($result['KK'])) {
      $result['yaw'] = [];

      for ($ii = 0; $ii < count($result['KK']); $ii+=self::GEO_DATA_CUTTER) {
        $result['yaw'][] = $result['KK'][$ii];
      }
    }

    if (isset($result['KR'])) {
      $result['roll'] = [];

      for ($ii = 0; $ii < count($result['KR']); $ii+=self::GEO_DATA_CUTTER) {
        $result['roll'][] = $result['KR'][$ii];
      }
    }

    if (isset($result['TG'])) {
      $result['pitch'] = [];

      for ($ii = 0; $ii < count($result['TG']); $ii+=self::GEO_DATA_CUTTER) {
        $result['pitch'][] = $result['TG'][$ii];
      }
    }

    return json_encode(
      array_merge([
          'timeline' => $timeline,
          'modelUrl' => $flight->getFdr()->getModelUrl()
        ],
        $result
      )
    );
  }
}
