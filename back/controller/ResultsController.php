<?php

namespace Controller;

use Component\EntityManagerComponent as EM;
use Component\FlightComponent;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Entity\Flight;
use Entity\Fdr;

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

    private function addFdrtypeCondition(&$qb, $fdrName)
    {
        if (empty($fdrName)) {
            return 0;
        }

        $em = EM::get();

        $result = $em->getRepository('Entity\Fdr')->createQueryBuilder('fdr')
           ->andWhere('fdr.name LIKE :fdrName')
           ->setParameter('fdrName', '%'.$fdrName.'%')
           ->getQuery()
           ->getResult();

        $fdrs = new ArrayCollection($result);

        $count = 0;

        foreach ($fdrs as $fdr) {
            $qb->orWhere(
                $qb->expr()->eq('fl.id_fdr', $fdr->getId())
            );

            $count++;
        }

        return $count;
    }

    private function addBortCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.bort', $qb->expr()->literal($val))
        );

        return 1;
    }

    private function addFlightCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.voyage', $qb->expr()->literal($val))
        );

        return 1;
    }

    private function addDepartureairportCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.departureAirport', $qb->expr()->literal($val))
        );

        return 1;
    }

    private function addArrivalairportCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.arrivalAirport', $qb->expr()->literal($val))
        );
        return 1;
    }

    private function addFromdateCondition(&$qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return 0;
        }

        $qb->andWhere($qb->expr()->gte('fl.startCopyTime', $timestamp));

        return 1;
    }

    private function addTodateCondition($qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return 0;
        }

        $condition = $qb->expr()->lte('fl.startCopyTime', $timestamp);
        $expr->andX()->add($condition);

        return 1;
    }

    public function getSettlements($args)
    {
        $em = EM::get();

        $qb = $em->createQueryBuilder()
            ->select('fl')
            ->from('Entity\Flight', 'fl');
        $conditionsCount = 0;

        foreach ($args as $key => $val) {
            $method = 'add' . ucfirst(str_replace('-', '', $key)) . 'Condition';
            if (!method_exists($this, $method)) {
                continue;
            }

            $conditionsCount += $this->$method($qb, $val);
        }

        if ($conditionsCount === 0) {
            echo json_encode([]);
            exit;
        }

        $flights = $qb->getQuery()->getResult();

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
        exit;
    }
}
