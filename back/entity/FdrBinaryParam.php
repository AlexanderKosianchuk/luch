<?php

namespace Entity;

/**
 * FdrBinaryParam
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrBinaryParam
{
    private static $_prefix = '_bp';

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
     * @Column(name="channel", type="string", length=255, nullable=false)
     */
    private $channel;

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
     * @var string
     *
     * @Column(name="dim", type="string", length=9, nullable=false)
     */
    private $color;

    /**
     * @var integer
     *
     * @Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @Column(name="prefix", type="string", length=9, nullable=false)
     */
    private $prefix;

    /**
     * @var integer
     *
     * @Column(name="mask", type="integer", nullable=false)
     */
    private $mask;

    /**
     * @var integer
     *
     * @Column(name="basis", type="integer", nullable=false)
     */
    private $basis;

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function get($isArray = false)
    {
        $arr = [
            'id' => $this->id,
            'channel' => $this->channel,
            'code' => $this->code,
            'name' => $this->name,
            'color' => $this->color,
            'type' => $this->type,
            'prefix' => $this->prefix,
            'mask' => $this->mask,
            'basis' => $this->basis
        ];

        if ($isArray) {
            return $arr;
        }

        return (object) $arr;
    }


}
