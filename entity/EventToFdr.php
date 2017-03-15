<?php

namespace Entity;

/**
 * EventToFdr
 *
 * @Table(name="event_to_fdr", indexes={@Index(name="id_event", columns={"id_event"}), @Index(name="id_fdr", columns={"id_fdr"}), @Index(name="id_user", columns={"id_user"})})
 * @Entity
 */
class EventToFdr
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
    private $idEvent;

    /**
     * @var integer
     *
     * @Column(name="id_fdr", type="integer", nullable=false)
     */
    private $idFdr;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

    /**
     * @var string
     *
     * @Column(name="param_substitution", type="text", length=65535, nullable=true)
     */
    private $paramSubstitution;

    /**
     * Many EventToFdrs have One Event.
     * @ManyToOne(targetEntity="Event", inversedBy="eventToFdr")
     * @JoinColumn(name="id_event", referencedColumnName="id")
     */
    private $event;

    /**
     * Many EventToFdrs have One Fdr.
     * @ManyToOne(targetEntity="Fdr", inversedBy="eventToFdr")
     * @JoinColumn(name="id_fdr", referencedColumnName="id")
     */
    private $fdr;

    public function getSubstitution()
    {
        return $this->paramSubstitution;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getFdr()
    {
        return $this->fdr;
    }
}
