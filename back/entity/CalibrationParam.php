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

    /**
     * One CalibrationParam has One Calibration.
     * @OneToOne(targetEntity="Calibration")
     * @JoinColumn(name="id_calibration", referencedColumnName="id")
     */
    private $calibration;

    /**
     * One CalibrationParam has One FdrAnalogParam.
     * @OneToOne(targetEntity="FdrAnalogParam")
     * @JoinColumn(name="id_param", referencedColumnName="id")
     */
    private $fdrAnalogParam;

    public function getId()
    {
        return $this->id;
    }

    public function getCalibration()
    {
        return $this->calibration;
    }

    public function getFdrAnalogParam()
    {
        return $this->fdrAnalogParam;
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
}
