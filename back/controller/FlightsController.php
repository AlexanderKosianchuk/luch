<?php

namespace Controller;

use Model\User;
use Model\Fdr;
use Model\Calibration;
use Model\Folder;
use Model\Flight;
use Model\FlightComments;
use Model\FlightException;
use Model\DataBaseConnector;

use Component\FlightComponent;
use Component\EventProcessingComponent;
use Component\RuntimeManager;

use Evenement\EventEmitter;

use Exception;
use ZipArchive;

class FlightsController extends CController
{
   public $curPage = 'flightsPage';

   function __construct()
   {
       $this->IsAppLoggedIn();
       $this->setAttributes();

       $get = $_GET;
       if(isset($get['action']) && ($get['action'] != '')) {
           $this->getAction = $get['action'];
       }
   }

   public function PutTopMenu()
   {
      $this->_user->username = $this->_user->username . "";
      $this->_user->usernameLen = strlen($this->_user->username);
      $styleFontSize = 24 - $this->_user->usernameLen / 2.2;
      $styleWidth = 20 + $this->_user->usernameLen * $styleFontSize / 2;
      $styleTop = 8 + $this->_user->usernameLen / 3;

      $topMenu = sprintf("<div id='topMenuFlightList' class='TopMenu'>

            <label id='logo' class='Logo' style='background-image:url(/front/stylesheets/basicImg/logo.png)'>
               <span style='position:absolute; margin-top:8px;'>Luch</span>
            </label>

            <img class='Separator'></img>

            <label id='currentUploadingTopButt' class='CurrentUploadingTopButt' style='background-image:url(/front/stylesheets/basicImg/add.png)'>
            </label>

            <label id='uploadTopButt' class='UploadButt'>
               <span style='position:absolute; margin-top:8px;'>%s</span>
            </label>

            <label id='userTopButt' class='UserButt' style='background-image:url(/front/stylesheets/basicImg/userPreferences.png); " .
            "width:%spx; font-size:%spx;'
               data-username='%s'>
               <span style='position:absolute; margin-top:%spx;'>%s</span>
            </label>

            <div id='view' style='display:none;'><img class='Separator2'></img>
               <label class='ViewItem' style='background-image:url(/front/stylesheets/basicImg/view.png);'>
               <span style='position:absolute; margin-top:8px;'>%s</span>
            </label></div>

            </div>", $this->lang->upload,
            $styleWidth,
            $styleFontSize,
            $this->_user->username,
            $styleTop,
            $this->_user->username,
            $this->lang->viewItem);

      return $topMenu;
   }

