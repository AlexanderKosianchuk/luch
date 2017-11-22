<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Exception;

class FolderRepository extends EntityRepository
{
    public function setExpanded($folderId, $expanded, $userId)
    {
        $folder = $this->findOneBy([
            'id' => $folderId,
            'userId' => $userId
        ]);

        if (!$folder) {
            return null;
        }

        $folder->setExpanded($expanded);
        $this->getEntityManager()->flush();

        return $folder;
    }

    public function delete($folderId, $userId)
    {
        $em = $this->getEntityManager();
        $folder = $em
            ->getRepository('Entity\Folder')
            ->findOneBy([
                'id' => $folderId,
                'userId' => $userId
            ]);

        $em->remove($folder);
        $em->flush();
    }
}
