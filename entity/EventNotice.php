<?php



namespace Entity;

/**
 * EventNotice
 *
 * @Table(name="event_notice", indexes={@Index(name="id_event", columns={"id_event"})})
 * @Entity
 */
class EventNotice
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
     * @var string
     *
     * @Column(name="text", type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @var string
     *
     * @Column(name="header_text", type="string", length=255, nullable=false)
     */
    private $headerText;

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


}
