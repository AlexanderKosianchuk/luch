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

    /**
     * One User has One User.
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="id_creator", referencedColumnName="id")
     */
    private $creator;

    public function getCreator()
    {
        return $this->creator;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getLogin()
    {
        return strval($this->login);
    }

    public function getName()
    {
        return strval($this->name);
    }

    public function getLang()
    {
        return strval($this->lang);
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function getCompany()
    {
        return $this->company;
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

    public function setLanguage($lang)
    {
        $this->lang = $lang;
    }

    public function set($data)
    {
        $this->login = $data['login'];
        $this->pass = md5($data['pass']);
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        $this->role = $data['role'];
        $this->company = $data['company'];
        $this->creator = $data['creator'];

        if (isset($data['lang'])) {
            $this->lang = $data['lang'];
        }

        $logoPath = SITE_ROOT_DIR.DIRECTORY_SEPARATOR.$data['logo'];

        if (file_exists($logoPath)) {
            $this->logo = stream_get_contents(fopen($logoPath,'rb'));
        }
    }
}
