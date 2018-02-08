<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Fdr
 *
 * @Table(name="fdrs")
 * @Entity
 */
class Fdr
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
   * @Column(name="code", type="string", length=255, nullable=false)
   */
  private $code;

  /**
   * @var float
   *
   * @Column(name="step_length", type="float", precision=10, scale=0, nullable=false)
   */
  private $stepLength;

  /**
   * @var integer
   *
   * @Column(name="step_divider", type="integer", nullable=false)
   */
  private $stepDivider;

  /**
   * @var integer
   *
   * @Column(name="frame_length", type="integer", nullable=false)
   */
  private $frameLength;

  /**
   * @var integer
   *
   * @Column(name="word_length", type="integer", nullable=false)
   */
  private $wordLength;

  /**
   * @var string
   *
   * @Column(name="aditional_info", type="text", length=65535, nullable=false)
   */
  private $aditionalInfo;

  /**
   * @var integer
   *
   * @Column(name="header_length", type="integer", nullable=false)
   */
  private $headerLength;

  /**
   * @var string
   *
   * @Column(name="header_scr", type="text", length=65535, nullable=false)
   */
  private $headerScr;

  /**
   * @var string
   *
   * @Column(name="frame_syncro_code", type="string", length=8, nullable=false)
   */
  private $frameSyncroCode;

  /**
   * @var string
   *
   * @Column(name="preview_params", type="string", length=255, nullable=false)
   */
  private $previewParams;

  /**
   * @var string
   *
   * @Column(name="id_user", type="integer",  nullable=false)
   */
  private $userId;

  /**
   * @var string
   *
   * @Column(name="kml_export_script", type="text", length=65535, nullable=false)
   */
  private $kmlExportScript;

  /**
   * One Fdr has Many EventToFdrs.
   * @OneToMany(targetEntity="EventToFdr", mappedBy="fdr")
   */
  private $eventsToFdr;

  /**
   * One Fdr has Many RealtimeEvents.
   * @OneToMany(targetEntity="EventsRealtime", mappedBy="fdr")
   */
  private $realtimeEventsToFdr;

  /**
   * One Fdr has Many FdrToUser.
   * @OneToMany(targetEntity="FdrToUser", mappedBy="fdr")
   */
  private $fdrToUser;

  public function __construct()
  {
    $this->eventsToFdr = new ArrayCollection();
    $this->realtimeEventsToFdr = new ArrayCollection();
    $this->fdrToUser = new ArrayCollection();
  }

  public function getRealtimeEvents()
  {
    return $this->realtimeEventsToFdr;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getStepLength()
  {
    return $this->stepLength;
  }

  public function getStepDivider()
  {
    return $this->stepDivider;
  }

  public function getFrameLength()
  {
    return $this->frameLength;
  }

  public function getWordLength()
  {
    return $this->wordLength;
  }

  public function getEventsToFdr()
  {
    return $this->eventsToFdr;
  }

  public function getPreviewParams()
  {
    return $this->previewParams;
  }

  public function getHeaderScr()
  {
    return $this->headerScr;
  }

  public function getHeaderLength()
  {
    return $this->headerLength;
  }

  public function getFrameSyncroCode()
  {
    return $this->frameSyncroCode;
  }

  public function getAditionalInfo()
  {
    return $this->aditionalInfo;
  }

  public function get($isArray = false)
  {
    $arr = [
      'id' => $this->id,
      'name' => $this->name,
      'code' => $this->code,
      'stepLength' => $this->stepLength,
      'stepDivider' => $this->stepDivider,
      'frameLength' => $this->frameLength,
      'wordLength' => $this->wordLength,
      'aditionalInfo' => $this->aditionalInfo,
      'headerLength' => $this->headerLength,
      'headerScr' => $this->headerScr,
      'frameSyncroCode' => $this->frameSyncroCode,
      'previewParams' => $this->previewParams,
      'kmlExportScript' => $this->kmlExportScript,
      'userId' => $this->userId
    ];

    if ($isArray) {
      return $arr;
    }

    return (object) $arr;
  }
}
