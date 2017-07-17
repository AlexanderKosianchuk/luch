<?php

namespace Controller;

use Component\EntityManagerComponent as EM;
use Component\FlightComponent;
use Component\RealConnectionFactory as LinkFactory;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Entity\Flight;
use Entity\Fdr;
use Entity\FlightSettlement;

use Exception\BadRequestException;

use \ReflectionMethod;

class ResultsController extends CController
{
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

    private static function addFdrtypeCondition(&$qb, $fdrName)
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

    private static function addBortCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.bort', $qb->expr()->literal($val))
        );

        return 1;
    }

    private static function addFlightCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.voyage', $qb->expr()->literal($val))
        );

        return 1;
    }

    private static function addDepartureairportCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.departureAirport', $qb->expr()->literal($val))
        );

        return 1;
    }

    private static function addArrivalairportCondition(&$qb, $val)
    {
        if (empty($val)) {
            return 0;
        }

        $qb->andWhere(
            $qb->expr()->like('fl.arrivalAirport', $qb->expr()->literal($val))
        );
        return 1;
    }

    private static function addFromdateCondition(&$qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return 0;
        }

        $qb->andWhere($qb->expr()->gte('fl.startCopyTime', $timestamp));

        return 1;
    }

    private static function addTodateCondition($qb, $val)
    {
        $timestamp = strtotime($val);

        if ($timestamp === false) {
            return 0;
        }

        $qb->andWhere($qb->expr()->lte('fl.startCopyTime', $timestamp));

        return 1;
    }

    private static function getFlightsByFilter($filter, $userId = null)
    {
        if (!$userId) {
            $userId = intval($this->_user->userInfo['id']);
        }

        $em = EM::get();

        $qb = $em->createQueryBuilder()
            ->select('fl')
            ->from('Entity\Flight', 'fl');
        $conditionsCount = 0;

        foreach ($filter as $key => $val) {
            $method = 'add' . ucfirst(str_replace('-', '', $key)) . 'Condition';

            if (!method_exists('\Controller\ResultsController', $method)) {
                continue;
            }

            $conditionsCount += self::$method($qb, $val);
        }

        if ($conditionsCount === 0) {
            return null;
        }

        return $qb
            ->join(
                '\Entity\FlightToFolder',
                'flightToFolders',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'fl.id = flightToFolders.flightId'
            )
            ->andWhere($qb->expr()->eq('flightToFolders.userId', $userId))
            ->getQuery()
            ->getResult();
    }

    public function getSettlements($args)
    {
        $userId = intval($this->_user->userInfo['id']);
        $flights = self::getFlightsByFilter($args, $userId);

        if ($flights === null) {
            return json_encode([]);
        }

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

        return json_encode($resp);
    }

    public function getReport($args)
    {
        if (!isset($args['chosenSettlements'])
            || !isset($args['flightFilter'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $settlements = json_decode(html_entity_decode($args['chosenSettlements']), true);
        $flightFilter = json_decode(html_entity_decode($args['flightFilter']), true);

        $userId = intval($this->_user->userInfo['id']);
        $flights = self::getFlightsByFilter($flightFilter, $userId);
        $report = [];
        $em = EM::get();

        foreach ($flights as $flight) {
            $flightGuid = $flight->getGuid();

            $link = LinkFactory::create();
            $flightSettlementTable = FlightSettlement::getTable($link, $flightGuid);

            LinkFactory::destroy($link);

            $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);

            foreach ($settlements as $settlementId) {
                if (!isset($report[$settlementId])) {
                    $report[$settlementId] = [];
                }

                $flightSettlements = $em->getRepository('Entity\FlightSettlement')->findBy([
                    'settlementId' => $settlementId
                ]);

                for ($ii = 0; $ii < count($flightSettlements); $ii++) {
                    $flightSettlement = $flightSettlements[$ii];

                    if (!isset($report[$settlementId]['text'])
                        && ($ii === 0)
                    ) {
                        $report[$settlementId]['text']
                            = $flightSettlement->getEventSettlement()->getText();
                        $report[$settlementId]['values'] = [];
                    }

                    $report[$settlementId]['values'][] = $flightSettlement->getValue();
                }
            }
        }

        $resp = [];
        foreach ($report as $item) {
            $resp[] = $item;
        }

        return json_encode($resp);
    }
}
