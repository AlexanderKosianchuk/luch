<?php

namespace Component;

use Exception;

class EventComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Entity\FdrEventOld
   */
  private $FdrEventOld;

  /**
   * @Inject
   * @var Entity\FlightEventOld
   */
  private $FlightEventOld;

  /**
   * @Inject
   * @var Entity\FlightEvent
   */
  private $FlightEvent;

  /**
   * @Inject
   * @var Entity\FlightSettlement
   */
  private $FlightSettlement;

  public function createOldEventsTable($guid)
  {
    if (!is_string($guid)) {
      throw new Exception("Incorrect guid passed. String is required. Passed: "
        . json_encode($guid), 1);
    }

    $flightExTableName = $guid.$this->FlightEventOld::getPrefix();

    $link = $this->connection()->create('flights');

    $this->connection()->drop($flightExTableName, null, $link);

    $query = "CREATE TABLE `".$flightExTableName."` (`id` INT NOT NULL AUTO_INCREMENT, "
      . " `frameNum` INT,"
      . " `startTime` BIGINT,"
      . " `endFrameNum` INT,"
      . " `endTime` BIGINT,"
      . " `refParam` VARCHAR(255),"
      . " `code` VARCHAR(255),"
      . " `excAditionalInfo` TEXT,"
      . " `falseAlarm` BOOL DEFAULT 0,"
      . " `userComment` TEXT,"
      . " PRIMARY KEY (`id`))"
      . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";

    $stmt = $link->prepare($query);
    $stmt->execute();
    $stmt->close();

    $this->connection()->destroy($link);

    return $flightExTableName;
  }

  public function createEventsTable($guid)
  {
    if (!is_string($guid)) {
      throw new Exception("Incorrect guid passed. String is required. Passed: "
        . json_encode($guid), 1);
    }

    $dynamicTableName = $guid.$this->FlightEvent::getPrefix();
    $link = $this->connection()->create('flights');
    $this->connection()->drop($dynamicTableName, null, $link);

    $query = "SHOW TABLES LIKE '".$dynamicTableName."';";
    $result = $link->query($query);
    if (!$result->fetch_array()) {
      $query = "CREATE TABLE `".$dynamicTableName."` ("
        . "`id` BIGINT NOT NULL AUTO_INCREMENT, "
        . "`start_time` BIGINT(20) NOT NULL, "
        . "`end_time` BIGINT(20) NOT NULL, "
        . "`id_event` BIGINT(20) NOT NULL, "
        . "`false_alarm` BOOLEAN NOT NULL, "
        . " INDEX (`id_event`), "
        . " PRIMARY KEY (`id`)) "
        . " ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";
      $stmt = $link->prepare($query);
      if (!$stmt->execute()) {
        throw new Exception("FlightEvent dynamic table creation query failed. Query: "
          . $query, 1);
      }
    } else {
      $query = "DELETE FROM `".$dynamicTableName."` WHERE 1;";
      $stmt = $link->prepare($query);
      if (!$stmt->execute()) {
        throw new Exception("FlightEvent dynamic table truncating query failed. Query: "
          . $query, 1);
      }
    }

    $this->connection()->destroy($link);

    return $dynamicTableName;
  }

  public function createSettlementsTable($guid)
  {
    if (!is_string($guid)) {
      throw new Exception("Incorrect guid passed. String is required. Passed: "
        . json_encode($guid), 1);
    }

    $dynamicTableName = $guid.$this->FlightSettlement::getPrefix();
    $link = $this->connection()->create('flights');
    $this->connection()->drop($dynamicTableName, null, $link);

    $query = "SHOW TABLES LIKE '".$dynamicTableName."';";
    $result = $link->query($query);
    if (!$result->fetch_array()) {
      $query = "CREATE TABLE `".$dynamicTableName."` ("
        . "`id` BIGINT NOT NULL AUTO_INCREMENT, "
        . "`id_event` BIGINT(20) NOT NULL, "
        . "`id_settlement` BIGINT(20) NOT NULL, "
        . "`id_flight_event` BIGINT(20) NOT NULL, "
        . "`value` VARCHAR(20) NOT NULL, "
        . " INDEX (`id_event`), "
        . " INDEX (`id_settlement`), "
        . " INDEX (`id_flight_event`), "
        . " PRIMARY KEY (`id`)) "
        . " ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB;";
      $stmt = $link->prepare($query);
      if (!$stmt->execute()) {
        throw new Exception("FlightSettlement dynamic table creation query failed. Query: "
          . $query, 1);
      }
    } else {
      $query = "DELETE FROM `".$dynamicTableName."` WHERE 1;";
      $stmt = $link->prepare($query);
      if (!$stmt->execute()) {
        throw new Exception("FlightSettlement dynamic table truncating query failed. Query: "
          . $query, 1);
      }
    }

    $this->connection()->destroy($link);

    return $dynamicTableName;
  }

  public function isOldProcessingEventsTableExist($code)
  {
    $table = $code.$this->FdrEventOld::getPrefix();

    return $this->connection()->isExist($table, 'fdrs');
  }

  private function setupFdrEventOldEntity($code)
  {
    $link = $this->connection()->create('fdrs');
    $table = $this->FdrEventOld::getTable($link, $code);
    $this->connection()->destroy($link);

    $this->em('fdrs')
      ->getClassMetadata('Entity\FdrEventOld')
      ->setTableName($table);
  }

  private function setupFlightEventOldEntity($code)
  {
    $link = $this->connection()->create('flights');
    $table = $this->FlightEventOld::getTable($link, $code);
    $this->connection()->destroy($link);

    $this->em('fdrs')
      ->getClassMetadata('Entity\FlightEventOld')
      ->setTableName($table);
  }

  private function setupFlightEventEntity($code)
  {
    $link = $this->connection()->create('flights');
    $table = $this->FlightEvent::getTable($link, $code);
    $this->connection()->destroy($link);

    $this->em('flights')
      ->getClassMetadata('Entity\FlightEvent')
      ->setTableName($table);
  }

  private function setupFlightSettlementEntity($code)
  {
    $link = $this->connection()->create('flights');
    $table = $this->FlightSettlement::getTable($link, $code);
    $this->connection()->destroy($link);

    $this->em('flights')
      ->getClassMetadata('Entity\FlightSettlement')
      ->setTableName($table);
  }


  public function getRefParams($code)
  {
    $this->setupFdrEventOldEntity($code);

    return $this->em('fdrs')
      ->getRepository('Entity\FdrEventOld')
      ->createQueryBuilder('fdrEventOld')
      ->select('DISTINCT fdrEventOld.refParam')
      ->getQuery()
      ->getResult();
  }

  public function getOldEvents($code)
  {
    $this->setupFdrEventOldEntity($code);

    return $this->em('fdrs')
      ->getRepository('Entity\FdrEventOld')
      ->createQueryBuilder('fdrEventOld')
      ->getQuery()
      ->getResult();
  }

  public function getFlightEventsByRefParam(
    $flight,
    $refParam
  ) {
    $flightEventsOld = $this->getFlightEventsOldExtended(
      $flight->getId(),
      $flight->getGuid(),
      $flight->getStartCopyTime(),
      $flight->getFdr()->getStepLength(),
      false,
      $refParam
    );

    $flightEvents = $this->getFlightEventsExtended(
      $flight->getGuid(),
      $flight->getStartCopyTime(),
      $flight->getFdr()->getStepLength(),
      false,
      $refParam
    );

    return array_merge($flightEventsOld, $flightEvents);
  }

  public function getFlightEvents ($flightId)
  {
    $flight = $this->em()->find('Entity\Flight', $flightId);

    $fdr = $flight->getFdr();
    $this->setupFdrEventOldEntity($fdr->getCode());
    $this->setupFlightEventOldEntity($flight->getGuid());

    $oldEvents = $this->em('flights')
      ->getRepository('Entity\FlightEventOld')->findAll();

    $eventsArray = [];

    foreach ($oldEvents as $event) {
      $eventsArray[] = $event->get(true);
    }

    $this->setupFlightEventEntity($flight->getGuid());
    $this->setupFlightSettlementEntity($flight->getGuid());

    $flightEvents = $this->em('flights')
      ->getRepository('Entity\FlightEvent')->findAll();

    if (count($flightEvents) === 0) {
      return [];
    }

    $ids = [];
    foreach ($flightEvents as $event) {
      $ids[] = $event->getId();
    }

    $qb = $this->em()->createQueryBuilder();

    $events = $this->em()
      ->getRepository('Entity\Event')
      ->createQueryBuilder('event')
      ->add('where', $qb->expr()->in('event.id', $ids))
      ->getQuery()
      ->getArrayResult();

    $eventsAssoc = [];
    foreach ($events as $event) {
      $eventsAssoc[$event['id']] = $event;
    }

    foreach ($flightEvents as $event) {
      $eventsArray[] = array_merge(
        $event->get(true),
        $eventsAssoc[$event->getId()]
      );
    }

    return $eventsArray;
  }

  public function getFormatedFlightEvents (
    $flightId,
    $flightGuid,
    $isDisabled,
    $startCopyTime,
    $stepLength
  ) {
    if (!is_string($flightGuid)) {
      throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
        . json_encode($flightGuid), 1);
    }

    $flightEventsOld = $this->getFlightEventsOldExtended(
      $flightId,
      $flightGuid,
      $startCopyTime,
      $stepLength,
      $isDisabled
    );

    $flightEvents = $this->getFlightEventsExtended(
      $flightGuid,
      $startCopyTime,
      $stepLength,
      $isDisabled
    );

    return array_merge($flightEventsOld, $flightEvents);
  }

  public function getFlightEventsOldExtended(
    $flightId,
    $flightGuid,
    $startCopyTime,
    $stepLength,
    $isDisabled,
    $refParamCode = null
  ) {
    $flight = $this->em()->find('Entity\Flight', $flightId);
    $fdr = $flight->getFdr();
    $this->setupFdrEventOldEntity($fdr->getCode());
    $this->setupFlightEventOldEntity($flightGuid);

    $oldEvents = [];
    $rp = $this->em('flights')
      ->getRepository('Entity\FlightEventOld');

    if ($refParamCode === null) {
      $oldEvents = $rp->findAll();
    } else {
      $oldEvents = $rp->findBy([
        'refParam' => $refParamCode
      ]);
    }

    $flightEvents = [];
    foreach ($oldEvents as $event) {
      $event = $event->get(true);
      $fdrEventOld = $this->em('fdrs')
        ->getRepository('Entity\FdrEventOld')
        ->findOneBy(['code' => $event['code']]);

      $flightEvents[] = array_merge(
        $event, [
          'start' => date("H:i:s", $event['startTime'] / 1000),
          'reliability' => (intval($event['falseAlarm']) === 0),
          'end' => date("H:i:s", $event['endTime'] / 1000),
          'duration' => $this->timestampToDuration($event['endTime']
            - $event['startTime']
          ),
          'eventType' => 1,
          'isDisabled' => $isDisabled
        ],
        $fdrEventOld->get(true)
      );
    }

    return $flightEvents;
  }

  public function getFlightEventsExtended(
    $flightGuid,
    $startCopyTime,
    $stepLength,
    $isDisabled,
    $refParamCode = null
  ) {
    $this->setupFlightEventEntity($flightGuid);
    $this->setupFlightSettlementEntity($flightGuid);

    $flightEvents = [];

    if ($refParamCode === null) {
      $flightEvents = $this->em('flights')
        ->getRepository('Entity\FlightEvent')
        ->findAll();
    } else {
      $event = $this->em()
        ->getRepository('Entity\Event')
        ->createQueryBuilder('event')
        ->where('event.refParam = ?1')
        ->setParameter(1, $refParamCode)
        ->getQuery()
        ->getResult();

      if (count($event)) {
        $flightEvents = $this->em('flights')
          ->getRepository('Entity\FlightEvent')
          ->findBy(['eventId' => $event[0]->getId()]);
      }
    }

    if (count($flightEvents) === 0) {
      return [];
    }

    $ids = [];
    foreach ($flightEvents as $event) {
      $ids[] = $event->getId();
    }

    $qb = $this->em()->createQueryBuilder();

    $events = $this->em()
      ->getRepository('Entity\Event')
      ->createQueryBuilder('event')
      ->add('where', $qb->expr()->in('event.id', $ids))
      ->getQuery()
      ->getArrayResult();

    $eventsAssoc = [];
    foreach ($events as $event) {
      $eventsAssoc[$event['id']] = $event;
    }

    $eventSettlements = $this->em()
      ->getRepository('Entity\EventSettlement')
      ->createQueryBuilder('eventSettlement')
      ->add('where', $qb->expr()->in('eventSettlement.eventId', $ids))
      ->getQuery()
      ->getArrayResult();

    $eventsSettlementsAssoc = [];
    foreach ($eventSettlements as $eventSettlement) {
      $eventsSettlementsAssoc[$eventSettlement['eventId']] = $eventSettlement;
    }

    $array = [];
    foreach ($flightEvents as $flightEvent) {
      $settlements = $flightEvent->getFlightSettlements();
      $flightEvent = $flightEvent->get(true);
      $event = $eventsAssoc[$flightEvent['id']] ?? null;

      if (!$event) {
        throw new Exception("Cant find Event for FlightEvent. FlightEventId:".$flightEvent['id'], 1);
      }

      $formatedSettlements = [];
      foreach ($settlements as $settlement) {
        $formatedSettlements[] = $eventsSettlementsAssoc[$flightEvent['id']]['text']
          .' = '.$settlement->getValue();
      }

      $array[] = [
        'id' => $flightEvent['id'],
        'refParam' => $event['refParam'],
        'frameNum' => (intval(substr($flightEvent['startTime'], 0, -3)) - $startCopyTime) * $stepLength,
        'endFrameNum' => (intval(substr($flightEvent['endTime'], 0, -3)) - $startCopyTime) * $stepLength,
        'start' => date('H:i:s', intval(substr($flightEvent['startTime'], 0, -3))),
        'end' => date('H:i:s', intval(substr($flightEvent['endTime'], 0, -3))),
        'startTime' => $flightEvent['startTime'],
        'endTime' => $flightEvent['endTime'],
        'duration' => gmdate('H:i:s',
             (($flightEvent['endTime']
               - $flightEvent['startTime'])
             / 1000)
           ),
        'code' => $event['code'],
        'comment' => '',
        'text' => $event['text'],
        'algText' => $event['algText'],
        'status' => $event['status'],
        'falseAlarm' => $flightEvent['falseAlarm'],
        'excAditionalInfo' => $formatedSettlements,
        'visualization' => $event['visualization'],
        'reliability' => (intval($flightEvent['falseAlarm']) === 0),
        'isDisabled' => $isDisabled,
        'userComment' => '',
        'eventType' => 2
      ];
    }

    return $array;
  }

  public function updateFalseAlarm(
    $flightGuid,
    $eventType,
    $eventId,
    $falseAlarm
  ) {
    if ($eventType === 1) {
      $this->setupFlightEventOldEntity($flightGuid);

      $flightEventOld = $this->em('flights')
        ->find('Entity\FlightEventOld', $eventId);

      $flightEventOld->setFalseAlarm($falseAlarm);
      $this->em('flights')->persist($flightEventOld);
      $this->em('flights')->flush();
    } else if ($eventType === 2) {
      $this->setupFlightEventEntity($flightGuid);

      $flightEvent = $this->em('flights')
        ->find('Entity\FlightEvent', $eventId);

      $flightEvent->setFalseAlarm($falseAlarm);
      $this->em('flights')->persist($flightEvent);
      $this->em('flights')->flush();
    }
  }

  public function timestampToDuration($microsecsCount)
  {
    if ($microsecsCount > 1000) {
      $timeInterval = $microsecsCount / 1000;

      $hours = floor($timeInterval / (60*60));
      $mins = floor(($timeInterval - $hours * 60*60) / 60);
      $secs = floor(($timeInterval - $hours * 60*60 - $mins * 60));

      if(strlen($hours) < 2) {
        $hours = "0".$hours;
      }
      if(strlen($mins) < 2) {
        $mins = "0".$mins;
      }
      if(strlen($secs) < 2) {
        $secs = "0".$secs;
      }
      $duration = $hours .":".$mins.":".$secs;
      return $duration;
    } else {
      return (float)($microsecsCount / 1000);
    }
  }
}
