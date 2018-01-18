<?php

namespace Entity;

use \Exception;

use EntityTraits\dynamicTable;

/**
 * FlightSettlement
 *
 * @Table(name="NULL", indexes={@Index(name="id_event", columns={"id_event"})})
 * @Entity
 */
class FlightSettlement
{
  use dynamicTable;
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
   * @var float
   *
   * @Column(name="value", type="string", nullable=false)
   */
  private $value;

  /**
   * Many FlightSettlements have One FlightEvent.
   * @ManyToOne(targetEntity="FlightEvent", inversedBy="flightSettlements")
   * @JoinColumn(name="id_flight_event", referencedColumnName="id")
   */
  private $flightEvent;

  public function getValue()
  {
    return $this->value;
  }

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

  public function setFlightEvent($flightEvent)
  {
    if (!is_a($flightEvent, 'Entity\FlightEvent')) {
      throw new Exception("Incorrect settlement passed. FightEvent obj is required. Passed: "
        . get_class($flightEvent) . '. ' . json_encode($flightEvent), 1);
    }

    $this->flightEvent = $flightEvent;
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
      || !isset($attributes['flightEvent'])
      || !isset($attributes['value'])
    ) {
      throw new Exception("Not all necessary attributes passed. "
        . "eventId, settlementId, flightEventId, value are required. Passed: "
        . json_encode($attributes), 1);
    }

    $this->setEventId($attributes['eventId']);
    $this->setSettlementId($attributes['settlementId']);
    $this->setFlightEventId($attributes['flightEventId']);
    $this->setFlightEvent($attributes['flightEvent']);
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
}
