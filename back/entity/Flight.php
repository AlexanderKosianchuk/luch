<?php

namespace Entity;

/**
 * Flights
 *
 * @Table(name="flights")
 * @Entity
 */
class Flight
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
    private $startCopyTime;

    /**
     * @var integer
     *
     * @Column(name="uploadingCopyTime", type="bigint", nullable=true)
     */
    private $uploadingCopyTime;

    /**
     * @var string
     *
     * @Column(name="performer", type="string", length=255, nullable=true)
     */
    private $performer;

    /**
     * @var string
     *
     * @Column(name="departureAirport", type="string", length=255, nullable=true)
     */
    private $departureAirport;

    /**
     * @var string
     *
     * @Column(name="arrivalAirport", type="string", length=255, nullable=true)
     */
    private $arrivalAirport;

    /**
     * @var string
     *
     * @Column(name="flightAditionalInfo", type="text", length=65535, nullable=true)
     */
    private $flightAditionalInfo;

    /**
     * @var string
     *
     * @Column(name="fileName", type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @Column(name="guid", type="string", length=20, nullable=true)
     */
    private $guid;

    /**
     * @var string
     *
     * @Column(name="id_fdr", type="integer", nullable=false)
     */
    private $id_fdr;

    /**
     * One Flight has One Fdr.
     * @OneToOne(targetEntity="Fdr")
     * @JoinColumn(name="id_fdr", referencedColumnName="id")
     */
    private $fdr;

    /**
     * One Flight has One User.
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $user;

    public function getFdr()
    {
        return $this->fdr;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function getStartCopyTime()
    {
        return $this->startCopyTime;
    }

    public function get()
    {
        $flightInfo = [
           'id' => $this->id,
           'bort' => $this->bort,
           'voyage' => $this->voyage,
           'startCopyTime' => $this->startCopyTime,
           'uploadingCopyTime' => $this->uploadingCopyTime,
           'performer' => $this->performer,
           'departureAirport' => $this->departureAirport,
           'arrivalAirport' => $this->arrivalAirport,
           'filename' => $this->filename,
           'guid' => $this->guid
       ];

        $flightInfo = array_merge($flightInfo,
            json_decode($this->flightAditionalInfo, true)
        );

        return $flightInfo;
    }
}
