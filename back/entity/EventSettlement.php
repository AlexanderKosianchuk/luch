<?php

namespace Entity;

/**
 * EventSettlement
 *
 * @Table(name="event_settlements", indexes={@Index(name="id_event", columns={"id_event"})})
 * @Entity
 */
class EventSettlement
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
     * @var integer
     *
     * @Column(name="id_event", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var string
     *
     * @Column(name="text", type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @var string
     *
     * @Column(name="alg", type="text", length=65535, nullable=false)
     */
    private $alg;

    /**
    * Many EventSettlements have One Event.
    * @ManyToOne(targetEntity="Event", inversedBy="eventSettlements")
    * @JoinColumn(name="id_event", referencedColumnName="id")
    */
    private $event;

    /**
     * One EventSettlement has Many FlightSettlements.
     * @OneToMany(targetEntity="FlightSettlement", mappedBy="eventSettlement")
     */
    private $flightSettlements;

    public function __construct()
    {
        $this->flightSettlements = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getAlg()
    {
        return $this->alg;
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'eventId' => $this->eventId,
            'text' => $this->text,
            'alg' => $this->alg
        ];
    }
}
