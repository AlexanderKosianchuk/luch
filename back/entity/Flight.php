<?php

namespace Entity;

use Exception;

/**
 * Flights
 *
 * @Table(name="flights")
 * @Entity(repositoryClass="Repository\FlightRepository")
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
     * @Column(name="guid", type="string", length=20, nullable=false)
     */
    private $guid;

    /**
     * @var string
     *
     * @Column(name="bort", type="string", length=255, nullable=false)
     */
    private $bort;

    /**
     * @var string
     *
     * @Column(name="voyage", type="string", length=255, nullable=false)
     */
    private $voyage;

    /**
     * @var string
     *
     * @Column(name="captain", type="string", length=255, nullable=false)
     */
    private $captain;

    /**
     * @var integer
     *
     * @Column(name="start_copy_time", type="bigint", nullable=false)
     */
    private $startCopyTime;

    /**
     * @var string
     *
     * @Column(name="performer", type="string", length=255, nullable=false)
     */
    private $performer;

    /**
     * @var string
     *
     * @Column(name="departure_airport", type="string", length=255, nullable=false)
     */
    private $departureAirport;

    /**
     * @var string
     *
     * @Column(name="arrival_airport", type="string", length=255, nullable=false)
     */
    private $arrivalAirport;

    /**
     * @var string
     *
     * @Column(name="aditional_info", type="text", length=65535, nullable=true)
     */
    private $aditionalInfo;

    /**
     * @var string
     *
     * @Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var integer
     *
     * @Column(name="id_fdr", type="integer", nullable=false)
     */
    private $fdrId;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @Column(name="id_calibration", type="integer", nullable=true)
     */
    private $calibrationId;

    /**
     * @var integer
     *
     * @Column(name="dt", type="datetime", nullable=false)
     */
    private $dt;

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

    /**
     * One Flight has One Calibration.
     * @OneToOne(targetEntity="Calibration")
     * @JoinColumn(name="id_calibration", referencedColumnName="id")
     */
    private $calibration;

    public function getFdr()
    {
        return $this->fdr;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUserId()
    {
        return $this->id_user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFdrId()
    {
        return $this->getFdr()->getId();
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function getStartCopyTime()
    {
        return $this->startCopyTime;
    }

    public function getBort()
    {
        return $this->bort;
    }

    public function getVoyage()
    {
        return $this->voyage;
    }

    public function getCaptain()
    {
        return $this->captain;
    }

    public function getDepartureAirport()
    {
        return $this->departureAirport;
    }

    public function getArrivalAirport()
    {
        return $this->arrivalAirport;
    }

    public function get($isArray = false)
    {
        $flightInfo = [
           'id' => $this->id,
           'guid' => $this->guid,
           'bort' => $this->bort,
           'voyage' => $this->voyage,
           'captain' => $this->captain,
           'startCopyTime' => $this->startCopyTime,
           'performer' => $this->performer,
           'departureAirport' => $this->departureAirport,
           'arrivalAirport' => $this->arrivalAirport,
           'path' => $this->path,
           'aditionalInfo' => [],
           'dt' => $this->dt,
        ];

        if (is_array(json_decode($this->aditionalInfo, true))) {
            $aditionalInfo = json_decode($this->aditionalInfo, true);
            $flightInfo = array_merge($flightInfo, $aditionalInfo);
            $flightInfo['aditionalInfo'] = $aditionalInfo;
        }

        if ($isArray) {
            return $flightInfo;
        }

        return (object) $flightInfo;
    }


    public function set($guid, $data, $fdr, $user, $calibration = null)
    {
        if (!$guid) {
            throw new Exception("Cant insert flight without guid", 1);
        }

        if (!$fdr) {
            throw new Exception("Cant insert flight without fdr", 1);
        }

        $this->guid = $guid;
        $this->bort = $data['bort'] ?? 'x';
        $this->voyage = $data['voyage'] ?? 'x';
        $this->captain = $data['captain'] ?? 'x';
        $this->startCopyTime = $data['startCopyTime'] ?? 0;
        $this->performer = $data['performer'] ?? 'x';
        $this->departureAirport = $data['departureAirport'] ?? 'x';
        $this->arrivalAirport = $data['arrivalAirport'] ?? 'x';
        $this->aditionalInfo = $data['aditionalInfo'] ?? '';
        $this->path = $data['path'] ?? '';

        $this->fdr = $fdr;
        $this->user = $user;
        $this->calibration = $calibration;
    }
}
