<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Fdr
 *
 * @Table(name="fdrs")
 * @Entity
 */
class Fdr
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
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Column(name="code", type="string", length=255, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @Column(name="gradiApTableName", type="string", length=255, nullable=false)
     */
    private $gradiaptablename;

    /**
     * @var string
     *
     * @Column(name="gradiBpTableName", type="string", length=255, nullable=false)
     */
    private $gradibptablename;

    /**
     * @var string
     *
     * @Column(name="excListTableName", type="string", length=255, nullable=false)
     */
    private $exclisttablename;

    /**
     * @var string
     *
     * @Column(name="paramSetTemplateListTableName", type="string", length=20, nullable=false)
     */
    private $paramsettemplatelisttablename;

    /**
     * @var float
     *
     * @Column(name="stepLength", type="float", precision=10, scale=0, nullable=false)
     */
    private $stepLength;

    /**
     * @var integer
     *
     * @Column(name="stepDivider", type="integer", nullable=false)
     */
    private $stepDivider;

    /**
     * @var integer
     *
     * @Column(name="frameLength", type="integer", nullable=false)
     */
    private $frameLength;

    /**
     * @var integer
     *
     * @Column(name="wordLength", type="integer", nullable=false)
     */
    private $wordLength;

    /**
     * @var string
     *
     * @Column(name="aditionalInfo", type="text", length=65535, nullable=false)
     */
    private $aditionalInfo;

    /**
     * @var integer
     *
     * @Column(name="headerLength", type="integer", nullable=false)
     */
    private $headerLength;

    /**
     * @var string
     *
     * @Column(name="headerScr", type="text", length=65535, nullable=false)
     */
    private $headerScr;

    /**
     * @var string
     *
     * @Column(name="frameSyncroCode", type="string", length=8, nullable=false)
     */
    private $frameSyncroCode;

    /**
     * @var string
     *
     * @Column(name="previewParams", type="string", length=255, nullable=false)
     */
    private $previewParams;

    /**
     * @var string
     *
     * @Column(name="author", type="string", length=200, nullable=false)
     */
    private $author;

    /**
     * @var string
     *
     * @Column(name="kml_export_script", type="text", length=65535, nullable=false)
     */
    private $kmlExportScript;

    /**
     * One Fdr has Many EventToFdrs.
     * @OneToMany(targetEntity="EventToFdr", mappedBy="fdr")
     */
    private $eventsToFdr;

    /**
     * One Fdr has Many FdrToUser.
     * @OneToMany(targetEntity="FdrToUser", mappedBy="fdr")
     */
    private $fdrToUser;

    public function __construct()
    {
        $this->eventsToFdr = new ArrayCollection();
        $this->fdrToUser = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getStepLength()
    {
        return $this->stepLength;
    }

    public function getFrameLength()
    {
        return $this->frameLength;
    }

    public function getEventsToFdr()
    {
        return $this->eventsToFdr;
    }

    public function get()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'gradiApTableName' => $this->gradiApTableName,
            'gradiBpTableName' => $this->gradiBpTableName,
            'excListTableName' => $this->excListTableName,
            'paramSetTemplateListTableName' => $this->paramSetTemplateListTableName,
            'stepLength' => $this->stepLength,
            'stepDivider' => $this->stepDivider,
            'frameLength' => $this->frameLength,
            'wordLength' => $this->wordLength,
            'aditionalInfo' => $this->aditionalInfo,
            'headerLength' => $this->headerLength,
            'headerScr' => $this->headerScr,
            'frameSyncroCode' => $this->frameSyncroCode,
            'previewParams' => $this->previewParams,
            'author' => $this->author,
            'kmlExportScript' => $this->kmlExportScript
        ];
    }
}
