<?php

namespace Entity;

use EntityTraits\dynamicTable;

/**
 * FdrVoice
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrVoice
{
  use dynamicTable;
  private static $_prefix = '_voice';

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
   * @Column(name="code", type="string", length=20, nullable=false)
   */
  private $code;

  /**
   * @var string
   *
   * @Column(name="name", type="string", length=65535, nullable=false)
   */
  private $name;

  /**
   * @var integer
   *
   * @Column(name="frame_length", type="integer", nullable=false)
   */
  private $frameLength;

  /**
   * @var integer
   *
   * @Column(name="start_offset", type="integer", nullable=false)
   */
  private $startOffset;

  /**
   * @var integer
   *
   * @Column(name="offset", type="integer", nullable=false)
   */
  private $offset;

  /**
   * @var integer
   *
   * @Column(name="data_length", type="integer", nullable=false)
   */
  private $dataLength;

  /**
   * @var integer
   *
   * @Column(name="word_length", type="integer", nullable=false)
   */
  private $wordLength;

  /**
   * @var string
   *
   * @Column(name="byte_order", type="string", length=2, nullable=false)
   */
  private $byteOrder;

  /**
   * @var integer
   *
   * @Column(name="k", type="float", nullable=false)
   */
  private $k;

  public function getId()
  {
    return $this->id;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function get($isArray = false)
  {
    $arr = [
      'id' => $this->id,
      'type' => 'voice',
      'code' => $this->code,
      'name' => $this->name,
      'frameLength' => $this->frameLength,
      'startOffset' => $this->startOffset,
      'offset' => $this->offset,
      'dataLength' => $this->dataLength,
      'wordLength' => $this->wordLength,
      'byteOrder' => $this->byteOrder,
      'k' => $this->k,
    ];

    if ($isArray) {
      return $arr;
    }

    return (object) $arr;
  }

  public static function getTablePrefix()
  {
    return self::$_prefix;
  }
}
