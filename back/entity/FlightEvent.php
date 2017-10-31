<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

use \Exception;

use EntityTraits\dynamicTable;

/**
 * FlightEvent
 *
 * @Table(name="NULL")
 * @Entity(repositoryClass="Repository\FlightEventRepository")
 */
class FlightEvent
{
    use dynamicTable;
    private static $_prefix = '_events';
    /**
     * @var integer
     *
     * @Column(name="id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Column(name="start_time", type="bigint", nullable=false)
     */
    private $startTime;

    /**
     * @var string
     *
     * @Column(name="end_time", type="bigint", nullable=false)
     */
    private $endTime;
    /**
     * @var integer
     *
     * @Column(name="id_event", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var boolean
     *
     * @Column(name="false_alarm", type="boolean", nullable=false)
     */
    private $falseAlarm;

    /**
     * One FlightEvent has Many FlightSettlements.
     * @OneToMany(targetEntity="FlightSettlement", mappedBy="flightEvent")
     */
    private $flightSettlements;

    public function __construct()
    {
        $this->flightSettlements = new ArrayCollection();
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEventId()
    {
        return $this->eventId;
    }

    public function getFlightSettlements()
    {
        return $this->flightSettlements;
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'eventId' => $this->eventId,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'falseAlarm' => $this->falseAlarm
        ];
    }

    public static function getPrefix()
    {
        return self::$_prefix;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    public function setEventId($eventId)
    {
        if (!is_int($eventId)) {
            throw new Exception("Incorrect eventId passed. Int is required. Passed: "
                . json_encode($eventId), 1);
        }

        $this->eventId = $eventId;
    }

    public function setEvent($event)
    {
        if (!is_a($event, 'Entity\Event')) {
            throw new Exception("Incorrect event passed. Event obj is required. Passed: "
                . get_class($event) . '. ' .json_encode($event), 1);
        }

        $this->event = $event;
    }

    public function setFalseAlarm($falseAlarm)
    {
        $this->falseAlarm = $falseAlarm;
    }

    public function setAttributes($attributes)
    {
        if (!is_array($attributes)) {
            throw new Exception("Incorrect attributes passed. Array is required. Passed: "
                . json_encode($attributes), 1);
        }

        if (!isset($attributes['startTime'])
            || !isset($attributes['endTime'])
            || !isset($attributes['eventId'])
        ) {
            throw new Exception("Not all necessary attributes passed. "
                . "startTime, endTime, eventId are required. Passed: "
                . json_encode($attributes), 1);
        }

        $this->setStartTime($attributes['startTime']);
        $this->setEndTime($attributes['endTime']);
        $this->setEventId($attributes['eventId']);

        $falseAlarm = isset($attributes['falseAlarm']) ? $attributes['falseAlarm'] : false;
        $this->setFalseAlarm($falseAlarm);
    }

    public static function createTable($link, $guid)
    {
        if (!is_string($guid)) {
            throw new Exception("Incorrect guid passed. String is required. Passed: "
                . json_encode($guid), 1);
        }

        $dynamicTableName = $guid . self::$_prefix;
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
                . " ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
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

        return $dynamicTableName;
    }
}
