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
    private $userId;

    public function getId()
    {
        return $this->id;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function get()
    {
        return [
            'id' => intval($this->id),
            'name' => $this->name,
            'path' => $this->path,
            'userId' => $this->userId,
        ];
    }

}
