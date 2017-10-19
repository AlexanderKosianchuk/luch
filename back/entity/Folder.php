<?php



namespace Entity;

/**
 * Folders
 *
 * @Table(name="folders")
 * @Entity(repositoryClass="Repository\FolderRepository")
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
     * @Column(name="is_expanded", type="boolean", nullable=false)
     */
    private $isExpanded;

    /**
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=true)
     */
    private $userId;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getIsExpanded()
    {
        return $this->isExpanded;
    }

    public function get()
    {
        return [
            'id' => intval($this->id),
            'name' => $this->name,
            'path' => $this->path,
            'userId' => $this->userId,
            'isExpanded' => $this->isExpanded
        ];
    }

    public function set($obj)
    {
        $this->name = $obj['name'];
        $this->path = $obj['path'];
        $this->userId = $obj['userId'];
        $this->isExpanded = isset($obj['isExpanded']) ? $obj['isExpanded'] : 0;
    }

    public function setExpanded($isExpanded)
    {
        $this->isExpanded = $isExpanded;
    }
}
