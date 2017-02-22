<?php



namespace Entity;

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
    private $idFdr;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

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


}
