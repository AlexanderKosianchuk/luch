<?php

namespace Entity;

use EntityTraits\dynamicTable;

/**
 * FdrBinaryParam
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrBinaryParam
{
    use dynamicTable;
    public static $_prefix = '_bp';

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
     * @var integer
     *
     * @Column(name="type", type="integer", nullable=false)
     */
    private $type;

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

    /**
     * @var string
     *
     * @Column(name="color", type="string", length=9, nullable=false)
     */
    private $color;

    /**
     * @var string
     *
     * @Column(name="prefix", type="string", length=9, nullable=false)
     */
    private $prefix;

    public function getCode()
    {
        return $this->code;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function get($isArray = false)
    {
        $channels = $this->channel;

        if (strpos($this->channel, ',') !== -1) {
            $channels = explode(',', $this->channel);
            $channels = array_map('trim', $channels);
        }

        $arr = [
            'id' => $this->id,
            'channel' => $channels,
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

    public static function getTablePrefix()
    {
        return self::$_prefix;
    }
}
