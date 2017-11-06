<?php

namespace Entity;

/**
 * UserSettings
 *
 * @Table(name="user_settings")
 * @Entity
 */
class UserSetting
{
    /**
     * @var integer
     *
     * @Column(name="id", type="bigint", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Column(name="value", type="string", length=200, nullable=true)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @Column(name="dt_cr", type="datetime", nullable=true)
     */
    private $dtCr;

    /**
     * @var \DateTime
     *
     * @Column(name="dt_up", type="datetime", nullable=true)
     */
    private $dtUp;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
