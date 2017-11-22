<?php



namespace Entity;

/**
 * UserAuth
 *
 * @Table(name="user_auth")
 * @Entity
 */
class UserAuth
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
     * @var integer
     *
     * @Column(name="id_user", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @Column(name="token", type="string", length=45, nullable=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @Column(name="exp", type="datetime", nullable=false)
     */
    private $exp;

    /**
     * @var \DateTime
     *
     * @Column(name="dt", type="datetime", nullable=false)
     */
    private $dt;

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getExp()
    {
        return $this->exp;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setExp($exp)
    {
        $this->exp = $exp;
    }
}
