<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

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

            <label id='logo' class='Logo' style='background-image:url(stylesheets/basicImg/logo.png)'>
               <span style='position:absolute; margin-top:8px;'>Luch</span>
            </label>

            <img class='Separator'></img>

            <label id='currentUploadingTopButt' class='CurrentUploadingTopButt' style='background-image:url(stylesheets/basicImg/add.png)'>
            </label>

            <label id='uploadTopButt' class='UploadButt'>
               <span style='position:absolute; margin-top:8px;'>%s</span>
            </label>

            <label id='userTopButt' class='UserButt' style='background-image:url(stylesheets/basicImg/userPreferences.png); " .
            "width:%spx; font-size:%spx;'
               data-username='%s'>
               <span style='position:absolute; margin-top:%spx;'>%s</span>
            </label>

            <div id='view' style='display:none;'><img class='Separator2'></img>
               <label class='ViewItem' style='background-image:url(stylesheets/basicImg/view.png);'>
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
           <img class='LeftMenuRowIcon' src='stylesheets/basicImg/flight.png'></img>
           %s&nbsp;
           </div>", $this->lang->flightsItem);

     $leftMenu .= sprintf("<div id='searchLeftMenuRow' class='LeftMenuRow'>
           <img class='LeftMenuRowIcon' src='stylesheets/basicImg/search.png'></img>
           %s&nbsp;
           </div>", $this->lang->searchItem);

      $leftMenu .= sprintf("<div id='resultsLeftMenuRow' class='LeftMenuRow'>
          <img class='LeftMenuRowIcon' src='stylesheets/basicImg/gear.png'></img>
          <a style='color: #676767; text-decoration: none;' href='/view/flights.php?action=results'>%s&nbsp;</a>
          </div>", $this->lang->resultsItem);

      /*$leftMenu .= sprintf("<div id='fdrLeftMenuRow' class='LeftMenuRow'>
         <img class='LeftMenuRowIcon' src='stylesheets/basicImg/fdr.png'></img>
         %s&nbsp;
         </div>", $this->lang->bruTypesItem);*/

      $leftMenu .= sprintf("<div id='calibrationLeftMenuRow' class='LeftMenuRow'>
        <img class='LeftMenuRowIcon' src='stylesheets/basicImg/compass.png'></img>
        %s&nbsp;
        </div>", $this->lang->calibrationItem);

      $role = $this->_user->userInfo['role'];
      if(User::isAdmin($role) || User::isModerator($role)) {
         $leftMenu .= sprintf("<div id='usersLeftMenuRow' class='LeftMenuRow'>
               <img class='LeftMenuRowIcon' src='stylesheets/basicImg/user.png'></img>
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

        $fdr = new Bru;
        $fdrList = $fdr->getFdrList($avalibleFdrIds);
        unset($fdr);

        $calibration = new Calibration;
        $fdrCalibrations = $calibration->getCalibrationsForFdrs($avalibleFdrIds, $userId);

        $calibrationSelects = "";
        foreach ($fdrCalibrations as $fdrId => $calibrations) {
            $calibrationSelects .= "<select class='fdr-calibration' data-fdr-id='".$fdrId."'>";
            foreach ($calibrations as $item) {
                $calibrationSelects .= "<option value='".$item['id']."'>".$item['name']."</option>";
            }
            $calibrationSelects .= "</select>";
        }

        $optionString = "";

        foreach($fdrList as $frdInfo) {
            $optionString .="<option data-id='".$frdInfo['id']."' value='".$frdInfo['id']."'>".$frdInfo['bruType']."</option>";
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

      $Fl = new Flight();
      $Fd = new Folder();
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
      $Fd = new Folder();

      $subFoldersArr = $Fd->GetSubfoldersByFolder($folderId, $userId);

      unset($Fd);

      return $subFoldersArr;
   }

   public function CreateNewFolder($extName, $extPath)
   {
      $name = $extName;
      $path = $extPath;

      $userId = $this->_user->GetUserIdByName($this->_user->username);

      $Fd = new Folder();
      $result = $Fd->CreateFolder($name, $path, $userId);
      unset($Fd);

      return $result;
   }

   public function ChangeFlightPath($sender, $target)
   {
      $userId = intval($this->_user->userInfo['id']);

      $Fd = new Folder();
      $result = $Fd->ChangeFlightFolder($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function ChangeFolderPath($sender, $target)
   {
      $userId = intval($this->_user->userInfo['id']);

      $Fd = new Folder();
      $result = $Fd->ChangeFolderPath($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function RenameFolder($extFolderId, $extFolderName)
   {
      $folderId = $extFolderId;
      $folderName = $extFolderName;

      $userId = $this->_user->GetUserIdByName($this->_user->username);

      $Fd = new Folder();
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

         $Fd = new Folder();
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

            $Fl = new Flight();
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
       $FC = new FlightComponent();
       $result = $FC->DeleteFlight($flightId, intval($this->_user->userInfo['id']));
       unset($FC);

       return $result;
   }

   public function SyncFlightsHeaders($extIds)
   {
      $idsArr = $extIds;
      $info = array();

      $Fl = new Flight();
      foreach ($idsArr as $flightId)
   {
         $flightInfo = $Fl->GetFlightInfo($flightId);
         $bruType = $flightInfo["bruType"];

      if($bruType == 'BUR-92A_An-148(158)')
         {
            $info['voyage'] = $flightInfo['voyage'];
            $info['departureAirport'] = $flightInfo['departureAirport'];
            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//            $info['capitan'] = $flightInfo['capitan'];
//            $info['weightto'] = $flightInfo['weightto'];
//            $info['weightlndg'] = $flightInfo['weightlndg'];
         }
//      else if($bruType == 'ER_BSTO_An-148(158)')
//         {
//            $info['voyage'] = $flightInfo['voyage'];
//            $info['departureAirport'] = $flightInfo['departureAirport'];
//            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//         }
//         else if($bruType == 'RPP_Fly_An-148(158)')
//         {
//            $info['voyage'] = $flightInfo['voyage'];
//            $info['departureAirport'] = $flightInfo['departureAirport'];
//            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//         }
   }

      foreach ($idsArr as $flightId) {
         $Fl->UpdateFlightInfo($flightId, $info);
      }

      unset($Fl);

      return true;
   }

    public function ProcessFlight($flightId)
    {
        if (is_int($flightId)) {
            $Fl = new Flight();
            $flightInfo = $Fl->GetFlightInfo($flightId);
            $apTableName = $flightInfo["apTableName"];
            $bpTableName = $flightInfo["bpTableName"];
            $excEventsTableName = $flightInfo["exTableName"];
            $startCopyTime = $flightInfo["startCopyTime"];
            $tableGuid = substr($apTableName, 0, 14);
            unset($Fl);

            $Bru = new Bru();
            $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
            $excListTableName = $bruInfo["excListTableName"];
            $apGradiTableName = $bruInfo["gradiApTableName"];
            $bpGradiTableName = $bruInfo["gradiBpTableName"];
            $stepLength = $bruInfo["stepLength"];

            if ($excListTableName != "")
            {
               $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
               $excListTableName = $bruInfo["excListTableName"];
               $apGradiTableName = $bruInfo["gradiApTableName"];
               $bpGradiTableName = $bruInfo["gradiBpTableName"];

               $FEx = new FlightException();
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
//                   $fp = fopen($tempFilePath, "w");
//                   fwrite($fp, json_encode($exList[$i]["code"]));
//                         fclose($fp);

                  $curExList = $exList[$i];
                  $FEx->PerformProcessingByExceptions($curExList,
                        $flightInfo, $flightExTableName,
                        $apTableName, $bpTableName,
                        $startCopyTime, $stepLength);
               }

               error_reporting(E_ALL);

//                unlink($tempFilePath);
            } else {
//                unlink($tempFilePath);
            }

            unset($Bru);
            $result = true;
            return $result;
      }
      else
      {
         error_log("Incorrect input data. DeleteFlight id - " . json_encode($extId) . ". Page FlightsController.php");
         $result['status'] = false;
         return $result['status'];
      }
   }

   public function GetUserInfo()
   {
      $uId = $this->_user->GetUserIdByName($this->_user->username);
      $this->_user->userInfo = $this->_user->GetUserInfo($uId);

      return $this->_user->userInfo;
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

      $Fd = new Folder();
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);

      $flightColumn .= "<div class='FlightsListTileView'>" .
            "<div id='jstree' class='Tree is-scrollable'></div>".
            "<div id='jstreeContent' class='TreeContent'></div>".
            "</div>";

      return $flightColumn;
   }

   public function PrepareTree($shownFolderId)
   {
      $userId = $this->_user->userInfo['id'];

      $Fd = new Folder();
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

      $Fd = new Folder();
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

      $Fc = new FlightComments();
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

      $Fd = new Folder();
      $flightsInFolders = $Fd->GetAllFlightsInFolders($userId);
      unset($Fd);

      $tableSegment = array();

      $Fl = new Flight();
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
      $Fd = new Folder();

      $uId = $this->_user->userInfo['id'];
      $role = $this->_user->userInfo['role'];
      $allFolders = [];

      if(User::isModerator($role)) {
         $uId = $this->_user->GetUserIdsByAuthor($this->_user->username);
      }

      foreach ($folderDest as $dest) {
         $allFolders = $Fd->SubfoldersDeepScan($dest, $uId, $adminRole);
      }

      foreach ($allFolders as $folderId) {
         $flightIds = array_merge($flightIds,
               $Fd->GetFlightsByFolder($folderId, $uId, $adminRole));
      }
      unset($Fd);

      $exportedFiles = array();
      $exportedFileName = '';
      error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
      set_time_limit (0);
      $Fl = new Flight();
      $C = new DataBaseConnector();
      $Bru = new Bru();

      foreach ($flightIds as $flightId) {

         $flightInfo = $Fl->GetFlightInfo($flightId);

         $fileGuid = uniqid();

         $exportedFileDir = UPLOADED_FILES_PATH;
         $exportedFileName = $flightInfo['bort'] . "_" .
            date("Y-m-d", $flightInfo['startCopyTime'])  . "_" .
            $flightInfo['voyage'] . "_" . $fileGuid;
         $exportedFileRoot = $exportedFileDir . $exportedFileName;

         $headerFile['dir'] = $exportedFileDir;
         $headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage'].$fileGuid.".json";
         $headerFile['root'] = $headerFile['dir'].$headerFile['filename'];

         $exportedFiles[] = $headerFile;

         $apPrefixes = $Bru->GetBruApCycloPrefixes($flightInfo["bruType"]);

         for($i = 0; $i < count($apPrefixes); $i++)
         {
            $exportedTable = $C->ExportTable($flightInfo["apTableName"]."_".$apPrefixes[$i],
                  $flightInfo["apTableName"]."_".$apPrefixes[$i] . "_" . $fileGuid, $exportedFileDir);

            $exportedFiles[] = $exportedTable;

            $flightInfo["apTables"][] = array(
                  "pref" => $apPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $bpPrefixes = $Bru->GetBruBpCycloPrefixes($flightInfo["bruType"]);

         for($i = 0; $i < count($bpPrefixes); $i++)
         {
            $exportedTable = $C->ExportTable($flightInfo["bpTableName"]."_".$apPrefixes[$i],
                  $flightInfo["bpTableName"]."_".$bpPrefixes[$i] . "_" . $fileGuid, $exportedFileDir);

            $exportedFiles[] = $exportedTable;

            $flightInfo["bpTables"][] = array(
                  "pref" => $bpPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         if($flightInfo["exTableName"] != "")
         {
            $exportedTable = $C->ExportTable($flightInfo["exTableName"],
                  $flightInfo["exTableName"] . "_" . $fileGuid, $exportedFileDir);
            $exportedFiles[] = $exportedTable;

            $flightInfo["exTables"] = $exportedTable["filename"];
         }

         $exportedFileDesc = fopen($headerFile['root'], "w");
         fwrite ($exportedFileDesc , json_encode($flightInfo));
         fclose($exportedFileDesc);

      }

      unset($Fl);
      unset($C);
      unset($Bru);

      $zip = new ZipArchive;
      if ($zip->open($exportedFileRoot . '.zip', ZipArchive::CREATE) === TRUE)
      {
         for($i = 0; $i < count($exportedFiles); $i++)
         {
            $zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
         }
         $zip->close();
      }
      else
      {
         error_log('Failed zipping flight. Page asyncFileProcessor.php"');
      }

      for($i = 0; $i < count($exportedFiles); $i++)
      {
         if(file_exists($exportedFiles[$i]['root'])) {
            unlink($exportedFiles[$i]['root']);
         }
      }

      $zipURL = 'http';
      if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on"))
      {
         $zipURL .= "s";
      }
      $zipURL .= "://";
      if ($_SERVER["SERVER_PORT"] != "80") {
         $zipURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
      }
      else
      {
         $zipURL .= $_SERVER["SERVER_NAME"];
      }
      $zipURL .=  UPLOADED_FILES_DIR . $exportedFileName . '.zip';

      error_reporting(E_ALL);

      if($exportedFileName == '') {
         return false;
      }

      return $zipURL;
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
       $c = new DataBaseConnector();
       $link = $c->Connect();
       $list = [];

       $query = "SELECT * FROM `results` WHERE 1;";
       $result = $link->query($query);

       $firstRow = true;

       if(!$result) {
           $c->Disconnect();
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
       $c->Disconnect();

       unset($c);

       return $list;
   }

   public function GetEvents()
   {
       $list = [];
       $userId = intval($this->_user->userInfo['id']);
       $Fd = new Folder();
       $flightsInFolders = $Fd->GetAllFlightsInFolders($userId);
       unset($Fd);

       $firstRow = true;
       $excTables = [];
       $FEx = new FlightException();
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

       $Fl = new Flight();
       $flight = $Fl->GetFlightInfo($flightId);
       unset($Fl);

       $bruType = $flight['bruType'];
       $apTableName = $flight['apTableName'];
       $bpTableName = $flight['bpTableName'];

       $Bru = new Bru();
       $bruInfo = $Bru->GetBruInfo($bruType);
       unset($Bru);

       $kmlScript = $bruInfo['kml_export_script'];
       $kmlScript = str_replace("[ap]", $apTableName, $kmlScript);
       $kmlScript = str_replace("[bp]", $bpTableName, $kmlScript);

       $c = new DataBaseConnector();
       $link = $c->Connect();

       $info = [];
       $averageLat = 0;
       $averageLong = 0;

       if (!$link->multi_query($kmlScript))
       {
           //err log
           error_log("Impossible to execute multiquery: (" .
               $kmlScript . ") " . $link->error);
       }

       do
       {
           if ($res = $link->store_result())
           {
               while($row = $res->fetch_array())
               {
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
}
