<?php

namespace Entity;

/**
 * FlightSettlement
 *
 * @Table(name="", indexes={@Index(name="id_event", columns={"id_event"})})
 * @Entity
 */
class FlightSettlement
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
     * @Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
    * Many FlightSettlement have One Event.
    * @ManyToOne(targetEntity="Event", inversedBy="аlightSettlements")
    * @JoinColumn(name="id_event", referencedColumnName="id")
    */
    private $event;

    public function setAttributes($attributes)
    {
        return $this->alg;
    }

}
