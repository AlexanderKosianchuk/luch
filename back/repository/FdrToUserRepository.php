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

        $ids = [];
        foreach ($fdrsToUser as $item) {
            if (!in_array($item->getFdrId(), $ids)) {
                $fdrs[] = $item->getFdr();
                $ids[] = $item->getFdrId();
            }
        }

        return $fdrs;
    }
}
