<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class FlightCommentController extends BaseController
{
  public function getAction($flightId)
  {
    $userId = $this->user()->getId();

    $flightsToFolders = $this->em()
      ->getRepository('Entity\FlightToFolder')
      ->findBy(['userId' => $userId, 'flightId' => $flightId]);

    if (!$flightsToFolders) {
      throw new ForbiddenException('flight not avaliable for this user. FlightId: '.$flightId);
    }

    $flight = $this->em()
      ->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('flight not found. FlightId: '.$flightId);
    }

    $fc = $this->em()
      ->getRepository('Entity\FlightComments')
      ->findOneBy(['flightId' => $flightId]);

    if (!$fc) {
      return json_encode([
        'comment' => '',
        'commanderAdmitted' => false,
        'aircraftAllowed' => false,
        'generalAdmission' => false,
        'userId' => $userId,
        'flightId' => $flightId
      ]);
    }

    return json_encode([
      'comment' => $fc->getComment(),
      'commanderAdmitted' => $fc->getCommanderAdmitted(),
      'aircraftAllowed' => $fc->getAircraftAllowed(),
      'generalAdmission' => $fc->getGeneralAdmission(),
      'userId' => $fc->getUserId(),
      'flightId' => $fc->getFlightId()
    ]);
  }

  public function setAction(
    $flightId,
    $comment,
    $commanderAdmitted,
    $aircraftAllowed,
    $generalAdmission
  ) {
    $commanderAdmitted = ($commanderAdmitted === 'true');
    $aircraftAllowed = ($aircraftAllowed === 'true');
    $generalAdmission = ($generalAdmission === 'true');
    $userId = $this->user()->getId();

    $flightsToFolders = $this->em()
      ->getRepository('Entity\FlightToFolder')
      ->findBy(['userId' => $userId, 'flightId' => $flightId]);

    if (!$flightsToFolders) {
      throw new ForbiddenException('flight not avaliable for this user. FlightId: '.$flightId);
    }

    $flight = $this->em()
      ->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('flight not found. FlightId: '.$flightId);
    }

    $fc = $this->em()
      ->getRepository('Entity\FlightComments')
      ->findBy(['flightId' => $flightId, 'userId' => $userId]);

    foreach ($fc as $item) {
      $this->em()->remove($item);
    }
    $this->em()->flush();

    $fc = new \Entity\FlightComments();
    $fc->setComment($comment);
    $fc->setCommanderAdmitted($commanderAdmitted);
    $fc->setAircraftAllowed($aircraftAllowed);
    $fc->setGeneralAdmission($generalAdmission);
    $fc->setUserId($userId);
    $fc->setFlightId($flightId);

    $this->em()->persist($fc);
    $this->em()->flush();

    return json_encode([
      'comment' => $fc->getComment(),
      'commanderAdmitted' => $fc->getCommanderAdmitted(),
      'aircraftAllowed' => $fc->getAircraftAllowed(),
      'generalAdmission' => $fc->getGeneralAdmission(),
      'userId' => $fc->getUserId(),
      'flightId' => $fc->getFlightId()
    ]);
  }
}
