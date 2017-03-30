<?php

namespace Controller;

use Component\EntityManagerComponent as EM;
use Component\FlightComponent;
use Entity\Flight;

class ResultsController extends CController
{
    public $curPage = 'resultsPage';

    private static $flightFilterArgs = [
        "fdr-type" => "",
        "bort" => "",
        "flight" => "",
        "departure-airport" => "",
        "arrival-airport" => "",
        "from-date" => "",
        "to-date" => ""
    ];

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    private function addFdrtypeCondition($qb, $val)
    {
        return $qb->expr()->eq('fl.id_fdr', $val);
    }

    private function addBortCondition($qb, $val)
    {
        return $qb->expr()->like('fl.bort', $qb->expr()->literal($val));
    }

    private function addFlightCondition($qb, $val)
    {
        return $qb->expr()->like('fl.voyage', $qb->expr()->literal($val));
    }

    private function addDepartureairportCondition($qb, $val)
    {
        return $qb->expr()->like('fl.departureAirport', $qb->expr()->literal($val));
    }

    private function addArrivalairportCondition($qb, $val)
    {
        return $qb->expr()->like('fl.arrivalAirport', $qb->expr()->literal($val));
    }

    private function addFromdateCondition($qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return false;
        }

        return $qb->expr()->gte('fl.startCopyTime', $timestamp);
    }

    private function addTodateCondition($qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return false;
        }

        return $qb->expr()->lte('fl.startCopyTime', $timestamp);
    }

    public function getSettlements($args)
    {
        $em = EM::get();

        $qb = $em->createQueryBuilder();
        $andX = $qb->expr()->andX();

        foreach ($args as $key => $val) {
            $method = 'add' . ucfirst(str_replace('-', '', $key)) . 'Condition';
            if(!method_exists($this, $method)) {
                continue;
            }

            $expr = $this->$method($qb, $val);
            if ($expr === false) {
                continue;
            }

            $andX->add($expr);
        }

        $flights = $qb->select('fl')
            ->from('Entity\Flight', 'fl')
            ->where($andX)
            ->getQuery()
            ->getResult();

        $flightSettlements = [];
        foreach ($flights as $flight) {
            $currentFlightSettlements = FlightComponent::getFlightSettlements(
                $flight->getId(),
                $flight->getGuid()
            );

            foreach ($currentFlightSettlements as $flightSettlement) {
                $eventSettlement = $flightSettlement->getEventSettlement();
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

        echo json_encode($resp);
    }
}
