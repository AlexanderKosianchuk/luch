<?php

namespace Component;

use Doctrine\ORM\Query;

use Exception;

class UserManagementComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Entity\FdrToUser
     */
    private $FdrToUser;

    public function getUsers()
    {
        $users = [];

        switch ($this->user()->getRole()) {
            case 'admin':
                $users = $this->em()
                    ->getRepository('Entity\User')
                    ->createQueryBuilder('users')
                    ->getQuery()
                    ->getResult(Query::HYDRATE_ARRAY);
            break;
            case 'moderator':
                $users = $this->em()
                    ->getRepository('Entity\User')
                    ->createQueryBuilder('users')
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
                $user['logo'] = 'users/getLogo/id/'.$user['id'];
            }

            $usersWithLogo[] = $user;
        }

        return $usersWithLogo;
    }

    public function setFdrAvailable($userId, $fdr)
    {
        $FdrToUser = $this->FdrToUser;
        $fdrToUser = new $FdrToUser;
        $fdrToUser->setUserId($userId);
        $fdrToUser->setFdr($fdr);

        $this->em()->persist($fdrToUser);
        $this->em()->flush();

        return $fdrToUser;
    }
}
