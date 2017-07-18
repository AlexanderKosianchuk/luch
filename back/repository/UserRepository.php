<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Entity\User;

use Component\RealConnectionFactory as LinkFactory;

use Exception;

class UserRepository extends EntityRepository
{
    public static $role = [
        'admin' => 'admin',
        'moderator' => 'moderator',
        'user' => 'user',
        'local' => 'local'
    ];

    public static function isAdmin($userRole) {
        if ($userRole == self::$role['admin']) {
            return true;
        }

        return false;
    }

    public static function isModerator($userRole) {
        if ($userRole == self::$role['moderator']) {
            return true;
        }

        return false;
    }

    public static function isUser($userRole) {
        if ($userRole == self::$role['user']) {
            return true;
        }

        return false;
    }

    public static function isLocal($userRole) {
        if($userRole == self::$role['local']) {
            return true;
        }

        return false;
    }

    public function getUsers($userId)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int is required. Passed: "
                . json_encode($userId), 1);
        }

        $user = $this->findOneBy(['id' => $userId]);
        $role = $user->getRole();
        $users = [];

        switch($role) {
            case self::$role['admin']:
                $users = $this->createQueryBuilder('users')
                    ->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);
            break;
            case self::$role['moderator']:
                $users = $this->createQueryBuilder('users')
                    ->from('Entity\User', 'u')
                    ->where('u.creatorId = :creatorId')
                    ->setParameter('creatorId', $userId)
                    ->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);
            break;
        }

        $usersWithLogo = [];
        foreach ($users as $user) {
            if (isset($user['logo'])) {
                $user['logo'] = self::getLogoUrl($user['id']);
            }

            $usersWithLogo[] = $user;
        }

        return $usersWithLogo;
    }

    public static function getLogoUrl($userId)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int is required. Passed: "
                . json_encode($userId), 1);
        }

        return 'action=users/getLogo&id='.$userId;
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
            case self::$role['admin']:
                return true;
            break;
            case self::$role['moderator']:
                $user = $this->findOneBy(['id' => $userId]);
                if ($user->getCreatorId() === $creatorId) {
                    return true;
                }
            break;
        }

        return false;
    }
}
