<?php



namespace Entity;

/**
 * Event
 *
 * @Table(name="events")
 * @Entity
 */
class Event
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
     * @Column(name="ref_param", type="string", length=255, nullable=false)
     */
    private $refParam;

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

    /**
     * @var string
     *
     * @Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @Column(name="alg_text", type="string", length=255, nullable=false)
     */
    private $algText;

    /**
     * @var string
     *
     * @Column(name="visualization", type="string", length=10, nullable=false)
     */
    private $visualization;


}
