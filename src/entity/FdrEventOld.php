<?php

namespace Entity;

use EntityTraits\dynamicTable;

/**
 * Event
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrEventOld
{
  use dynamicTable;
  public static $_prefix = '_ex';

  /**
   * @var string
   * @Id
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
   * @Column(name="refParam", type="string", length=255, nullable=false)
   */
  private $refParam;

  /**
   * @var integer
   *
   * @Column(name="minLength", type="integer", nullable=false)
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
   * @Column(name="algText", type="string", length=255, nullable=false)
   */
  private $algText;

  /**
   * @var string
   *
   * @Column(name="visualization", type="string", length=10, nullable=false)
   */
  private $visualization;

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

  public static function getPrefix()
  {
    return self::$_prefix;
  }

  public function get($isArray = false)
  {
    $arr = [
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

    if ($isArray) {
      return $arr;
    }

    return (object) $arr;
  }
}
