<?php

namespace Entity;

/**
 * FdrToUser
 *
 * @Table(name="fdr_to_user")
 * @Entity
 */
class FdrToUser
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
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @Column(name="id_fdr", type="integer", nullable=false)
     */
    private $fdrId;

    /**
     * @var \DateTime
     *
     * @Column(name="dt", type="datetime", nullable=false)
     */
    private $dt;

    /**
     * Many FdrToUser have One Fdr.
     * @ManyToOne(targetEntity="Fdr", inversedBy="fdrToUser")
     * @JoinColumn(name="id_fdr", referencedColumnName="id")
     */
    private $fdr;
}
