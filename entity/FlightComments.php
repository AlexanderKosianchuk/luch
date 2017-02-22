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
     * @Column(name="commander-admitted", type="boolean", nullable=false)
     */
    private $commanderAdmitted;

    /**
     * @var boolean
     *
     * @Column(name="aircraft-allowed", type="boolean", nullable=false)
     */
    private $aircraftAllowed;

    /**
     * @var boolean
     *
     * @Column(name="general-admission", type="boolean", nullable=false)
     */
    private $generalAdmission;

    /**
     * @var integer
     *
     * @Column(name="id_flight", type="integer", nullable=false)
     */
    private $idFlight;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

    /**
     * @var \DateTime
     *
     * @Column(name="dt", type="datetime", nullable=false)
     */
    private $dt;


}
