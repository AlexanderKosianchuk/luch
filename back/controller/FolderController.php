<?php

namespace Controller;

use Model\User;
use Model\Folder;
use Model\Flight;

use Entity\Folder as FolderEntity;

use Component\EntityManagerComponent as EM;

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
            header("Status: 400 Bad Request");
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page flights.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
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
            'userId' => $userId
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
                   'parentId' => intval($folder->getPath())
               ]
           );
       }

       echo json_encode($items);
   }

   public function ChangeFlightPath($sender, $target)
   {
      $userId = intval($this->_user->userInfo['id']);

      $Fd = new Folder;
      $result = $Fd->ChangeFlightFolder($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function ChangeFolderPath($sender, $target)
   {
      $userId = intval($this->_user->userInfo['id']);

      $Fd = new Folder;
      $result = $Fd->ChangeFolderPath($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function RenameFolder($extFolderId, $extFolderName)
   {
      $folderId = $extFolderId;
      $folderName = $extFolderName;

      $userId = $this->_user->GetUserIdByName($this->_user->username);

      $Fd = new Folder;
      $result = $Fd->RenameFolder($folderId, $folderName, $userId);
      unset($Fd);

      return $result;
   }

   public function DeleteFolderWithAllChildren($extId)
   {
      if(is_int($extId))
      {
         $id = $extId;

         $userId = intval($this->_user->GetUserIdByName($this->_user->username));

         $Fd = new Folder;
         $availableFolders = $Fd->GetAvailableFolders($userId);
         $result = array();

         if(in_array($id, $availableFolders))
         {
            $nodeTree = $this->PrepareTree(0); // here PrepareTree argument is not important
            $children = $nodeTree[0]['children'];
            $matches = array(
               0 => $id
            );
            $this->recursiveCollectChildren($children, $id, $matches);

            $Fl = new Flight;
            foreach ($matches as $id)
            {
               $id = intval($id);
               $flightInfo = $Fl->GetFlightInfo($id);
               if(!empty($flightInfo))
               {
                  $this->DeleteFlight($id);
               }

               if(in_array($id, $availableFolders))
               {
                  $folderInfo = $Fd->GetFolderInfo($id);
                  if(!empty($folderInfo))
                  {
                     $result[] = $Fd->DeleteFolder($id, $userId);
                  }
               }
            }
            unset($Fd);
            $result['status'] = true;
            return $result;
         }
         else
         {
            unset($Fd);
            $dat = "Not available for current user. DeleteFolder id - " . $id . ". " .
               "Username - " . $this->_user->username . ". Page FlightsController.php";
            error_log($dat);
            $result['status'] = false;
            $result['data'] = $dat;
            return $result;
         }
      }
      else
      {
         error_log("Incorrect input data. DeleteFolder id - " . json_encode($extId) . ". Page FlightsController.php");
         $result['status'] = false;
         return $result;
      }
   }

   private function recursiveCollectChildren($branch, $parentId, &$childIds)
   {
      foreach ($branch as $childBranch)
      {
         if($childBranch['parent'] == $parentId)
         {
            $childIds[] = $childBranch['id'];
            if(!empty($childBranch['children']))
            {
               $searchedNewParentId = $childBranch['id'];
               $searchedNewBranch = $childBranch['children'];
               $this->recursiveCollectChildren($searchedNewBranch, $searchedNewParentId, $childIds);
            }
         }
         else
         {
            if(!empty($childBranch['children']))
            {
               $searchedNewBranch = $childBranch['children'];
               $this->recursiveCollectChildren($searchedNewBranch, $parentId, $childIds);
            }
         }
      }
   }

   public function folderChangePath($data)
   {
       if(isset($data['sender'])
           && isset($data['target'])
       ) {
           $sender = $data['sender'];
           $target = $data['target'];

           $result = $this->ChangeFolderPath($sender, $target);
           $this->RegisterActionExecution($this->action, "executed", $sender, 'folderId', $target, "newPath");

           $answ = array();
           if($result) {
               $answ['status'] = 'ok';
           } else {
               $answ['status'] = 'err';
               $answ['error'] = 'Error during folder change path.';
               $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           }
           echo json_encode($answ);
       } else {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function folderRename($data)
   {
       if(isset($data['folderId'])
           && isset($data['folderName'])
       ) {
           $folderId = $data['folderId'];
           $folderName = $data['folderName'];

           $result = $this->RenameFolder($folderId, $folderName);
           $this->RegisterActionExecution($this->action, "executed", $folderId, 'folderId', $folderName, "newName");

           $answ = array();
           if($result) {
               $answ['status'] = 'ok';
           } else {
               $answ['status'] = 'err';
               $answ['error'] = 'Error during folder rename.';
               $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           }
           echo json_encode($answ);
       } else {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function itemDelete($data)
   {
       if(isset($data['type'])
           && isset($data['id'])
       ) {
           $type = $data['type'];
           $id = intval($data['id']);

           if($type == 'folder') {
               $result = $this->DeleteFolderWithAllChildren($id);

               $answ = array();
               if ($result)
               {
                   $answ['status'] = 'ok';
                   $this->RegisterActionExecution($this->action, "executed", $id, "itemId", $type, 'typeDeletedItem');
               } else {
                   $answ['status'] = 'err';
                   $answ['data']['error'] = 'Error during folder deleting.';
                   $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
               }
               echo json_encode($answ);
           } else if($type == 'flight') {
               $result = $this->DeleteFlight($id);

               $answ = array();
               if($result) {
                   $answ['status'] = 'ok';
                   $this->RegisterActionExecution($this->action, "executed", $id, "itemId", $type, 'typeDeletedItem');
               } else {
                   $answ['status'] = 'err';
                   $answ['data']['error'] = 'Error during flight deleting.';
                   $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
               }
               echo json_encode($answ);
           } else {
               $answ["status"] = "err";
               $answ["error"] = "Incorect type. Post: ".
                       json_encode($_POST) . ". Page flights.php";
               echo(json_encode($answ));
           }
       } else {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }
}
