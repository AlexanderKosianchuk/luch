<?php



namespace Entity;

/**
 * Flights
 *
 * @Table(name="flights")
 * @Entity
 */
class Flights
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
     * @var string
     *
     * @Column(name="bort", type="string", length=255, nullable=true)
     */
    private $bort;

    /**
     * @var string
     *
     * @Column(name="voyage", type="string", length=255, nullable=true)
     */
    private $voyage;

    /**
     * @var integer
     *
     * @Column(name="startCopyTime", type="bigint", nullable=true)
     */
    private $startcopytime;

    /**
     * @var integer
     *
     * @Column(name="uploadingCopyTime", type="bigint", nullable=true)
     */
    private $uploadingcopytime;

    /**
     * @var string
     *
     * @Column(name="performer", type="string", length=255, nullable=true)
     */
    private $performer;

    /**
     * @var string
     *
     * @Column(name="bruType", type="string", length=255, nullable=true)
     */
    private $brutype;

    /**
     * @var string
     *
     * @Column(name="departureAirport", type="string", length=255, nullable=true)
     */
    private $departureairport;

    /**
     * @var string
     *
     * @Column(name="arrivalAirport", type="string", length=255, nullable=true)
     */
    private $arrivalairport;

    /**
     * @var string
     *
     * @Column(name="flightAditionalInfo", type="text", length=65535, nullable=true)
     */
    private $flightaditionalinfo;

    /**
     * @var string
     *
     * @Column(name="fileName", type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @Column(name="apTableName", type="string", length=20, nullable=true)
     */
    private $aptablename;

    /**
     * @var string
     *
     * @Column(name="bpTableName", type="string", length=20, nullable=true)
     */
    private $bptablename;

    /**
     * @var string
     *
     * @Column(name="exTableName", type="string", length=20, nullable=true)
     */
    private $extablename;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;


}
