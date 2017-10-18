<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Exception;

class FdrToUserRepository extends EntityRepository
{
    public function getAvaliableFdrs($userId)
    {
        $fdrsToUser = $this->findBy(['userId' => $userId]);
        $fdrs = [];

        foreach ($fdrsToUser as $item) {
            $fdrs[] = $item->getFdr();
        }

        return $fdrs;
    }
}
