<?php

namespace Entity;

/**
 * FlightSettlement
 *
 * @Table(name="NULL", indexes={@Index(name="id_event", columns={"id_event"})})
 * @Entity
 */
class FlightSettlement
{
    private static $_prefix = '_settlements';
    /**
     * @var integer
     *
     * @Column(name="id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @Column(name="id_event", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var integer
     *
     * @Column(name="id_settlement", type="integer", nullable=false)
     */
    private $settlementId;

    /**
     * @var integer
     *
     * @Column(name="id_flight_event", type="integer", nullable=false)
     */
    private $flightEventId;

    /**
     * @var string
     *
     * @Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    public function setEventId($eventId)
    {
        if (!is_int($eventId)) {
            throw new Exception("Incorrect eventId passed. Int is required. Passed: "
                . json_encode($settlementId), 1);
        }

        $this->eventId = $eventId;
    }

    public function setSettlementId($settlementId)
    {
        if (!is_int($settlementId)) {
            throw new Exception("Incorrect settlementId passed. Int is required. Passed: "
                . json_encode($settlementId), 1);
        }

        $this->settlementId = $settlementId;
    }

    public function setFlightEventId($flightEventId)
    {
        if (!is_int($flightEventId)) {
            throw new Exception("Incorrect flightEventId passed. Int is required. Passed: "
                . json_encode($flightEventId), 1);
        }

        $this->flightEventId = $flightEventId;
    }

    public function setValue($value)
    {
        if (!is_string($value)) {
            throw new Exception("Incorrect value passed. String is required. Passed: "
                . json_encode($value), 1);
        }

        $this->value = $value;
    }

    public function setAttributes($attributes)
    {
        if (!is_array($attributes)) {
            throw new Exception("Incorrect attributes passed. Array is required. Passed: "
                . json_encode($attributes), 1);
        }

        if (!isset($attributes['eventId'])
            || !isset($attributes['settlementId'])
            || !isset($attributes['flightEventId'])
            || !isset($attributes['value'])
        ) {
            throw new Exception("Not all necessary attributes passed. "
                . "eventId, settlementId, flightEventId, value are required. Passed: "
                . json_encode($attributes), 1);
        }

        $this->setEventId($attributes['eventId']);
        $this->setSettlementId($attributes['settlementId']);
        $this->setFlightEventId($attributes['flightEventId']);
        $this->setValue($attributes['value']);
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'eventId' => $this->eventId,
            'settlementId' => $this->settlementId,
            'flightEventId' => $this->flightEventId,
            'value' => $this->value
        ];
    }

    public static function getPrefix ()
    {
        return self::$_prefix;
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
        if (!$result->fetch_array())
        {
            $query = "CREATE TABLE `".$dynamicTableName."` ("
                . "`id` BIGINT NOT NULL AUTO_INCREMENT, "
                . "`id_event` BIGINT(20) NOT NULL, "
                . "`id_settlement` BIGINT(20) NOT NULL, "
                . "`id_flight_event` BIGINT(20) NOT NULL, "
                . "`value` VARCHAR(255) NOT NULL, "
                . " INDEX (`id_event`), "
                . " INDEX (`id_settlement`), "
                . " INDEX (`id_flight_event`), "
                . " PRIMARY KEY (`id`)) "
                . " ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
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

        return $dynamicTableName;
    }
}
