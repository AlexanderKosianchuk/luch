<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Calibration
 *
 * @Table(name="calibrations", indexes={@Index(name="id_fdr", columns={"id_fdr"}), @Index(name="id_user", columns={"id_user"})})
 * @Entity
 */
class Calibration
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
   * @var integer
   *
   * @Column(name="id_fdr", type="integer", nullable=false)
   */
  private $fdrId;

  /**
   * @var integer
   *
   * @Column(name="id_user", type="integer", nullable=false)
   */
  private $userId;

  /**
   * @var \DateTime
   *
   * @Column(name="dt_created", type="datetime", nullable=false)
   */
  private $dtCreated;

  /**
   * @var \DateTime
   *
   * @Column(name="dt_updated", type="datetime", nullable=false)
   */
  private $dtUpdated;

  /**
   * One Calibration has One Fdr.
   * @OneToOne(targetEntity="Fdr")
   * @JoinColumn(name="id_fdr", referencedColumnName="id")
   */
  private $fdr;

  public function getId()
  {
    return $this->id;
  }

  public function getFdr()
  {
    return $this->fdr;
  }

  public function getFdrId()
  {
    return $this->fdr->getId();
  }

  public function get($isArray = false)
  {
    $arr = [
      'id' => $this->id,
      'name' => $this->name,
      'fdrId' => $this->fdrId,
      'fdrName' => $this->fdr->getName(),
      'userId' => $this->userId,
      'dtCreated' => $this->dtCreated->format('y/m/d H:i:s'),
      'dtUpdated' => $this->dtUpdated->format('y/m/d H:i:s')
    ];

    if ($isArray) {
      return $arr;
    }

    return (object)$arr;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setDtUpdated($dtUpdated)
  {
    $this->dtUpdated = $dtUpdated;
  }

  public function setFdr($fdr)
  {
    return $this->fdr = $fdr;
  }

  public function set($data)
  {
    $this->name = $data['name'];
    $this->userId = $data['userId'];
    $this->fdr = $data['fdr'];
    $this->dtCreated = $data['dtCreated'] ?? new \DateTime();
    $this->dtUpdated = $data['dtUpdated'] ?? new \DateTime();
  }

  /**
   * @PrePersist
   */
  public function onPrePersist()
  {
    $this->dtUpdated = date('Y-m-d H:m:s');
    $this->dtCreated = date('Y-m-d H:m:s');
  }
}