   public function PutLeftMenu()
   {
       $leftMenu = sprintf("<div id='leftMenuFlightList' class='LeftMenu'>");
       $leftMenu .= sprintf("<input class='SearchBox' value='' size='24' style='visibility: hidden;'></input>");

     $leftMenu .= sprintf("<div id='flightLeftMenuRow' class='LeftMenuRow LeftMenuRowSelected' data-selected='true'>
           <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/flight.png'></img>
           %s&nbsp;
           </div>", $this->lang->flightsItem);

     $leftMenu .= sprintf("<div id='searchLeftMenuRow' class='LeftMenuRow'>
           <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/search.png'></img>
           %s&nbsp;
           </div>", $this->lang->searchItem);

      $leftMenu .= sprintf("<div id='resultsLeftMenuRow' class='LeftMenuRow'>
          <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/gear.png'></img>
          %s&nbsp;
          </div>", $this->lang->resultsItem);

      /*$leftMenu .= sprintf("<div id='fdrLeftMenuRow' class='LeftMenuRow'>
         <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/fdr.png'></img>
         %s&nbsp;
         </div>", $this->lang->bruTypesItem);*/

      $leftMenu .= sprintf("<div id='calibrationLeftMenuRow' class='LeftMenuRow'>
        <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/compass.png'></img>
        %s&nbsp;
        </div>", $this->lang->calibrationItem);

      $role = $this->_user->userInfo['role'];
      if(User::isAdmin($role) || User::isModerator($role)) {
         $leftMenu .= sprintf("<div id='usersLeftMenuRow' class='LeftMenuRow'>
               <img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/user.png'></img>
               %s&nbsp;
               </div>", $this->lang->usersItem);
      }

      $leftMenu .= sprintf("</div>");

      return $leftMenu;
   }


    public function FileUploadBlock()
    {
        $userId = intval($this->_user->userInfo['id']);
        $avalibleFdrIds = $this->_user->getAvailableFdrs($userId);

        $fdr = new Fdr;
        $fdrList = $fdr->getFdrList($avalibleFdrIds);
        unset($fdr);

        $optionString = "";
        $firstFdrId = null;
        $first = true;
        foreach($fdrList as $fdrInfo) {
            if ($first) {
                $firstFdrId = intval($fdrInfo['id']);
                $first = false;
            }
            $optionString .="<option data-id='".$fdrInfo['id']."' value='".$fdrInfo['id']."'>"
                .$fdrInfo['name']
            ."</option>";
        }

        $calibration = new Calibration;
        $fdrCalibrations = $calibration->getCalibrationsForFdrs($avalibleFdrIds, $userId);

        $calibrationSelects = "";
        foreach ($fdrCalibrations as $fdrId => $calibrations) {
            $style = '';
            if ($firstFdrId === $fdrId) {
                $style = 'style="display:block;"';
            }

            $calibrationSelects .= "<select class='fdr-calibration' ".$style." data-fdr-id='".$fdrId."'>";
            foreach ($calibrations as $item) {
                $calibrationSelects .= "<option value='".$item['id']."'>".$item['name']."</option>";
            }
            $calibrationSelects .= "</select>";
        }

        $fileUploadBlock = sprintf("<div id='fileUploadDialog' class='OptionBlock' title='%s'><br>", $this->lang->flightUpload);

        $fileUploadBlock .= sprintf("<div id='importConvertRadio'>
            <input type='radio' id='convertFl' name='radio' checked='checked'><label for='convertFl'>%s</label>
            <input type='radio' id='importFl' name='radio'><label for='importFl'>%s</label>
         </div>", $this->lang->fileConvert, $this->lang->fileImport);
        $fileUploadBlock .= "<br>";

        $fileUploadBlock .= sprintf("<div id='previewCheckBoxDiv' class='FlightUploadingInputs'>
            <label>
                <input checked='checked' id='previewCheckBox' type='checkbox'>
                %s
            </label>
        </div>", $this->lang->filePreview);

        $fileUploadBlock .= sprintf("<div id='bruTypeSelectForUploadingDiv'>
            <select id='bruTypeSelectForUploading' name='bruType' class='FlightUploadingInputs'>%s</select>
         </div>", $optionString);

        $fileUploadBlock .= "<div class='calibrations-for-ubloading'>";
        $fileUploadBlock .= $calibrationSelects;
        $fileUploadBlock .= "</div>";

        $fileUploadBlock .= sprintf("<div id='progress' class='progress'>
               <div class='progress-bar progress-bar-success'></div>
            </div>
         <div id='files' class='files'></div>");
        $fileUploadBlock .= "<br>";

        $fileUploadBlock .= sprintf("<span class='btn btn-success fileinput-button choose-file-btn'>
             <input id='chooseFileBut' type='file' name='files[]' multiple>
         </span>");

        $fileUploadBlock .= sprintf("</div>");

        return $fileUploadBlock;
    }

   private function GetFlightsByPath($folderId)
   {
      $userId = $this->_user->GetUserIdByName($this->_user->username);

      $Fl = new Flight;
      $Fd = new Folder;
      $flightIdsArr = $Fd->GetFlightsByFolder($folderId, $userId);

      $flightsInfoArr = [];
      foreach ($flightIdsArr as $id) {
         $flightsInfoArr[] = $Fl->GetFlightInfo($id);
      }

      unset($Fd);
      unset($Fl);

      return $flightsInfoArr;
   }

   private function GetFoldersByPath($folderId)
   {
      $userId = $this->_user->userInfo['id'];
      $subFoldersArr = [];
      $Fd = new Folder;

      $subFoldersArr = $Fd->GetSubfoldersByFolder($folderId, $userId);

      unset($Fd);

      return $subFoldersArr;
   }

   public function CreateNewFolder($extName, $extPath)
   {
      $name = $extName;
      $path = $extPath;

      $userId = $this->_user->GetUserIdByName($this->_user->username);

      $Fd = new Folder;
      $result = $Fd->CreateFolder($name, $path, $userId);
      unset($Fd);

      return $result;
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

   public function DeleteFlight($flightId)
   {
       $FC = new FlightComponent;
       $result = $FC->DeleteFlight($flightId, intval($this->_user->userInfo['id']));
       unset($FC);

       return $result;
   }

    public function ProcessFlight($data)
    {
        $flightId = intval($data['id']);

        if (is_int($flightId)) {
            $Fl = new Flight;
            $flightInfo = $Fl->GetFlightInfo($flightId);
            $apTableName = $flightInfo["apTableName"];
            $bpTableName = $flightInfo["bpTableName"];
            $excEventsTableName = $flightInfo["exTableName"];
            $startCopyTime = $flightInfo["startCopyTime"];
            $tableGuid = substr($apTableName, 0, 14);
            unset($Fl);

            $fdr = new Fdr;
            $fdrInfo = $fdr->getFdrInfo(intval($flightInfo["id_fdr"]));
            $excListTableName = $fdrInfo["excListTableName"];
            $apGradiTableName = $fdrInfo["gradiApTableName"];
            $bpGradiTableName = $fdrInfo["gradiBpTableName"];
            $stepLength = $fdrInfo["stepLength"];

            if ($excListTableName != "") {
               $excListTableName = $fdrInfo["excListTableName"];
               $apGradiTableName = $fdrInfo["gradiApTableName"];
               $bpGradiTableName = $fdrInfo["gradiBpTableName"];

               $FEx = new FlightException;
               $FEx->DropFlightExceptionTable($excEventsTableName);
               $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
               //Get exc refParam list
               $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

               $exList = $FEx->GetFlightExceptionTable($excListTableName);

               //file can be accesed by ajax what can cause warning
               error_reporting(E_ALL ^ E_WARNING);
               set_time_limit (0);

               //perform proc be cached table
               for($i = 0; $i < count($exList); $i++)
               {
                  $curExList = $exList[$i];
                  $FEx->PerformProcessingByExceptions($curExList,
                        $flightInfo, $flightExTableName,
                        $apTableName, $bpTableName,
                        $startCopyTime, $stepLength);
               }

               EventProcessingComponent::processEvents($flightId, new EventEmitter);

               error_reporting(E_ALL);
            }

            unset($fdr);
            echo json_encode(['status' => 'ok']);
      } else {
         $msg = "Incorrect input data. ProcessFlight id - " . json_encode($flightId) . ". Page FlightsController.php";
         throw new Exception($msg, 1);
      }
   }

   public function GetLastViewType()
   {
      $viewTypes = [
         "flightListTree",
         "flightListTable"
     ];

      $userId = $this->_user->userInfo['id'];
      $lastView = $this->_user->GetLastActionFromRange($userId, $viewTypes);

      return $lastView;
   }

   public function GetLastViewedFolder()
   {
      $viewType = "showFolderContent";

      $userId = $this->_user->userInfo['id'];
      $actionsInfo = $this->_user->GetLastAction($userId, $viewType);

      return $actionsInfo;
   }

   public function BuildFlightsInTree($extFolder)
   {
      $shownFolderId = $extFolder;

      $flightColumn = "";

      $userId = $this->_user->userInfo['id'];

      $Fd = new Folder;
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);

      $flightColumn .= "<div class='FlightsListTileView'>" .
            "<div id='jstree' class='Tree'></div>".
            "<div id='jstreeContent' class='TreeContent'></div>".
            "</div>";

      return $flightColumn;
   }

   public function PrepareTree($shownFolderId)
   {
      $userId = $this->_user->userInfo['id'];

      $Fd = new Folder;
      $content = $Fd->GetAvailableContent($shownFolderId, $userId);
      unset($Fd);

      $relatedNodes = false;
      if(count($content) > 0) {
         $relatedNodes = $this->makeRecursive($content);
      }

      return $relatedNodes;
   }

   public function BuildSelectedFolderContent($shownFolderId)
   {
      $flightColumn = "";

      $Fd = new Folder;
      $flightsInPath = $this->GetFlightsByPath($shownFolderId);
      $subFolders = (array)$this->GetFoldersByPath($shownFolderId);
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);

      foreach($subFolders as $key => $val)
      {
         $input = '<input class="ItemsCheck" type="checkbox" data-type="folder" data-folderpath="'.$shownFolderId.'" data-folderdestination="'.$val['id'].'">';
         $flightColumn .= "<div class='JstreeContentItemFolder'><label>" . $input . " " . $val['name']."</label></div>";
      }

      $Fc = new FlightComments;
      foreach($flightsInPath as $key => $val)
      {
         $flightComment = $Fc->getComment(intval($val['id']));
         $name = $val['bort'] . ", " .  $val['voyage']  . ", " . date('d/m/y H:i', $val['startCopyTime'])  .
         ", " . $val['bruType']  . ", " . $val['departureAirport']  . "-" . $val['arrivalAirport'] ;

         $isAnalyzed = 'is-analyzed';
         if (!isset($flightComment['id'])) {
             $isAnalyzed = '';
         }
         $input = '<input class="ItemsCheck" type="checkbox" data-type="flight" data-folderpath="'.$shownFolderId.'" data-flightid="'.$val['id'].'">';
         $flightColumn .= "<div class='JstreeContentItemFlight ".$isAnalyzed."'><label>" . $input . " " . $name . "</label></div>";
      }

      if((count($flightsInPath) == 0) && (count($subFolders) == 0))
      {
         $flightColumn = "<div>" . $this->lang->noContent . "</div>";
      }

      $result = array(
         'folderName' => $shownFolder,
         'content' => $flightColumn
      );

      return $result;
   }

   private function makeRecursive($d, $r = 0, $pk = 'parent', $k = 'id', $c = 'children') {
      $m = array();
      foreach ($d as $e) {
         isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
         isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
         $m[$e[$pk]][] = array_merge($e, array($c => &$m[$e[$k]]));
      }

      return $m[$r];//[0]; // remove [0] if there could be more than one root nodes
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

   public function BuildTable()
   {
      $table = sprintf("<table id='flightTable' cellpadding='0' cellspacing='0' border='0'>
            <thead><tr>");

      $table .= sprintf("<th name='checkbox' style='width:%s;'>%s</th>", "1%", "<input id='tableCheckAllItems' type='checkbox'/>");
      $table .= sprintf("<th name='bort'>%s</th>", $this->lang->bort);
      $table .= sprintf("<th name='voyage'>%s</th>", $this->lang->voyage);
      $table .= sprintf("<th name='flightTime'>%s</th>", $this->lang->flightTime);
      $table .= sprintf("<th name='uploadTime'>%s</th>", $this->lang->uploadTime);
      $table .= sprintf("<th name='bruType'>%s</th>", $this->lang->bruType);
      $table .= sprintf("<th name='departureAirport'>%s</th>", $this->lang->departureAirport);
      $table .= sprintf("<th name='arrivalAirport'>%s</th>", $this->lang->arrivalAirport);
      $table .= sprintf("<th name='performer'>%s</th>", $this->lang->performer);
      $table .= sprintf("<th name='status'>%s</th>", $this->lang->status);

      $table .= sprintf("</tr></thead><tfoot style='display: none;'><tr>");

      for($i = 0; $i < 10; $i++)
      {
         $table .= sprintf("<th></th>");
      }

      $table .= sprintf("</tr></tfoot><tbody></tbody></table>");
      return $table;
   }

   public function BuildTableSegment($orderColumn, $orderType)
   {
      $userId = intval($this->_user->userInfo['id']);

      $Fd = new Folder;
      $flightsInFolders = $Fd->GetAllFlightsInFolders($userId);
      unset($Fd);

      $tableSegment = array();

      $Fl = new Flight;
      foreach($flightsInFolders as $flightInFolder)
      {
          $flight = $Fl->GetFlightInfo(intval($flightInFolder['flightId']));
          $execution = "-";
          if($flight['exTableName'] != '') {
            $execution = "+";
          }

          $tableSegment[] = array(
            "<input class='ItemsCheck' data-type='flight' data-flightid='".$flight['id']."' type='checkbox'/>",
            $flight['bort'],
            $flight['voyage'],
            date('d/m/y H:i', $flight['startCopyTime']),
            date('d/m/y H:i', $flight['uploadingCopyTime']),
            $flight['bruType'],
            $flight['departureAirport'],
            $flight['arrivalAirport'],
            $flight['performer'],
            $execution
         );
      }

      unset($Fl);

      return $tableSegment;
   }

   public function ExportFlightsAndFolders($flightIds, $folderDest)
   {
      $Fd = new Folder;

      $userId = intval($this->_user->userInfo['id']);
      $allFolders = [];

      foreach ($folderDest as $dest) {
         $allFolders = $Fd->SubfoldersDeepScan($dest, $userId, $adminRole);
      }

      foreach ($allFolders as $folderId) {
         $flightIds = array_merge($flightIds,
               $Fd->GetFlightsByFolder($folderId, $userId, $adminRole));
      }
      unset($Fd);

      $exportedFiles = [];

      $Fl = new Flight;
      $C = new DataBaseConnector;
      $Fdr = new Fdr;

      foreach ($flightIds as $flightId) {
         $flightInfo = $Fl->GetFlightInfo($flightId);

         $fileGuid = uniqid();
         $exportedFileName = $flightInfo['bort']
            . "_" . date("Y-m-d", $flightInfo['startCopyTime'])
            . "_" . $flightInfo['voyage']
            . "_" . $fileGuid;

         $exportedFileDir = RuntimeManager::getExportFolder();
         $exportedFilePath = RuntimeManager::createExportedFile($exportedFileName);

         $headerFile = [];
         $headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage'].$fileGuid.".json";
         $headerFile['root'] = $exportedFileDir.DIRECTORY_SEPARATOR.$headerFile['filename'];
         $exportedFiles[] = $headerFile;

         $apPrefixes = $Fdr->GetBruApCycloPrefixes(intval($flightInfo["id_fdr"]));

         for ($i = 0; $i < count($apPrefixes); $i++) {
            $exportedTable = $C->ExportTable(
                $flightInfo["apTableName"]."_".$apPrefixes[$i],
                $flightInfo["apTableName"]."_".$apPrefixes[$i] . "_" . $fileGuid,
                $exportedFileDir
            );

            $exportedFiles[] = $exportedTable;

            $flightInfo["apTables"][] = array(
                  "pref" => $apPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $bpPrefixes = $Fdr->GetBruBpCycloPrefixes(intval($flightInfo["id_fdr"]));

         for ($i = 0; $i < count($bpPrefixes); $i++) {
            $exportedTable = $C->ExportTable(
                $flightInfo["bpTableName"]."_".$apPrefixes[$i],
                $flightInfo["bpTableName"]."_".$bpPrefixes[$i] . "_" . $fileGuid,
                $exportedFileDir
            );

            $exportedFiles[] = $exportedTable;

            $flightInfo["bpTables"][] = array(
                  "pref" => $bpPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $eventTables = [
            ['table' => $flightInfo["exTableName"], 'label' => "exTables"],
            ['table' => $flightInfo["guid"].'_events', 'label' => "eventsTable"],
            ['table' => $flightInfo["guid"].'_settlements', 'label' => "settlementsTable"],
         ];

         foreach ($eventTables as $item) {
             $this->exportEventTable(
                 $flightInfo,
                 $item['table'],
                 $item['label'],
                 $fileGuid,
                 $exportedFiles,
                 $exportedFileDir
             );
         }

         $exportedFileDesc = fopen($headerFile['root'], "w");
         fwrite ($exportedFileDesc , json_encode($flightInfo));
         fclose($exportedFileDesc);
      }

      unset($Fl);
      unset($C);
      unset($Fdr);

      $zip = new ZipArchive;
      if ($zip->open($exportedFilePath, ZipArchive::CREATE) === TRUE) {
         for($i = 0; $i < count($exportedFiles); $i++) {
            $zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
         }
         $zip->close();
      } else {
         error_log('Failed zipping flight.');
      }

      for ($i = 0; $i < count($exportedFiles); $i++) {
         if(file_exists($exportedFiles[$i]['root'])) {
            unlink($exportedFiles[$i]['root']);
         }
      }

      error_reporting(E_ALL);
      return RuntimeManager::getExportedUrl($exportedFileName);
   }

   private function exportEventTable(
       &$flightInfo,
       $tableName,
       $flightInfolabel,
       $fileGuid,
       &$exportedFiles,
       $exportedFileDir
   ) {
       $C = new DataBaseConnector;

       if ($C->checkTableExist($tableName)) {
           $exportedTable = $C->ExportTable(
             $tableName,
             $tableName . "_" . $fileGuid,
             $exportedFileDir
           );
           $exportedFiles[] = $exportedTable;

           $flightInfo[$flightInfolabel] = $exportedTable["filename"];

           return $tableName . "_" . $fileGuid;
       }

       unset($C);

       return false;
   }

   public function GetLastSortTableType()
   {
      $viewType = "segmentTable";

      $userId = $this->_user->userInfo['id'];
      $actionsInfo = $this->_user->GetLastAction($userId, $viewType);

      return $actionsInfo;
   }

   public function GetResults()
   {
       $c = new DataBaseConnector;
       $link = $this->Connect();
       $list = [];

       $query = "SELECT * FROM `results` WHERE 1;";
       $result = $link->query($query);

       $firstRow = true;

       if(!$result) {
           $this->Disconnect();
           unset($c);
           return $list;
       }

       while($row = $result->fetch_array())
       {
           if ($firstRow) {
               $firstRow = false;

               $plainRow = [];
               foreach ($row as $key => $val) {
                   if (gettype($key) === 'string') {
                       $plainRow[] = $key;
                   }
               }
               array_push($list, $plainRow);

               $plainRow = [];
               foreach ($row as $key => $val) {
                   if (gettype($key) !== 'string') {
                       $plainRow[] = $val;
                   }
               }

               array_push($list, $plainRow);
           } else {
               $plainRow = [];
               foreach ($row as $key => $val) {
                   if (gettype($key) !== 'string') {
                       $plainRow[] = $val;
                   }
               }

               array_push($list, $plainRow);
           }
       }

       $result->free();
       $this->Disconnect();

       unset($c);

       return $list;
   }

   public function GetEvents()
   {
       $list = [];
       $userId = intval($this->_user->userInfo['id']);
       $Fd = new Folder;
       $flightsInFolders = $Fd->GetAllFlightsInFolders($userId);
       unset($Fd);

       $firstRow = true;
       $excTables = [];
       $FEx = new FlightException;
       foreach($flightsInFolders as $flightInFolder)
       {
           $flight = $Fl->GetFlightInfo(intval($flightInFolder['flightId']));
           $excTable = $flight['exTableName'];
           $rows = $FEx->GetFlightEventsList($excTable);

           foreach ($rows as $row) {
               $falseAlarm = $row['falseAlarm'];

               if($falseAlarm) {
                   continue;
               }

               $row = array(
                   "code" => $row['code'],
                   "startTime" => date('Y-m-d H:i:s', ($row['startTime'] / 1000)),
                   "endTime" => date('Y-m-d H:i:s', ($row['endTime'] / 1000)),
                   "excAditionalInfo" => $row['excAditionalInfo'],
                   "userComment" => $row['userComment']
               );

               if ($firstRow) {
                   $firstRow = false;

                   $plainRow = [];
                   foreach ($row as $key => $val) {
                       if (gettype($key) === 'string') {
                           $plainRow[] = $key;
                       }
                   }
                   array_push($list, $plainRow);
               }

               $plainRow = [];
               foreach ($row as $key => $val) {
                   if (isset($val)) {
                       $val = str_replace([PHP_EOL, ',', ';'], ' ', $val);

                       $plainRow[] = $val;
                   } else {
                       $plainRow[] = '';
                   }
               }

               array_push($list, $plainRow);
           }
       }
       unset($FEx);

       return $list;
   }

   public function GetCoordinates($flightId)
   {
       if(!is_int(intval($flightId))) {
           throw new Exception("Incorrect flightId passed into GetCoordinates FlightsController." . $flightId, 1);
       }

       $Fl = new Flight;
       $flight = $Fl->GetFlightInfo($flightId);
       $fdrId = intval($flightInfo['id_fdr']);
       unset($Fl);

       $apTableName = $flight['apTableName'];
       $bpTableName = $flight['bpTableName'];

       $fdr = new Fdr;
       $fdrInfo = $fdr->getFdrInfo($fdrId);
       unset($fdr);

       $kmlScript = $fdrInfo['kml_export_script'];
       $kmlScript = str_replace("[ap]", $apTableName, $kmlScript);
       $kmlScript = str_replace("[bp]", $bpTableName, $kmlScript);

       $c = new DataBaseConnector;
       $link = $c->Connect();

       $info = [];
       $averageLat = 0;
       $averageLong = 0;

       if (!$link->multi_query($kmlScript)) {
           //err log
           error_log("Impossible to execute multiquery: (" .
               $kmlScript . ") " . $link->error);
       }

       do
       {
           if ($res = $link->store_result())  {
               while($row = $res->fetch_array()) {
                   $lat = $row['LAT'];
                   $long = $row['LONG'];
                   $h = $row['H'];

                   $averageLat += $lat;
                   $averageLong += $long;
                   $averageLat /= 2;
                   $averageLong /= 2;

                   if ($h < 0) {
                       $h = 10.00;
                   }
                   $h = round($h, 2);
                   $info[] = [
                       $long,
                       $lat,
                       $h,
                   ];
               }

               $res->free();
           }
       } while ($link->more_results() && $link->next_result());

       $c->Disconnect();

       unset($c);

       return $info;
   }


   /*
   * ==========================================
   * REAL ACTIONS
   * ==========================================
   */
   public function flightGeneralElements($data)
   {
       $topMenu = $this->PutTopMenu();
       $leftMenu = $this->PutLeftMenu();
       $fileUploadBlock = $this->FileUploadBlock();

       $answ = array(
               'status' => 'ok',
               'data' => array(
                   'topMenu' => $topMenu,
                   'leftMenu' => $leftMenu,
                   'fileUploadBlock' => $fileUploadBlock
               )
       );

       echo json_encode($answ);
   }

   public function getLastView($data)
   {
       $lastViewType = $this->GetLastViewType();
       $answ = array();

       if ($lastViewType == null) {
               $targetId = 0;
               $targetName = 'root';
               $viewAction = "flightListTree";
               $flightsListTileView = $this->BuildFlightsInTree($targetId);
               $this->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);

               $answ["status"] = "ok";
               $answ["type"] = $viewAction;
               $answ["lastViewedFolder"] = $targetId;
               $answ["data"] = $flightsListTileView;
       } else {
           $flightsListByPath = "";
           $viewAction = $lastViewType["action"];
           if($viewAction === "flightListTree") {
               $actionsInfo = $this->GetLastViewedFolder();
               $targetId = 0;
               if($actionsInfo == null) {
                   $targetName = 'root';
                   $flightsListTileView = $this->BuildFlightsInTree($targetId);
                   $this->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
               } else {
                   $targetId = $actionsInfo['targetId'];
                   $targetName = $actionsInfo['targetName'];

                   $Fd = new Folder();
                   $folderInfo = $Fd->GetFolderInfo($targetId);
                   unset($Fd);

                   if(empty($folderInfo))
                   {
                       $targetId = 0;
                       $targetName = 'root';
                   }

                   $flightsListTileView = $this->BuildFlightsInTree($targetId);
                   $this->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
               }

               $answ["status"] = "ok";
               $answ["type"] = $viewAction;
               $answ["lastViewedFolder"] = $targetId;
               $answ["data"] = $flightsListTileView;

           } else if($viewAction === "flightListTable") {
               $action = "flightListTable";

               $table = $this->BuildTable();
               $this->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');
               $actionsInfo = $this->GetLastSortTableType();

               if (empty($actionsInfo)) {
                   $actionsInfo['senderId'] = 3; // colunm 3 - start copy time
                   $actionsInfo['targetName'] = 'desc';
               }

               $answ["status"] = "ok";
               $answ["type"] = $viewAction;
               $answ["data"] = $table;
               $answ["sortCol"] = $actionsInfo['senderId'];
               $answ["sortType"] = $actionsInfo['targetName'];
           }
       }

       echo json_encode($answ);
   }

   public function flightListTree()
   {
       $flightsListTile = "";

       $actionsInfo = $this->GetLastViewedFolder();
       $targetId = 0;

       if ($actionsInfo == null) {
           $targetName = 'root';
           $flightsListTileView = $this->BuildFlightsInTree($targetId);
           $this->RegisterActionExecution($this->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
       } else {
           $targetId = $actionsInfo['targetId'];
           $targetName = $actionsInfo['targetName'];

           $Fd = new Folder();
           $folderInfo = $Fd->GetFolderInfo($targetId);
           unset($Fd);

           if (empty($folderInfo)) {
               $targetId = 0;
               $targetName = 'root';
           }

           $flightsListTileView = $this->BuildFlightsInTree($targetId);
           $this->RegisterActionExecution($this->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
       }

       $answ["status"] = "ok";
       $answ["lastViewedFolder"] = $targetId;
       $answ["data"] = $flightsListTileView;

       echo json_encode($answ);
   }

   public function receiveTree($data)
   {
       if(isset($data['data']))
       {
           $folderid = 0;
           $folderName = $this->lang->root;

           $relatedNodes = "";
           $actionsInfo = $this->GetLastViewedFolder();

           if($actionsInfo == null)
           {
               $targetId = $folderid;
               $targetName = 'root';
               $relatedNodes = $this->PrepareTree($targetId);
               $this->RegisterActionExecution($this->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
           }
           else
           {
               $targetId = $actionsInfo['targetId'];
               $targetName = $actionsInfo['targetName'];

               $Fd = new Folder();
               $folderInfo = $Fd->GetFolderInfo($targetId);
               unset($Fd);

               if(empty($folderInfo))
               {
                   $targetId = 0;
                   $targetName = 'root';
               }

               $relatedNodes = $this->PrepareTree($targetId);
               $this->RegisterActionExecution($this->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
           }

           $tree[] = array(
                   "id" => (string)$folderid,
                   "text" => $folderName,
                   'type' => 'folder',
                   'state' =>  array(
                           "opened" => true
                   ),
                   'children' => $relatedNodes
           );

           if(($actionsInfo == null) || ($actionsInfo['targetId'] == 0))
           {
               $tree[0]["state"] =  array(
                       "opened" => true,
                       "selected" => true
               );
           }

           echo json_encode($tree);
       }
       else
       {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function flightListTable($data)
   {
       if(isset($data['data']))
       {
           $table = $this->BuildTable();
           $this->RegisterActionExecution($this->action, "executed", 0, 'tableView', '', '');

           $actionsInfo = $this->GetLastSortTableType();

           if(empty($actionsInfo)){
               $actionsInfo['senderId'] = 3; // colunm 3 - start copy time
               $actionsInfo['targetName'] = 'desc';
           }

           $answ = array(
               'status' => 'ok',
               'data' => $table,
               'sortCol' => $actionsInfo['senderId'],
               'sortType' => $actionsInfo['targetName']
           );

           echo json_encode($answ);
       }
       else
       {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function segmentTable($data)
   {
       if(isset($data['data']))
       {
           $aoData = $this->data['data'];
           $sEcho = $aoData[sEcho]['value'];
           $iDisplayStart = $aoData[iDisplayStart]['value'];
           $iDisplayLength = $aoData[iDisplayLength]['value'];

           $sortValue = count($aoData) - 3;
           $sortColumnName = 'id';
           $sortColumnNum = $aoData[$sortValue]['value'];
           $sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);

           switch ($sortColumnNum){
               case(1):
               {
                   $sortColumnName = 'bort';
                   break;
               }
               case(2):
               {
                   $sortColumnName = 'voyage';
                   break;
               }
               case(3):
               {
                   $sortColumnName = 'startCopyTime';
                   break;
               }
               case(4):
               {
                   $sortColumnName = 'uploadingCopyTime';
                   break;
               }
               case(5):
               {
                   $sortColumnName = 'bruType';
                   break;
               }
               case(6):
               {
                   $sortColumnName = 'arrivalAirport';
                   break;
               }
               case(7):
               {
                   $sortColumnName = 'departureAirport';
                   break;
               }
               case(8):
               {
                   $sortColumnName = 'performer';
                   break;
               }
               case(9):
               {
                   $sortColumnName = 'exTableName';
                   break;
               }
           }

           $totalRecords = -1;
           $aaData["sEcho"] = $sEcho;
           $aaData["iTotalRecords"] = $totalRecords;
           $aaData["iTotalDisplayRecords"] = $totalRecords;

           $this->RegisterActionExecution($this->action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);

           $tableSegment = $this->BuildTableSegment($sortColumnName, $sortColumnType);
           $aaData["aaData"] = $tableSegment;

           echo(json_encode($aaData));
       }
       else
       {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function showFolderContent($data)
   {
       if(isset($data['folderId']))
       {
           $folderid = intval($this->data['folderId']);
           $result = $this->BuildSelectedFolderContent($folderid);

           $folderContent = $result['content'];
           $targetId = $folderid;
           $targetName = $result['folderName'];
           $this->RegisterActionExecution($this->action, "executed", 0, 'treeViewPath', $targetId, $targetName);

           $answ = array(
               'status' => 'ok',
               'data' => $folderContent
           );

           echo json_encode($answ);
       }
       else
       {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function folderCreateNew($data)
   {
       if(isset($data['folderName'])
           && isset($data['fullpath']))
       {
           $folderName = $data['folderName'];
           $fullpath = $data['fullpath'];

           $res = $this->CreateNewFolder($folderName, $fullpath);
           $this->RegisterActionExecution($this->action, "executed", 0, 'folderCreation', $fullpath, $folderName);

           $answ["status"] = "ok";
           $folderId = $res['folderId'];

           $answ["data"] = $res;
           $answ["data"]['folderId'] = $folderId;

           echo json_encode($answ);
       } else {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page flights.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
       }
   }

   public function flightChangePath($data)
   {
       if(isset($data['sender'])
           && isset($data['target'])
       ) {
           $sender = $data['sender'];
           $target = $data['target'];

           $result = $this->ChangeFlightPath($sender, $target);
           $this->RegisterActionExecution($this->action, "executed", $sender, 'flightId', $target, "newPath");

           $answ = array();
           if($result) {
               $answ['status'] = 'ok';
           } else {
               $answ['status'] = 'err';
               $answ['error'] = 'Error during flight change path.';
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

   public function itemExport($data)
   {
       if (!isset($data['flightIds']) && !isset($data['folderDest'])) {
           $answ["status"] = "err";
           $answ["error"] = "Not all nessesary params sent. Post: ".
                   json_encode($_POST) . ". Page FlightsController.php";
           $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
           echo(json_encode($answ));
           exit;
       }

       $flightIds = [];
       $folderDest = [];
       if (isset($data['flightIds'])) {
           if(is_array($data['flightIds'])) {
               $flightIds = array_merge($flightIds, $data['flightIds']);
           } else {
               $flightIds[] = $data['flightIds'];
           }
       }

       $folderDest = [];
       if(isset($data['folderDest']) &&
           is_array($data['folderDest'])) {
               $folderDest = array_merge($folderDest, $data['folderDest']);
       }

       $zipUrl = $this->ExportFlightsAndFolders($flightIds, $folderDest);

       $answ = [];

       if ($zipUrl) {
           $answ = [
               'status' => 'ok',
               'zipUrl' => $zipUrl
           ];

           $this->RegisterActionExecution($this->action, "executed", json_encode(array_merge($flightIds, $flightIds)), "itemId");
       } else {
           $answ = [
               'status' => 'empty',
               'info' => 'No flights to export'
           ];
       }

       echo json_encode($answ);
       exit;
    }

    public function getFlightFdrId($data)
    {
        if (isset($data['flightId'])) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FlightsController.php";
            echo(json_encode($answ));
        }

        $flightId = intval($data['flightId']);

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        unset($Fl);

        $data = array(
            'bruTypeId' => $fdrId
        );

        $answ["status"] = "ok";
        $answ["data"] = $data;

        echo json_encode($answ);
    }

    public function coordinates($data)
    {
        if (!isset($data['id'])) {
            echo 'error';
        }

        header("Content-Type: text/comma-separated-values; charset=utf-8");
        header("Content-Disposition: attachment; filename=coordinates.kml");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        $id = $data['id'];
        $list = $this->GetCoordinates($id);

        $figPrRow = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
         .'<kml xmlns="http://www.opengis.net/kml/2.2"' . PHP_EOL
         .' xmlns:gx="http://www.google.com/kml/ext/2.2"> <!-- required when using gx-prefixed elements -->' . PHP_EOL
        .'<Placemark>' . PHP_EOL
          .'<name>gx:altitudeMode Example</name>' . PHP_EOL
          .'<LineString>' . PHP_EOL
            .'<extrude>1</extrude>' . PHP_EOL
            .'<gx:altitudeMode>absolute </gx:altitudeMode>' . PHP_EOL
            .'<coordinates>' . PHP_EOL;

        foreach ($list as $fields) {
            for($i = 0; $i < count($fields); $i++) {
                $figPrRow .= $fields[$i] . ",";
            }

            $figPrRow = substr($figPrRow, 0, -1);
            $figPrRow .= PHP_EOL;
        }

        $figPrRow .= '</coordinates>' . PHP_EOL
            .'</LineString>' . PHP_EOL
            .'</Placemark>' . PHP_EOL
            .'</kml>';

        echo $figPrRow;
        unset($U);
    }
}
