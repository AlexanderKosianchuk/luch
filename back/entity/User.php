<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserPersonal
 *
 * @Table(name="user_personal", uniqueConstraints={@UniqueConstraint(name="login", columns={"login"})}, indexes={@Index(name="login_2", columns={"login"})})
 * @Entity(repositoryClass="Repository\UserRepository")
 */
class User
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
     * @Column(name="login", type="string", length=200, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @Column(name="pass", type="string", length=45, nullable=false)
     */
    private $pass;

    /**
     * @var string
     *
     * @Column(name="lang", type="string", length=10, nullable=false)
     */
    private $lang;

    /**
     * @var string
     *
     * @Column(name="role", type="string", length=255, nullable=false)
     */
    private $role;

    /**
     * @var string
     *
     * @Column(name="company", type="string", length=200, nullable=false)
     */
    private $company;

    /**
     * @var string
     *
     * @Column(name="logo", type="blob", length=16777215, nullable=true)
     */
    private $logo;

    /**
     * @var integer
     *
     * @Column(name="id_creator", type="integer", nullable=true)
     */
    private $creatorId;

    public function getRole()
    {
        return $this->role;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function getCreatorId()
    {
        return $this->creatorId;
    }
}
