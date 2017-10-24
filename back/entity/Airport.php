<?php



namespace Entity;

/**
 * Airports
 *
 * @Table(name="airports")
 * @Entity(repositoryClass="Repository\AirportRepository")
 */
class Airport
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
     * @Column(name="IATA", type="string", length=3, nullable=false)
     */
    private $iata;

    /**
     * @var string
     *
     * @Column(name="ICAO", type="string", length=4, nullable=false)
     */
    private $icao;

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Column(name="city", type="string", length=255, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @Column(name="country", type="string", length=255, nullable=false)
     */
    private $country;

    /**
     * @var string
     *
     * @Column(name="runway", type="string", length=10, nullable=false)
     */
    private $runway;

    /**
     * @var float
     *
     * @Column(name="magnVariation", type="float", precision=10, scale=0, nullable=false)
     */
    private $magnvariation;

    /**
     * @var float
     *
     * @Column(name="runwayStartLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $runwaystartlat;

    /**
     * @var float
     *
     * @Column(name="runwayStartLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $runwaystartlong;

    /**
     * @var float
     *
     * @Column(name="runwayStartElev", type="float", precision=10, scale=0, nullable=false)
     */
    private $runwaystartelev;

    /**
     * @var float
     *
     * @Column(name="runwayEndLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $runwayendlat;

    /**
     * @var float
     *
     * @Column(name="runwayEndLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $runwayendlong;

    /**
     * @var float
     *
     * @Column(name="runwayEndElev", type="float", precision=10, scale=0, nullable=false)
     */
    private $runwayendelev;

    /**
     * @var integer
     *
     * @Column(name="course", type="integer", nullable=false)
     */
    private $course;

    /**
     * @var float
     *
     * @Column(name="length", type="float", precision=10, scale=0, nullable=false)
     */
    private $length;

    /**
     * @var float
     *
     * @Column(name="width", type="float", precision=10, scale=0, nullable=false)
     */
    private $width;

    /**
     * @var float
     *
     * @Column(name="ILSAlt", type="float", precision=10, scale=0, nullable=false)
     */
    private $ilsalt;

    /**
     * @var float
     *
     * @Column(name="ILSDist", type="float", precision=10, scale=0, nullable=false)
     */
    private $ilsdist;

    /**
     * @var float
     *
     * @Column(name="ILSLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $ilslat;

    /**
     * @var float
     *
     * @Column(name="ILSLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $ilslong;

    /**
     * @var float
     *
     * @Column(name="landingTransLevel", type="float", precision=10, scale=0, nullable=false)
     */
    private $landingtranslevel;

    /**
     * @var float
     *
     * @Column(name="takeoffTransAlt", type="float", precision=10, scale=0, nullable=false)
     */
    private $takeofftransalt;

    /**
     * @var float
     *
     * @Column(name="OMAlt", type="float", precision=10, scale=0, nullable=false)
     */
    private $omalt;

    /**
     * @var float
     *
     * @Column(name="OMDist", type="float", precision=10, scale=0, nullable=false)
     */
    private $omdist;

    /**
     * @var float
     *
     * @Column(name="OMLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $omlat;

    /**
     * @var float
     *
     * @Column(name="OMLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $omlong;

    /**
     * @var float
     *
     * @Column(name="IMAlt", type="float", precision=10, scale=0, nullable=false)
     */
    private $imalt;

    /**
     * @var float
     *
     * @Column(name="IMDist", type="float", precision=10, scale=0, nullable=false)
     */
    private $imdist;

    /**
     * @var float
     *
     * @Column(name="IMLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $imlat;

    /**
     * @var float
     *
     * @Column(name="IMLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $imlong;

    /**
     * @var float
     *
     * @Column(name="MMAlt", type="float", precision=10, scale=0, nullable=false)
     */
    private $mmalt;

    /**
     * @var float
     *
     * @Column(name="MMDist", type="float", precision=10, scale=0, nullable=false)
     */
    private $mmdist;

    /**
     * @var float
     *
     * @Column(name="MMLat", type="float", precision=10, scale=7, nullable=false)
     */
    private $mmlat;

    /**
     * @var float
     *
     * @Column(name="MMLong", type="float", precision=10, scale=7, nullable=false)
     */
    private $mmlong;


}
