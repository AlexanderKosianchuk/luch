<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use Exception;

class AirportRepository extends EntityRepository
{
    public function getAirportByLatAndLong($lat, $long)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        return $qb->select('a')
           ->from('Entity\Airport', 'a')
           ->andWhere('a.runwayStartLat > ?1')
           ->andWhere('a.runwayStartLat < ?2')
           ->andWhere('a.runwayStartLong > ?3')
           ->andWhere('a.runwayStartLong < ?4')
           ->setParameter(1, $lat - 0.02)
           ->setParameter(2, $lat + 0.02)
           ->setParameter(3, $long - 0.02)
           ->setParameter(4, $long + 0.02)
           ->getQuery()
           ->getArrayResult();
    }
}
