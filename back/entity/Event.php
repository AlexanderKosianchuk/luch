<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Event
 *
 * @Table(name="events")
 * @Entity
 */
class Event
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
     * @Column(name="code", type="string", length=255, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @Column(name="status", type="string", length=3, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @Column(name="text", type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @var string
     *
     * @Column(name="ref_param", type="string", length=255, nullable=false)
     */
    private $refParam;

    /**
     * @var integer
     *
     * @Column(name="min_length", type="integer", nullable=false)
     */
    private $minLength;

    /**
     * @var string
     *
     * @Column(name="alg", type="text", length=65535, nullable=false)
     */
    private $alg;

    /**
     * @var string
     *
     * @Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @Column(name="alg_text", type="string", length=255, nullable=false)
     */
    private $algText;

    /**
     * @var string
     *
     * @Column(name="visualization", type="string", length=10, nullable=false)
     */
    private $visualization;

    /**
     * One Event has Many EventToFdrs.
     * @OneToMany(targetEntity="EventToFdr", mappedBy="event")
     */
    private $eventToFdrs;

    /**
     * One Event has Many FlightEvents.
     * @OneToMany(targetEntity="FlightEvent", mappedBy="event")
     */
    private $flightEvents;

    /**
     * One Event has Many EventSettlements.
     * @OneToMany(targetEntity="EventSettlement", mappedBy="event")
     */
    private $eventSettlements;

    public function __construct()
    {
        $this->eventToFdrs = new ArrayCollection();
        $this->flightEvents = new ArrayCollection();
        $this->eventSettlements = new ArrayCollection();
    }

    public function getEventToFdrs()
    {
        return $this->eventToFdrs;
    }

    public function getFlightEvents()
    {
        return $this->flightEvents;
    }

    public function getEventSettlements()
    {
        return $this->eventSettlements;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAlg()
    {
        return $this->alg;
    }

    public function getAlgText()
    {
        return $this->algText;
    }

    public function getMinLength()
    {
        return $this->minLength;
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'text' => $this->text,
            'refParam' => $this->refParam,
            'minLength' => $this->minLength,
            'alg' => $this->alg,
            'comment' => $this->comment,
            'algText' => $this->algText,
            'visualization' => $this->visualization
        ];
    }
}
