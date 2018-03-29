<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Entity\User;

use Component\RealConnectionFactory as LinkFactory;

use Exception;

class UserRepository extends EntityRepository
{
  public function getAdmins()
  {
    return $this->findBy(['role' => 'admin']);
  }

  public function isAvaliable($userId, $creatorId)
  {
    if (!is_int($userId)) {
      throw new Exception("Incorrect userId passed. Int is required. Passed: "
        . json_encode($userId), 1);
    }

    if (!is_int($creatorId)) {
      throw new Exception("Incorrect creatorId passed. Int is required. Passed: "
        . json_encode($creatorId), 1);
    }

    $creator = $this->findOneBy(['id' => $creatorId]);
    $role = $creator->getRole();

    switch($role) {
      case 'admin':
        return true;
      break;
      case 'moderator':
        $user = $this->findOneBy(['id' => $userId]);
        if ($user->getCreatorId() === $creatorId) {
          return true;
        }
      break;
    }

    return false;
  }
}
