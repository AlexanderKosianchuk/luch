<?php

namespace Entity;

use EntityTraits\dynamicTable;

/**
 * FdrTemplate
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrTemplate
{
    use dynamicTable;
    public static $_prefix = '_pst';

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
     * @Column(name="name", type="string", length=20, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Column(name="paramCode", type="string", length=20, nullable=false)
     */
    private $paramCode;

    /**
     * @var integer
     *
     * @Column(name="minYaxis", type="float", nullable=true)
     */
    private $minYaxis;

    /**
     * @var integer
     *
     * @Column(name="maxYaxis", type="float", nullable=true)
     */
    private $maxYaxis;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $userId;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setParamCode($paramsCode)
    {
        $this->paramCode = $paramsCode;
    }

    public function setMinYaxis($minYaxis)
    {
        $this->minYaxis = $minYaxis;
    }

    public function setMaxYaxis($maxYaxis)
    {
        $this->maxYaxis = $maxYaxis;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getParamCode()
    {
        return $this->paramCode;
    }

    public static function getPrefix()
    {
        return self::$_prefix;
    }
}
