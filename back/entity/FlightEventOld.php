<?php

namespace Entity;

use \Exception;

use EntityTraits\dynamicTable;

/**
 * FlightEventOld
 *
 * @Table(name="NULL")
 * @Entity
 */
class FlightEventOld
{
  use dynamicTable;
  public static $_prefix = '_ex';
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
   * @Column(name="frameNum", type="integer", nullable=false)
   */
  private $frameNum;

  /**
   * @var integer
   *
   * @Column(name="startTime", type="bigint", nullable=false)
   */
  private $startTime;

  /**
   * @var integer
   *
   * @Column(name="endFrameNum", type="integer", nullable=false)
   */
  private $endFrameNum;

  /**
   * @var integer
   *
   * @Column(name="endTime", type="bigint", nullable=false)
   */
  private $endTime;

  /**
   * @var string
   *
   * @Column(name="refParam", type="string", length=255, nullable=false)
   */
  private $refParam;

  /**
   * @var string
   *
   * @Column(name="code", type="string", length=255, nullable=false)
   */
  private $code;

  /**
   * @var string
   *
   * @Column(name="excAditionalInfo", type="text", length=65255, nullable=false)
   */
  private $excAditionalInfo;

  /**
   * @var boolean
   *
   * @Column(name="falseAlarm", type="boolean", nullable=false)
   */
  private $falseAlarm;

  /**
   * @var string
   *
   * @Column(name="userComment", type="text", length=65255, nullable=false)
   */
  private $userComment;

  public static function getPrefix()
  {
    return self::$_prefix;
  }

  public function setFalseAlarm($falseAlarm)
  {
    $this->falseAlarm = $falseAlarm;
  }

  public function get($isArray = false)
  {
    $flightEvent = [
      'id' => $this->id,
      'frameNum' => $this->frameNum,
      'startTime' => $this->startTime,
      'endFrameNum' => $this->endFrameNum,
      'endTime' => $this->endTime,
      'refParam' => $this->refParam,
      'code' => $this->code,
      'excAditionalInfo' => $this->excAditionalInfo,
      'falseAlarm' => $this->falseAlarm,
      'userComment' => $this->userComment
    ];

    if ($isArray) {
      return $flightEvent;
    }

    return (object)$flightEvent;
  }
}
