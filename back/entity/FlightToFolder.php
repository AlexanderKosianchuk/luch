<?php

namespace Entity;

/**
 * FlightToFolder
 *
 * @Table(name="flight_to_folder")
 * @Entity(repositoryClass="Repository\FlightToFolderRepository")
 */
class FlightToFolder
{
  /**
   * @var integer
   *
   * @Column(name="id", type="bigint", nullable=false)
   * @Id
   * @GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   * @var integer
   *
   * @Column(name="id_flight", type="integer", nullable=false)
   */
  private $flightId;

  /**
   * @var integer
   *
   * @Column(name="id_folder", type="integer", nullable=false)
   */
  private $folderId;

  /**
   * @var integer
   *
   * @Column(name="id_user", type="integer", nullable=false)
   */
  private $userId;

  /**
   * Many FlightToFolder has One Flight.
   * @ManyToOne(targetEntity="Flight")
   * @JoinColumn(name="id_flight", referencedColumnName="id")
   */
  private $flight;

  public function getFlight()
  {
    return $this->flight;
  }

  public function getFlightId()
  {
    return $this->flightId;
  }

  public function getFolderId()
  {
    return $this->folderId;
  }

  public function setFlightId($flightId)
  {
    return $this->flightId = $flightId;
  }

  public function setFolderId($folderId)
  {
    return $this->folderId = $folderId;
  }

  public function setUserId($userId)
  {
    return $this->userId = $userId;
  }

  public function setFlight($flight)
  {
    return $this->flight = $flight;
  }
}
