<?php

namespace Controller;

use Model\User;
use Model\Folder;
use Model\Flight;

use Entity\Folder as FolderEntity;

use Component\EntityManagerComponent as EM;
use Component\FlightComponent;

class FolderController extends CController
{
    public $curPage = 'folderPage';

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
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ = "Not all nessesary params sent. Post: ".
                    json_encode($data) . ". Page FolderController";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ);
            echo(json_encode($answ));
            exit;
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

       $this->RegisterActionExecution($this->action, "executed", 0, 'folderCreation', $fullpath, $folderName);

       echo json_encode(array_merge(
           $folder->get(),
           [
               'type' => 'folder',
               'parentId' => intval($folder->getPath())
           ]
       ));

       exit;
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

        echo json_encode($items);
    }

    public function toggleFolderExpanding($data)
    {
        if (!isset($data['id'])
            || !is_int(intval($data['id']))
            || !isset($data['expanded'])
        ) {
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FolderController";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit;
        }

        $id = intval($data['id']);
        $userId = intval($this->_user->userInfo['id']);
        $expanded = ($data['expanded'] === 'true') ? true : false;

        $em = EM::get();

        $folders = $em->find('Entity\Folder', $id);

        if ($folders === null) {
            http_response_code(404);
            header("Status: 404 Not Found");
            $msg = "Requested folder not found. Id: ". $id;
            $this->RegisterActionReject($this->action, "rejected", 0, $msg);
            echo(json_encode($msg));
            exit;
        }

        $folders->setExpanded(intval($expanded));

        $em->flush();

        echo(json_encode([
            'id' => $id,
            'expanded' => $expanded
        ]));
        exit;
    }

    public function deleteFolder($data)
    {
        if (!isset($data['id'])
            || !is_int(intval($data['id']))
        ) {
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FolderController";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit;
        }

        $id = $data['id'];
        $userId = intval($this->_user->userInfo['id']);
        $userInfo = $this->_user->userInfo;

        $Fd = new Folder;
        $availableFolders = $Fd->GetAvailableFolders($userId);
        $result = array();

        if (!in_array($id, $availableFolders)) {
            unset($Fd);
            http_response_code(403);
            header("Status: 403 Forbidden");
            $dat = "Not available for current user. DeleteFolder id - " . $id . ". " .
               "Username - " . $this->_user->username . ". Page FlightsController.php";
            $result['data'] = $dat;
            echo(json_encode($answ));
            exit;
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

        echo(json_encode('ok'));
        exit;
    }

    public function ChangeFolderPath($data)
    {
        if (!isset($data['id'])
            || !isset($data['parentId'])
            || !is_int(intval($data['id']))
            || !is_int(intval($data['parentId']))
        ) {
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent or incorrect param types. Post: ".
                    json_encode($_POST) . ". Page FolderController";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit;
        }

        $userId = intval($this->_user->userInfo['id']);
        $sender = intval($data['id']);
        $target = intval($data['parentId']);

        $Fd = new Folder;
        $result = $Fd->ChangeFolderPath($sender, $target, $userId);
        unset($Fd);

        echo (json_encode([
            'id' => $sender,
            'parentId' => $target
        ]));
        exit;
    }

   public function renameFolder($data)
   {
       if(!isset($data['id'])
           || !isset($data['name'])
       ) {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page FolderController";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
           exit;
       }

       $folderId = $data['id'];
       $folderName = $data['name'];

       $userId = intval($this->_user->userInfo['id']);

       $Fd = new Folder;
       $Fd->RenameFolder($folderId, $folderName, $userId);
       unset($Fd);

       $this->RegisterActionExecution($this->action, "executed", $folderId, 'folderId', $folderName, "newName");

       echo json_encode('ok');
   }
}
