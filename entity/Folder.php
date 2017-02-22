<?php



namespace Entity;

/**
 * Folders
 *
 * @Table(name="folders")
 * @Entity
 */
class Folder
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
     * @var string
     *
     * @Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @Column(name="path", type="integer", nullable=true)
     */
    private $path;

    /**
     * @var integer
     *
     * @Column(name="userId", type="integer", nullable=true)
     */
    private $userid;

    public function getId()
    {
        return $this->id;
    }

}
