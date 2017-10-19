<?php

namespace Controller;

use Framework\Application as App;

use Entity\Folder;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class FolderController extends BaseController
{
    public function getFoldersAction()
    {
        $userId = App::user()->getId();

        $folders = App::em()->getRepository('Entity\Folder')
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
        $userId = App::user()->getId();

        $folder = App::em()->getRepository('Entity\Folder')->setExpanded($id, $expanded, $userId);

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
        $folder = new Folder;
        $folder->set([
            'name' => $folderName,
            'path' => 0, // root
            'userId' => App::user()->getId(),
            'isExpanded' => true
        ]);

        App::em()->persist($folder);
        App::em()->flush();

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

        $folder = App::em()->find('Entity\Folder', $id);

        if (!$folder) {
            throw new NotFoundException("Folder id: ". $id);
        }

        $folder = App::em()
            ->find('Entity\Folder', [
                'id' => $id,
                'userId' => App::user()->getId()
            ]);

        if (!$folder) {
            throw new ForbiddenException('requested folder not avaliable for current user. Folder id: '. $id);
        }

        if (!App::rbac()->check('deleteFolder')) {
            throw new ForbiddenException('action: deleteFolder. Folder id: '. $id);
        }

        $result = App::dic()->get('folder')->deleteFolderContent($id);

        if (!$result) {
            throw new Exception('Cant delete folder. Folder id: '. $id);
        }

        return json_encode('ok');
    }

    public function ChangeFolderPath($data)
    {
        if (!isset($data['id'])
            || !isset($data['parentId'])
            || !is_int(intval($data['id']))
            || !is_int(intval($data['parentId']))
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $userId = intval($this->_user->userInfo['id']);
        $sender = intval($data['id']);
        $target = intval($data['parentId']);

        $Fd = new Folder;
        $result = $Fd->ChangeFolderPath($sender, $target, $userId);
        unset($Fd);

        return json_encode([
            'id' => $sender,
            'parentId' => $target
        ]);
    }

   public function renameFolder($data)
   {
       if(!isset($data['id'])
           || !isset($data['name'])
       ) {
           throw new BadRequestException(json_encode($data));
       }

       $folderId = $data['id'];
       $folderName = $data['name'];

       $userId = intval($this->_user->userInfo['id']);

       $Fd = new Folder;
       $Fd->RenameFolder($folderId, $folderName, $userId);
       unset($Fd);

       return json_encode('ok');
   }
}
