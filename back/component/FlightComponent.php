<?php

namespace Component;

use Exception;

class FlightComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Component\FdrComponent
     */
    private $fdrComponent;

    /**
     * @Inject
     * @var Entity\FdrAnalogParam
     */
    private $FdrAnalogParam;

    /**
     * @Inject
     * @var Entity\FdrBinaryParam
     */
    private $FdrBinaryParam;

    /**
     * @Inject
     * @var Entity\FlightSettlement
     */
    private $FlightSettlement;

    /**
     * @Inject
     * @var Entity\FlightEvent
     */
    private $FlightEvent;

    /**
     * @Inject
     * @var Entity\CalibrationParam
     */
    private $CalibrationParam;

    /**
     * @Inject
     * @var Entity\FlightEventOld
     */
    private $FlightEventOld;

    public function deleteFlight($flightId, $userId)
    {
        $criteria = ['id' => $flightId];

        if (!self::dic()->get('user')->isAdmin()) {
            $criteria['userId'] = $userId;
        }

        $flightToFolder = $this->em()
            ->getRepository('Entity\FlightToFolder')
            ->findBy($criteria);

        if (!$flightToFolder) {
            return;
        }

        foreach ($flightToFolder as $item) {
            $em->remove($item);
            $em->flush();
        }

        if (!App::rbac()->check('deleteFlightIrretrievably')) {
            return;
        }

        $flight = $this->em()
            ->getRepository('Entity\Flight')
            ->findOneBy([
                'id' => $flightId,
                'userId' => $userId
            ]);

        $fdr = $flight->getFdr();
        $guid = $flight->getGuid();

        $analogPrefixes = $fdrComponent->getAnalogPrefixes($fdr->getId());
        $binaryPrefixes = $fdrComponent->getBinaryPrefixes($fdr->getId());

        $tables = [];
        foreach ($analogPrefixes as $num) {
            $tables[] = '_' . $guid . '_' . $this->FdrAnalogParam::$prefix . '_' . $num;
        }

        foreach ($binaryPrefixes as $num) {
            $tables[] = '_' . $guid . '_' . $this->FdrBinaryParam::$prefix . '_' . $num;
        }

        $link = $this->connection()->create('flights');
        $tables[] = $this->FlightSettlement::getTable($link, $guid);
        $tables[] = $this->FlightEvent::getTable($link, $guid);
        $tables[] = $this->CalibrationParam::getTable($link, $guid);

        foreach ($tables as $table) {
            $this->connection()->drop($link, $table);
        }

        $this->connection()->destroy($link);

        $em->remove($flight);
        $em->flush();

        return true;
    }

    public static function getFlightEvents($flightId, $flightGuid = '')
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($sections), 1);
        }

        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. Integer is required. Passed: "
                . json_encode($sections), 1);
        }

        if ($flightGuid === '') {
            $flight = $em->find('Entity\Flight', $flightId);
            $flightGuid = $flight->getGuid();
        }

        $link = LinkFactory::create();
        $flightEventTable = FlightEvent::getTable($link, $flightGuid);
        $flightSettlementTable = FlightSettlement::getTable($link, $flightGuid);
        LinkFactory::destroy($link);

        if (!isset($flightEventTable)
            || !isset($flightSettlementTable)
        ) {
            return [];
        }

        $em = EM::get();
        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
        $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);
        $events = $em->getRepository('Entity\FlightEvent')->findAll();

        return $events;
    }

    public static function getFlightSettlements($flightId, $flightGuid = '')
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flight id passed", 1);
        }

        if ($flightGuid === '') {
            $em = EM::get();
            $flight = $em->find('Entity\Flight', $flightId);
            $flightGuid = $flight->getGuid();
        }

        $flightEvents = self::getFlightEvents($flightId, $flightGuid);

        $allFlightSettlements = [];
        foreach ($flightEvents as $flightEvent) {
            $flightSettlements = $flightEvent->getFlightSettlements();
            foreach ($flightSettlements as $flightSettlement) {
                $allFlightSettlements[] = $flightSettlement;
            }
        }

        return $allFlightSettlements;
    }


}
