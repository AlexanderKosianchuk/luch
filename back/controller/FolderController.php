<?php

namespace Controller;

use Exception\NotFoundException;
use Exception\ForbiddenException;

class FolderController extends BaseController
{
    public function getFoldersAction()
    {
        $userId = $this->user()->getId();

        $folders = $this->em()->getRepository('Entity\Folder')
            ->findBy(['userId' => $userId]);

        $items = [];
        foreach ($folders as $folder) {
            $items[] = array_merge(
                $folder->get(),
                [
                    'type' => 'folder',
                    'parentId' => intval($folder->getPath()),
                    'expanded' => boolval($folder->getIsExpanded())
                ]
            );
        }

        return json_encode($items);
    }

    public function toggleFolderExpandingAction($id, $expanded)
    {
        $id = intval($id);
        $expanded = ($expanded === 'true') ? true : false;
        $userId = $this->user()->getId();

        $folder = $this->em()->getRepository('Entity\Folder')->setExpanded($id, $expanded, $userId);

        if ($folder === null) {
            throw new NotFoundException("requested folder not found. Folder id: ". $id);
        }

        return json_encode([
            'id' => $id,
            'expanded' => $expanded
        ]);
    }

    public function createFolderAction($folderName)
    {
        $folder = new \Entity\Folder;;
        $folder->set([
            'name' => $folderName,
            'path' => 0, // root
            'userId' => $this->user()->getId(),
            'isExpanded' => true
        ]);

        $this->em()->persist($folder);
        $this->em()->flush();

       return json_encode(array_merge(
           $folder->get(), [
               'type' => 'folder',
               'parentId' => $folder->getPath()
           ]
       ));
    }

    public function deleteFolderAction($id)
    {
        $id = intval($id);

        $folder = $this->em()->find('Entity\Folder', $id);

        if (!$folder) {
            throw new NotFoundException("Folder id: ". $id);
        }

        $folder = $this->em()
            ->find('Entity\Folder', [
                'id' => $id,
                'userId' => $this->user()->getId()
            ]);

        if (!$folder) {
            throw new ForbiddenException('requested folder not avaliable for current user. Folder id: '. $id);
        }

        if (!$this->rbac()->check('deleteFolder')) {
            throw new ForbiddenException('action: deleteFolder. Folder id: '. $id);
        }

        $result = $this->dic()->get('folder')->deleteFolderContent($id);

        if (!$result) {
            throw new Exception('Cant delete folder. Folder id: '. $id);
        }

        return json_encode('ok');
    }

    public function renameFolderAction($id, $name)
    {
        $userId = $this->user()->getId();

        $folder = $this->em()->find('Entity\Folder', [
            'id' => $id,
            'userId' => $userId
        ]);

        if (!$folder) {
            throw new NotFoundException("Folder id: ". $id);
        }

        $folder->setName($name);

        $this->em()->merge($folder);
        $this->em()->flush();

        return json_encode('ok');
    }

    public function changeFolderPathAction($id, $parentId)
    {
        $sender = intval($id);
        $target = intval($parentId);

        $userId = $this->user()->getId();

        $folder = $this->em()->find('Entity\Folder', [
            'id' => $sender,
            'userId' => $userId
        ]);

        if (!$folder) {
            throw new NotFoundException("Folder id: ". $id);
        }

        $folder->setPath($target);

        $this->em()->merge($folder);
        $this->em()->flush();

        return json_encode([
            'id' => $sender,
            'parentId' => $target
        ]);
    }


}
