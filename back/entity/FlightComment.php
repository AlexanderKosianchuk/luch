<?php

namespace Entity;

/**
 * FlightComments
 *
 * @Table(name="flight_comments", indexes={@Index(name="id_flight", columns={"id_flight"}), @Index(name="id_user", columns={"id_user"})})
 * @Entity
 */
class FlightComments
{
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
   * @Column(name="comment", type="text", length=65535, nullable=false)
   */
  private $comment;

  /**
   * @var boolean
   *
   * @Column(name="commander_admitted", type="boolean", nullable=false)
   */
  private $commanderAdmitted;

  /**
   * @var boolean
   *
   * @Column(name="aircraft_allowed", type="boolean", nullable=false)
   */
  private $aircraftAllowed;

  /**
   * @var boolean
   *
   * @Column(name="general_admission", type="boolean", nullable=false)
   */
  private $generalAdmission;

  /**
   * @var integer
   *
   * @Column(name="id_flight", type="integer", nullable=false)
   */
  private $flightId;

  /**
   * @var integer
   *
   * @Column(name="id_user", type="integer", nullable=false)
   */
  private $userId;

  /**
   * @var \DateTime
   *
   * @Column(name="dt", type="datetime", nullable=false)
   */
  private $dt;

  public function getComment()
  {
    return $this->comment;
  }

  public function getCommanderAdmitted()
  {
    return $this->commanderAdmitted;
  }

  public function getAircraftAllowed()
  {
    return $this->aircraftAllowed;
  }

  public function getGeneralAdmission()
  {
    return $this->generalAdmission;
  }

  public function getFlightId()
  {
    return $this->flightId;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  public function setFlightId($flightId)
  {
    $this->flightId = $flightId;
  }

  public function setComment($comment)
  {
    $this->comment = $comment;
  }

  public function setCommanderAdmitted($commanderAdmitted)
  {
    $this->commanderAdmitted = boolval($commanderAdmitted);
  }

  public function setAircraftAllowed($aircraftAllowed)
  {
    $this->aircraftAllowed = boolval($aircraftAllowed);
  }

  public function setGeneralAdmission($generalAdmission)
  {
    $this->generalAdmission = boolval($generalAdmission);
  }

  public function setUserId($userId)
  {
    $this->userId = $userId;
  }
}
