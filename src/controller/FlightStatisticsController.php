<?php

namespace Controller;

class FlightStatisticsController extends BaseController
{
  public function getParamsAction($flightFilter) {
    $userId = $this->user()->getId();
    $flights = $this->dic('flight')->getFlightsByFilter($flightFilter, $userId);

    if ($flights === null) {
      return json_encode([]);
    }

    $flightSettlements = [];
    foreach ($flights as $flight) {
      $currentFlightSettlements = $this->dic('flight')->getFlightSettlements(
        $flight->getId(),
        $flight->getGuid()
      );

      foreach ($currentFlightSettlements as $flightSettlement) {
        $flightEventId = $flightSettlement->getFlightEvent()->getEventId();
        $eventSettlement = $this->em()->find('Entity\EventSettlement', $flightEventId);

        // to prevent duplications
        $flightSettlements[$eventSettlement->getId()] = $eventSettlement->getText();
      }
    }

    $resp = [];
    foreach($flightSettlements as $key => $val) {
      $resp[] = [
        'id' => $key,
        'text' => $val
      ];
    }

    return json_encode($resp);
  }

  public function getValuesAction($chosenParams, $flightFilter)
  {
    $userId = $this->user()->getId();
    $flights = $this->dic('flight')->getFlightsByFilter($flightFilter, $userId);

    if ($flights === null) {
      return json_encode([]);
    }

    $resp = $this->dic('event')
      ->buildSettlementsReport($chosenParams, $flights);

    return json_encode(array_values($resp));
  }
}
