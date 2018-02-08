<?php

namespace Entity;

/**
 * Event
 *
 * @Table(name="events_realtime")
 * @Entity
 */
class EventsRealtime
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
   * @Column(name="name", type="string", length=255, nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * @Column(name="color", type="string", length=6, nullable=false)
   */
  private $color;

  /**
   * @var string
   *
   * @Column(name="text", type="string", length=255, nullable=false)
   */
  private $text;

  /**
   * @var string
   *
   * @Column(name="stresshold", type="string", length=255, nullable=false)
   */
  private $stresshold;

  /**
   * @var string
   *
   * @Column(name="func", type="text", length=65535, nullable=false)
   */
  private $func;

  /**
   * @var string
   *
   * @Column(name="alg", type="text", length=65535, nullable=false)
   */
  private $alg;

  /**
   * @var integer
   *
   * @Column(name="id_fdr", type="integer", nullable=false)
   */
  private $fdrId;

  /**
   * Many RealtimeEvents have One Fdr.
   * @ManyToOne(targetEntity="Fdr", inversedBy="realtimeEventsToFdr")
   * @JoinColumn(name="id_fdr", referencedColumnName="id")
   */
  private $fdr;


  public function getFdr()
  {
    return $this->fdr;
  }

  public function getId()
  {
    return $this->id;
  }

  public function get()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'color' => $this->color,
      'text' => $this->text,
      'stresshold' => $this->stresshold,
      'func' => $this->func,
      'alg' => $this->alg,
      'fdrId' => $this->fdrId
    ];
  }
}
