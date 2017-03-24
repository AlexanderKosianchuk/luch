<?php

namespace Entity;

/**
 * FlightToFolder
 *
 * @Table(name="flight_to_folder")
 * @Entity
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
    private $flightid;

    /**
     * @var integer
     *
     * @Column(name="folderId", type="integer", nullable=true)
     */
    private $folderid;

    /**
     * @var integer
     *
     * @Column(name="userId", type="integer", nullable=true)
     */
    private $userid;


}
