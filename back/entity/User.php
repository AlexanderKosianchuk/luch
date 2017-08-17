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
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone;

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

    public function get()
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'pass' => $this->pass,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'lang' => $this->lang,
            'role' => $this->role,
            'company' => $this->company,
            'creatorId' => $this->creatorId,
        ];
    }

    public function set($data)
    {
        $this->login = $data['login'];
        $this->pass = $data['pass'];
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        $this->lang = $data['lang'];
        $this->role = $data['role'];
        $this->company = $data['company'];
        $this->creatorId = $data['creatorId'];
        $this->logo = $data['logo'];
    }
}
