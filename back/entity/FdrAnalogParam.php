<?php

namespace Entity;

use EntityTraits\dynamicTable;

/**
 * FdrAnalogParam
 *
 * @Table(name="NULL")
 * @Entity
 */
class FdrAnalogParam
{
    use dynamicTable;
    private static $_prefix = '_ap';

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
     * @Column(name="dim", type="string", length=20, nullable=false)
     */
    private $dim;

    /**
     * @var integer
     *
     * @Column(name="minValue", type="integer", nullable=false)
     */
    private $minValue;

    /**
     * @var integer
     *
     * @Column(name="maxValue", type="integer", nullable=false)
     */
    private $maxValue;

    /**
     * @var string
     *
     * @Column(name="color", type="string", length=9, nullable=false)
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
     * @Column(name="shift", type="integer", nullable=false)
     */
    private $shift;

    /**
     * @var integer
     *
     * @Column(name="minus", type="integer", nullable=false)
     */
    private $minus;

    /**
     * @var integer
     *
     * @Column(name="k", type="float", nullable=false)
     */
    private $k;

    /**
     * @var string
     *
     * @Column(name="xy", type="string", length=65535, nullable=false)
     */
    private $xy;

    /**
     * @var string
     *
     * @Column(name="alg", type="string", length=65535, nullable=false)
     */
    private $alg;

    public function getId()
    {
        return $this->id;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getFrequency()
    {
        $channels = $this->getChannel();
        return is_array($channels) ? count($channels) : 1;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMinValue($minValue)
    {
        $this->minValue = $minValue;
    }

    public function getMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
    }

    public function getChannel()
    {
        if (strpos($this->channel, ',') !== -1) {
            $channels = explode(',', $this->channel);
            $channels = array_map('trim', $channels);
            return $channels;
        }

        return $this->channel;
    }

    public function isCalibrated()
    {
        return isset($this->xy)
            && ($this->xy !== '')
            && ($this->xy !== 0)
            && (strlen($this->xy) > 2);
    }

    public function get($isArray = false)
    {
        $arr = [
            'id' => $this->id,
            'channel' => $this->getChannel(),
            'code' => $this->code,
            'name' => $this->name,
            'dim' => $this->dim,
            'minValue' => $this->minValue,
            'maxValue' => $this->maxValue,
            'color' => $this->color,
            'type' => $this->type,
            'prefix' => $this->prefix,
            'mask' => $this->mask,
            'shift' => $this->shift,
            'minus' => $this->minus,
            'k' => $this->k,
            'frequency' => $this->getFrequency(),
            'xy' => (strlen($this->xy) > 2) ? json_decode($this->xy, true) : [],
            'alg' => $this->alg
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
