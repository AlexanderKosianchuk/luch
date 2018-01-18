<?php



namespace Entity;

/**
 * UserActivity
 *
 * @Table(name="user_activity")
 * @Entity
 */
class UserActivity
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
   * @Column(name="action", type="string", length=255, nullable=false)
   */
  private $action;

  /**
   * @var string
   *
   * @Column(name="status", type="string", length=255, nullable=false)
   */
  private $status;

  /**
   * @var integer
   *
   * @Column(name="code", type="integer", nullable=false)
   */
  private $code;

  /**
   * @var string
   *
   * @Column(name="message", type="string", length=5000, nullable=false)
   */
  private $message;

  /**
   * @var \DateTime
   *
   * @Column(name="date", type="datetime", nullable=false)
   */
  private $date;

  /**
   * @var integer
   *
   * @Column(name="id_user", type="integer", nullable=false)
   */
  private $userId;

  public function setAttributes($data)
  {
    $this->action = $data['action'];
    $this->status = $data['status'];
    $this->code = $data['code'];
    $this->message = $data['message'];
    $this->userId = $data['userId'];
  }

  public function get()
  {
    return [
      'id' => $this->id,
      'action' => $this->action,
      'status' => $this->status,
      'code' => $this->code,
      'message' => $this->message,
      'date' => $this->date->format('y/m/d H:i:s'),
      'userId' => $this->userId
    ];
  }
}
