<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use \Entity\Flight;

class FlightRepository extends EntityRepository
{
  public function insert($guid, $flightInfo, $fdr, $user, $calibration = null)
  {
    $em = $this->getEntityManager();

    $flight = new Flight;
    $flight->set(
      $guid,
      $flightInfo,
      $fdr,
      $user,
      $calibration
    );
    $em->persist($flight);
    $em->flush();

    return $flight;
  }
}
