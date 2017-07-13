<?php

namespace Controller;

use Model\User;
use Model\Folder;
use Model\Flight;

use Entity\Folder as FolderEntity;

use Component\EntityManagerComponent as EM;
use Component\FlightComponent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class FolderController extends CController
{
    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $get = $_GET;
        if(isset($get['action']) && ($get['action'] != '')) {
            $this->getAction = $get['action'];
        }
    }

    public function createFolder($data)
    {
        if (!isset($data['name']) ) {
            throw new BadRequestException(json_encode($data));
        }

        $folderName = $data['name'];
        $fullpath = 0; // root

        $userId = intval($this->_user->userInfo['id']);

        $em = EM::get();

        $folder = new FolderEntity;
        $folder->set([
            'name' => $folderName,
            'path' => $fullpath,
            'userId' => $userId,
            'isExpanded' => 1
        ]);
        $em->persist($folder);
        $em->flush();

       return json_encode(array_merge(
           $folder->get(),
           [
               'type' => 'folder',
               'parentId' => intval($folder->getPath())
           ]
       ));
    }

    public function getFolders($args)
    {
        $userId = intval($this->_user->userInfo['id']);

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId used in getFolders FlightsController." . $userId, 1);
        }

        $em = EM::get();

        $folders = $em->getRepository('Entity\Folder')
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

    public function toggleFolderExpanding($data)
    {
        if (!isset($data['id'])
            || !is_int(intval($data['id']))
            || !isset($data['expanded'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $id = intval($data['id']);
        $userId = intval($this->_user->userInfo['id']);
        $expanded = ($data['expanded'] === 'true') ? true : false;

        $em = EM::get();

        $folders = $em->find('Entity\Folder', $id);

        if ($folders === null) {
            throw new NotFoundException("requested filder not found. Folder id: ". $id);
        }

        $folders->setExpanded(intval($expanded));
        $em->flush();

        return json_encode([
            'id' => $id,
            'expanded' => $expanded
        ]);
    }

    public function deleteFolder($data)
    {
        if (!isset($data['id'])
            || !is_int(intval($data['id']))
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $id = $data['id'];
        $userId = intval($this->_user->userInfo['id']);
        $userInfo = $this->_user->userInfo;

        $Fd = new Folder;
        $availableFolders = $Fd->GetAvailableFolders($userId);
        $result = array();

        if (!in_array($id, $availableFolders)) {
            throw new ForbiddenException('requested folder not avaliable for current user. Folder id: '. $flightId);
        }

        $Fd = new Folder;
        $subitems = $Fd->getSubitems($id, $userId);

        foreach ($subitems as $item) {
            $id = intval($item['id']);
            if ($item['type'] === 'folder') {
                 $Fd->DeleteFolder($id, $userId);
            } else if ($item['type'] === 'flight') {
                if (User::isAdmin($userInfo['role'])
                    || User::isModerator($userInfo['role'])
                ) {
                    $FC = new FlightComponent;
                    $result = $FC->DeleteFlight($id, $userId);
                    unset($FC);
                }
            }
        }
        unset($Fd);

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
