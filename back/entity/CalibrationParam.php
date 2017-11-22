<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;

use EntityTraits\dynamicTable;

/**
 * CalibrationParam
 *
 * @Table(name="NULL", indexes={@Index(name="id_calibration", columns={"id_calibration"}), @Index(name="id_param", columns={"id_param"})})
 * @Entity
 */
class CalibrationParam
{
    use dynamicTable;
    private static $_prefix = '_c';

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
     * @Column(name="id_calibration", type="integer", nullable=false)
     */
    private $calibrationId;

    /**
     * @var integer
     *
     * @Column(name="id_param", type="integer", nullable=false)
     */
    private $paramId;

    /**
     * @var string
     *
     * @Column(name="xy", type="string", length=65535, nullable=false)
     */
    private $xy;

    public function getId()
    {
        return $this->id;
    }

    public function getParamId()
    {
        return $this->paramId;
    }

    public function getXy()
    {
        return (strlen($this->xy) > 2) ? json_decode($this->xy, true) : [];
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'calibrationId' => $this->calibrationId,
            'paramId' => $this->paramId,
            'xy' => json_decode($this->xy)
        ];
    }

    public function set($data)
    {
        $this->calibrationId = $data['calibrationId'];
        $this->paramId = $data['paramId'];
        $this->xy = json_encode($data['xy']);
    }

    public static function getPrefix()
    {
        return self::$_prefix;
    }
}
