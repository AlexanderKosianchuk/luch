<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use Entity\FlightToFolder;

class FlightToFolderRepository extends EntityRepository
{
    public function getTreeItem($flightId, $userId)
    {
        $flightToFolders = $this->findOneBy(
            ['userId' => $userId, 'flightId' => $flightId]
        );

        $startCopyTimeFormated = date('d/m/y H:i:s', $flightToFolders->getFlight()->getStartCopyTime());

        $flight = $flightToFolders->getFlight();

        $item = array_merge(
            $flight->get(true), [
                'noChildren' => true,
                'type' => 'flight',
                'parentId' => $flightToFolders->getFolderId(),
                'startCopyTimeFormated' => $startCopyTimeFormated,
                'fdrName' => $flight->getFdr()->getName()
            ]
        );

        return $item;
    }

    public function insert($folderId, $userId, $flight)
    {
        $em = $this->getEntityManager();

        $ftf = new FlightToFolder;
        $ftf->setFolderId($folderId);
        $ftf->setUserId($userId);
        $ftf->setFlight($flight);

        $em->persist($ftf);
        $em->flush();

        return $ftf;
    }
}
