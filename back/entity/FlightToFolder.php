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
     * @Column(name="flightId", type="integer", nullable=true)
     */
    private $flightId;

    /**
     * @var integer
     *
     * @Column(name="folderId", type="integer", nullable=true)
     */
    private $folderId;

    /**
     * @var integer
     *
     * @Column(name="userId", type="integer", nullable=true)
     */
    private $userId;

    /**
     * Many FlightToFolder has One Flight.
     * @ManyToOne(targetEntity="Flight")
     * @JoinColumn(name="flightId", referencedColumnName="id")
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
}
