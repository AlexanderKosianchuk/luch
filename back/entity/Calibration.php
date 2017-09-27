<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Calibration
 *
 * @Table(name="calibrations", indexes={@Index(name="id_fdr", columns={"id_fdr"}), @Index(name="id_user", columns={"id_user"})})
 * @Entity(repositoryClass="Repository\CalibrationRepository")
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

    public function getFdr()
    {
        return $this->fdr;
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fdrId' => $this->fdrId,
            'fdrName' => $this->fdr->getName(),
            'userId' => $this->userId,
            'dtCreated' => $this->dtCreated->format('y/m/d H:i:s'),
            'dtUpdated' => $this->dtUpdated->format('y/m/d H:i:s')
        ];
    }


}
