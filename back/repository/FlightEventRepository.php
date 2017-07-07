<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Component\RealConnectionFactory as LinkFactory;

use Exception;

class FlightEventRepository extends EntityRepository
{
    public function getFlightEvents($flightGuid)
    {
        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
                . json_encode($flightGuid), 1);
        }

        $em = $this->getEntityManager();

        $link = LinkFactory::create();
        $flightEventTable = FlightEvent::getTable($link, $flightGuid);
        $flightSettlementTable = FlightSettlement::getTable($link, $flightGuid);
        LinkFactory::destroy($link);

        if ($flightEventTable === null) {
            return null;
        }

        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
        $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);

        $qb = $em->createQueryBuilder();

        return $qb->select(['flightEvent', 'event', 'flightSettlement', 'settlement'])
           ->from('Entity\FlightEvent', 'flightEvent')
           ->leftJoin('flightEvent.event', 'event')
           ->leftJoin('flightEvent.flightSettlements', 'flightSettlement')
           ->leftJoin('flightSettlement.eventSettlement', 'settlement')
           ->orderBy('flightEvent.id', 'ASC')
           ->getQuery()
           ->getArrayResult();
    }

    public function getFormatedFlightEvents ($flightGuid, $isDisabled, $startCopyTime, $stepLength)
    {
        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
                . json_encode($flightGuid), 1);
        }

        $flightEvents = $this->getFlightEvents($flightGuid) ?? [];
        $formated = [];

        foreach ($flightEvents as $flightEvent) {
            $settlements = $flightEvent['flightSettlements'];
            $formatedSettlements = [];

            foreach ($settlements as $settlement) {
                $formatedSettlements[] = $settlement['eventSettlement']['text'] . ' = ' . $settlement['value'];
            }

            $formated[] = [
                'id' => $flightEvent['id'],
                'refParam' => $flightEvent['event']['refParam'],
                'frameNum' => (intval(substr($flightEvent['startTime'], 0, -3)) - $startCopyTime) * $stepLength,
                'endFrameNum' => (intval(substr($flightEvent['endTime'], 0, -3)) - $startCopyTime) * $stepLength,
                'start' => date('H:i:s', intval(substr($flightEvent['startTime'], 0, -3))),
                'end' => date('H:i:s', intval(substr($flightEvent['endTime'], 0, -3))),
                'duration' => gmdate('H:i:s',
                       (($flightEvent['endTime']
                           - $flightEvent['startTime'])
                       / 1000)
                   ),
                'code' => $flightEvent['event']['code'],
                'comment' => $flightEvent['event']['comment'],
                'algText' => $flightEvent['event']['algText'],
                'status' => $flightEvent['event']['status'],
                'excAditionalInfo' => $formatedSettlements,
                'reliability' => (intval($flightEvent['falseAlarm']) === 0),
                'isDisabled' => $isDisabled,
                'userComment' => '',
                'eventType' => 2
            ];
        }

        return $formated;
    }

    public function updateFalseAlarm($flightGuid, $eventId, $value)
    {
        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
                . json_encode($flightGuid), 1);
        }

        if (!is_int($eventId)) {
            throw new Exception("Incorrect eventId passed. Integer is required. Passed: "
                . json_encode($eventId), 1);
        }

        if (!is_bool($value)) {
            throw new Exception("Incorrect false alarm value passed. Boolean is required. Passed: "
                . json_encode($value), 1);
        }

        $em = $this->getEntityManager();

        $link = LinkFactory::create();
        $flightEventTable = FlightEvent::getTable($link, $flightGuid);
        LinkFactory::destroy($link);

        if ($flightEventTable === null) {
            return null;
        }

        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);

        $flightEvent = $this->findOneBy(['id' => $eventId]);

        $flightEvent->setFalseAlarm($value);
        $em->persist($flightEvent);
        $em->flush();
    }
}
