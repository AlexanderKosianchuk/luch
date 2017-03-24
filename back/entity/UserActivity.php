<?php



namespace Entity;

/**
 * UserActivity
 *
 * @Table(name="user_activity")
 * @Entity
 */
class UserActivity
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
     * @Column(name="action", type="string", length=255, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var integer
     *
     * @Column(name="userId", type="integer", nullable=false)
     */
    private $userid;

    /**
     * @var integer
     *
     * @Column(name="senderId", type="integer", nullable=false)
     */
    private $senderid;

    /**
     * @var string
     *
     * @Column(name="senderName", type="text", length=65535, nullable=false)
     */
    private $sendername;

    /**
     * @var integer
     *
     * @Column(name="targetId", type="integer", nullable=false)
     */
    private $targetid;

    /**
     * @var string
     *
     * @Column(name="targetName", type="text", length=65535, nullable=false)
     */
    private $targetname;


}
