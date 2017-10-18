<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

class FlightToFolderRepository extends EntityRepository
{
    public function getTreeItem($flightId, $userId)
    {
        $flightToFolders = $this->findOneBy(
            ['userId' => $userId, 'flightId' => $flightId]
        );

        $startCopyTimeFormated = date('d/m/y H:i:s', $flightToFolders->getFlight()->getStartCopyTime());

        $item = array_merge(
            $flightToFolders->getFlight()->get(), [
                'noChildren' => true,
                'type' => 'flight',
                'parentId' => $flightToFolders->getFolderId(),
                'startCopyTimeFormated' => $startCopyTimeFormated,
            ]
        );

        return $item;
    }
}
