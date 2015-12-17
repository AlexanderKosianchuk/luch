<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

//================================================================
//╔═══╦╗────╔╗─╔╗
//║╔══╣║────║║╔╝╚╗
//║╚══╣║╔╦══╣╚╩╗╔╬══╗
//║╔══╣║╠╣╔╗║╔╗║║║══╣
//║║──║╚╣║╚╝║║║║╚╬══║
//╚╝──╚═╩╩═╗╠╝╚╩═╩══╝
//───────╔═╝║
//───────╚══╝
//================================================================
class FlightsModel
{
	public $curPage = 'flightsPage';
	
	private $ulogin;
	private $username;
	
	public $privilege;
	public $lang;
	public $flightActions;

	public $action;
	public $data;

	function __construct($post, $session)
	{
		$L = new Language();
		$this->lang = $L->GetLanguage($this->curPage);
		$this->flightActions = (array)$L->GetServiceStrs($this->curPage);
		unset($L);

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		if(isset($session['username']))
		{
			$this->username = $session['username'];
		}
		else
		{
			$this->username = '';
		}
		
		//even if flight was selected if file send this variant will be processed
		if((isset($post['action']) && ($post['action'] != '')) && 
			(isset($post['data']) && ($post['data'] != '')))
		{
			$this->action = $post['action'];
			$this->data = $post['data'];			
		}
		else
		{
			$msg = "Incorect input. Data: " . json_encode($post['data']) . 
				" . Action: " . json_encode($post['action']) . 
				" . Page: " . $this->curPage. ".";
			echo($msg);
			error_log($msg);
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}
	
	public function PutTopMenu()
	{
		$username = $this->username . "";
		$usernameLen = strlen($username);
		$styleFontSize = 24 - $usernameLen / 2.2;
		$styleWidth = 20 + $usernameLen * $styleFontSize / 2;
		$styleTop = 8 + $usernameLen / 3;
	
		$topMenu = sprintf("<div id='topMenuFlightList' class='TopMenu'>
	
				<label id='logo' class='Logo' style='background-image:url(stylesheets/basicImg/logo.png)'>
					<span style='position:absolute; margin-top:8px;'>Luch</span>
				</label>
	
				<img class='Separator'></img>
	
				<label id='currentUploadingTopButt' class='CurrentUploadingTopButt' style='background-image:url(stylesheets/basicImg/add.png)'>
				</label>
	
				<label id='uploadTopButt' class='UploadButt'>
					<span style='position:absolute; margin-top:8px;'>Загрузка</span>
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
	
				</div>", $styleWidth, $styleFontSize, $username, $styleTop, $username, $this->lang->viewItem);
	
		return $topMenu;
	}
	
	public function PutLeftMenu()
	{
		$leftMenu = sprintf("<div id='leftMenuFlightList' class='LeftMenu'>");
		$leftMenu .= sprintf("<input class='SearchBox' value='' size='24' style='visibility: hidden;'></input>");
	
		$Usr = new User();
		if(in_array($Usr->flightPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->flightPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->flightPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->flightPrivilegeArr[3], $this->privilege) ||
				in_array($Usr->flightPrivilegeArr[4], $this->privilege) ||
				in_array($Usr->flightPrivilegeArr[5], $this->privilege))
		{
			$leftMenu .= sprintf("<div id='flightLeftMenuRow' class='LeftMenuRow LeftMenuRowSelected' data-selected='true'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/flight.png'></img>
					%s&nbsp;
					</div>", $this->lang->flightsItem);
		}
	
		if(in_array($Usr->slicePrivilegeArr[0], $this->privilege) ||
				in_array($Usr->slicePrivilegeArr[1], $this->privilege) ||
				in_array($Usr->slicePrivilegeArr[2], $this->privilege) ||
				in_array($Usr->slicePrivilegeArr[3], $this->privilege))
		{
			/*$leftMenu .= sprintf("<div id='sliceLeftMenuRow' class='LeftMenuRow'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/slice.png'></img>
					%s&nbsp;
					</div>", $this->lang->slicesItem);*/
		}
	
		if(in_array($Usr->enginePrivilegeArr[0], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[1], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[2], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[3], $this->privilege))
		{
			/*$leftMenu .= sprintf("<div id='enginesLeftMenuRow' class='LeftMenuRow'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/engine.png'></img>
					%s&nbsp;
					</div>", $this->lang->enginesItem);*/
		}
	
		if(in_array($Usr->enginePrivilegeArr[0], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[1], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[2], $this->privilege))
		{
			/*$leftMenu .= sprintf("<div id='bruTypesLeftMenuRow' class='LeftMenuRow'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/bru.png'></img>
					%s&nbsp;
					</div>", $this->lang->bruTypesItem);*/
		}
	
		if(in_array($Usr->docsPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[3], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[4], $this->privilege))
		{
			/*$leftMenu .= sprintf("<div id='docsLeftMenuRow' class='LeftMenuRow'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/doc.png'></img>
					%s&nbsp;
					</div>", $this->lang->docsItem);*/
		}
	
		if(in_array($Usr->userPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[3], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[4], $this->privilege))
		{
			/*$leftMenu .= sprintf("<div id='usersLeftMenuRow' class='LeftMenuRow'>
					<img class='LeftMenuRowIcon' src='stylesheets/basicImg/user.png'></img>
					%s&nbsp;
					</div>", $this->lang->usersItem);*/
		}
	
		$leftMenu .= sprintf("</div>");
	
		return $leftMenu;
	}
	
	
	public function FileUploadBlock()
	{
		$Usr = new User();
		$avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->username);
		unset($Usr);
	
		$Bru = new Bru();
		$bruList = $Bru->GetBruList($avalibleBruTypes);
		unset($Bru);
	
		$optionString = "";
	
		foreach($bruList as $bruInfo)
		{
			$optionString .="<option>".$bruInfo['bruType']."</option>";
		}
	
		$fileUploadBlock = sprintf("<div id='fileUploadDialog' class='OptionBlock' title='%s'><br>
			<div id='importConvertRadio'>
				<input type='radio' id='%s' name='radio' checked='checked'><label for='%s'>%s</label>
   				<input type='radio' id='%s' name='radio'><label for='%s'>%s</label>
			</div>
				<br>
			<span class='btn btn-success fileinput-button'>
			<i class='glyphicon'>%s</i>
			<input id='chooseFileBut' type='file' name='files[]' multiple>
			</span>
	
			<div id='bruTypeSelectForUploadingDiv'>
				<select id='bruTypeSelectForUploading' name='bruType' class='FlightUploadingInputs'>%s</select>
			</div>
	
			<div id='previewCheckBoxDiv' class='FlightUploadingInputs'><label><input checked='checked' id='previewCheckBox' type='checkbox'></input>%s</label></div>
	
			<div id='progress' class='progress' style='margin-top:10px;'>
					<div class='progress-bar progress-bar-success'></div>
   			</div>
			<div id='files' class='files'></div>
			<br></div>",
				$this->lang->flightUpload,
	
			$this->flightActions['flightFileConvert'],
				$this->flightActions['flightFileConvert'],
				$this->lang->fileConvert,
				$this->flightActions['flightFileImport'],
				$this->flightActions['flightFileImport'],
				$this->lang->fileImport,
	
				$this->lang->chooseFile,
	
				$optionString,
	
				$this->lang->filePreview);
	
		return $fileUploadBlock;
	}
	
	public function BuildFlightListInTwoColumns($extShownFolder1, $extShownFolder2)
	{		
		$shownFolder1 = $extShownFolder1;
		$shownFolder2 = $extShownFolder2;
		
		$flightsInTwoContainers = "<table class='TwoColumnsTable'><tr>" . 
				"<td id='filesContainerLeft' class='TwoColumnsTableColumn'>";		
		$flightsInTwoContainers .= $this->BuildFlightColumnFromTwoColumns($shownFolder1, "Left");
		$flightsInTwoContainers .= "</td><td id='filesContainerRight' class='TwoColumnsTableColumn'>";
		$flightsInTwoContainers .= $this->BuildFlightColumnFromTwoColumns($shownFolder2, "Right");
		$flightsInTwoContainers .= "</td></tr></table>";		
		
		return $flightsInTwoContainers;
	}
	
	public function BuildFlightColumnFromTwoColumns($extFolder, $extPosition)
	{
		$shownFolderId = $extFolder;
		$position = $extPosition;
		
		$flightColumn = "";
		$flightsInPath = (array)$this->GetFlightsByPath($shownFolderId);
		$subFolders = (array)$this->GetFoldersByPath($shownFolderId);
		
		$Fd = new Folder();
		$shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
		$shownFolder = $shownFolderInfo['name'];
		unset($Fd);
		
		//left container
		if($shownFolderId != 0) //if not root
		{
			$flightColumn .= "<div class='FolderPathInTwoColumnContainer' " .
							"data-path='" .$shownFolderId. "' " .
							"data-position='" .$position. "'>" .
				"<img id='toRootFromPath' " .
					"class='ui-icon ui-icon-home ui-state-default' style='float:left; margin-top:7px;'/>" .
				"<img id='upperFromPath' " .
					"class='ui-icon ui-icon-carat-1-n ui-state-default' style='float:left; margin-top:7px;'/>" .
				"<img id='refreshFolder' " .
					"class='ui-icon ui-icon-refresh ui-state-default' style='float:left; margin-top:7px;'/>" .
				"<img id='newFolderInPath' " .
					"class='ui-icon ui-icon-folder-collapsed ui-state-default' style='float:left; margin-top:7px;'/>&nbsp; " .
					$shownFolder . "</div>";
		}
		else
		{
			$flightColumn .= "<div class='FolderPathInTwoColumnContainer' " .
							"data-path='" .$shownFolderId. "' " .
							"data-position='" .$position. "'>" .
				"<img id='refreshFolder' " .
					"class='ui-icon ui-icon-refresh ui-state-default' style='float:left; margin-top:7px;'/>" .
				"<img id='newFolderInPath' " .
					"class='ui-icon ui-icon-folder-collapsed ui-state-default' style='float:left; margin-top:7px;'/>&nbsp; " .
					$this->lang->root . "</div>";
		}
		
		//if no folders we should also add NonSortableList list container
		if(count($subFolders) == 0)
		{
			$flightColumn .= "<ul class='NonSortableList' " .
						"data-position='".$position."' " .
						"data-folderpath='".$shownFolderId."'></ul>";
		}
		
		for($i = 0; $i < (count($flightsInPath) + count($subFolders)); $i++)
		{
			if($i < count($subFolders))
			{
				if($i == 0)
				{
					$flightColumn .= "<ul class='NonSortableList' " .
						"data-position='".$position."' " .
						"data-folderpath='".$shownFolderId."'>";
				}
		
				$flightColumn .=
					"<li id='draggable".$position."' class='FolderInTwoColumnContainer' " .
						"data-position='".$position."' " .
						"data-folderpath='".$shownFolderId."' " .
						"data-folderdestination='". $subFolders[$i]['id'] . "'>" .
						"<table><tr><td style='width:100%;'>" .
						$subFolders[$i]['name'] . 
						"</td><td style='width:15px; vertical-align:top;'><input class='ItemsCheck' type='checkbox'" . 
						"data-type='folder' data-position='".$position."' data-folderpath='".$shownFolderId."' " .
						"data-folderdestination='". $subFolders[$i]['id'] . "'/>".
						"</td><tr></table>" .
						"</li>";
		
				if($i == count($subFolders) - 1)
				{
					$flightColumn .= "</ul>";
				}
			}
			else
			{
				$j = $i - count($subFolders);
		
				if($j == 0)
				{
					$flightColumn .= "<div id='dropable'><ul id='sortable".$position."' data-curpath='".$shownFolderId."'>";
				}
		
				$procStatus = $this->lang->notPerformed;
				if($flightsInPath[$j]["exTableName"] != "")
				{
					$procStatus = $this->lang->performed;
				}
		
				$flightColumn .= "<li id='draggable".$position."' class='FlightInTwoColumnContainer' " .
							"data-position='".$position."' 
							data-flightid='".$flightsInPath[$j]["id"]."'
							data-folderpath='".$shownFolderId."'>" .
					"<table><tr><td style='width:100%;'>" .
					"<p>" . $this->lang->bort . ": " . $flightsInPath[$j]["bort"] . "; " .
							$this->lang->voyage . ": " . $flightsInPath[$j]["voyage"] .
					"</p>" .
					"<p>" . $this->lang->flightTime . ": " .
						date ("H:i:s d/m/y", $flightsInPath[$j]["startCopyTime"]) . "</p>" .
					"<p>" . $this->lang->uploadTime . ": " .
						date ("H:i:s d/m/y", $flightsInPath[$j]["uploadingCopyTime"]) . "</p>" .
					"<p>" . $this->lang->bruType . ": " .
						$flightsInPath[$j]["bruType"] ."</p>" .
					"<p>" . $this->lang->performer . ": " .
						$flightsInPath[$j]["performer"] ."</p>" .
					"<p>" . $this->lang->status . ": " .
						$procStatus ."</p>" .
					"</td><td style='width:15px; vertical-align:top;'>".
					"<input class='ItemsCheck' type='checkbox' ".
						"data-type='flight' data-position='".$position."' data-flightid='".$flightsInPath[$j]["id"]."'" .
						"data-folderpath='".$shownFolderId."'/></td><tr></table>" .
					"</li>";
		
				if($j == count($flightsInPath) - 1)
				{
					$flightColumn .= "</ul></div>";
				}
			}
		}
		
		//if no flights we should also add dropable area
		if(count($flightsInPath) == 0)
		{
			$flightColumn .= "<div id='dropable'><ul id='sortable".$position."' data-curpath='".$shownFolder."'>";
			$flightColumn .= "</ul></div>";
		}

		return $flightColumn;
	}
	
	private function GetFlightsByPath($extFolderId)
	{		
		$folderId = $extFolderId;
		$U = new User();
		$avalIds = $U->GetAvaliableFlights($this->username);
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
		
		$Fl = new Flight();
		$Fd = new Folder();
		$flightIdsArr = (array)$Fd->GetFlightsByFolder($folderId, $userId);
		$flightsInfoArr = array();
		foreach ($flightIdsArr as $id)
		{
			$flightsInfoArr[] = $Fl->GetFlightInfo($id);
		}
		
		unset($Fd);
		unset($Fl);
		
		return $flightsInfoArr;
	}
	
	private function GetFoldersByPath($extFolderId)
	{
		$folderId = $extFolderId;
	
		$U = new User();
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
	
		$Fd = new Folder();
	
		$subFoldersArr = (array)$Fd->GetSubfoldersByFolder($folderId, $userId);
		unset($Fd);
	
		return $subFoldersArr;
	}
	
	public function CreateNewFolder($extName, $extPath)
	{
		$name = $extName;
		$path = $extPath;
	
		$U = new User();
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
		
		$Fd = new Folder();		
		$result = $Fd->CreateFolder($name, $path, $userId);
		unset($Fd);

		return $result;
	}
	
	public function ChangeFlightPath($extSender, $extTarget)
	{
		$sender = $extSender;
		$target = $extTarget;
		
		$U = new User();
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
		
		$Fd = new Folder();
		$result = $Fd->ChangeFlightFolder($sender, $target, $userId);
		unset($Fd);
	
		return $result;
	}
	
	public function ChangeFolderPath($extSender, $extTarget)
	{
		$sender = $extSender;
		$target = $extTarget;
		
		$U = new User();
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
		
		$Fd = new Folder();
		$result = $Fd->ChangeFolderPath($sender, $target, $userId);
		unset($Fd);
	
		return $result;
	}
	
	public function RenameFolder($extFolderId, $extFolderName)
	{
		$folderId = $extFolderId;
		$folderName = $extFolderName;
	
		$U = new User();
		$userId = $U->GetUserIdByName($this->username);
		unset($U);
	
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
			
			$U = new User();
			$userId = intval($U->GetUserIdByName($this->username));
			unset($U);
			
			$Fd = new Folder();
			$avaliableFolders = $Fd->GetAvaliableFolders($userId);
			$result = array();
			
			if(in_array($id, $avaliableFolders))
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
					
					if(in_array($id, $avaliableFolders))
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
				$dat = "Not avaliable for current user. DeleteFolder id - " . $id . ". " .
					"Username - " . $this->username . ". Page FlightsModel.php";
				error_log($dat);
				$result['status'] = false;
				$result['data'] = $dat;
				return $result;
			}
		}
		else
		{
			error_log("Incorrect input data. DeleteFolder id - " . json_encode($extId) . ". Page FlightsModel.php");
			$result['status'] = false;
			return $result;
		}
	}
	
	public function DeleteFlight($extId)
	{
		if(is_int($extId))
		{
			$id = $extId;
				
			$U = new User();
			$avaliableFlights = $U->GetAvaliableFlights($this->username);
			unset($U);

			if(in_array($id, $avaliableFlights))
			{
				$Fl = new Flight();
				$flightInfo = $Fl->GetFlightInfo($id);
				$bruType = $flightInfo["bruType"];
				
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($bruType);
				$prefixApArr = $Bru->GetBruApCycloPrefixes($bruType);
				$prefixBpArr = $Bru->GetBruBpCycloPrefixes($bruType);
				
				$result = $Fl->DeleteFlight($id, $prefixApArr, $prefixBpArr);
				
				$Usr = new User();
				$Usr->UnsetFlightAvaliable($id);
				unset($Usr);
				$Fd = new Folder();
				$Fd->DeleteFlightFromFolders($id);
				unset($Fd);
				
				return $result;
			}
			else
			{
				error_log("Not avaliable for current user. DeleteFlight id - " . $id . ". " .
						"Username - " . $this->username . ". Page FlightsModel.php");
				$result['status'] = false;
				return $result['status'];
			}
		}
		else
		{
			error_log("Incorrect input data. DeleteFlight id - " . json_encode($extId) . ". Page FlightsModel.php");
			$result['status'] = false;
			return $result['status'];
		}
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
//				$info['capitan'] = $flightInfo['capitan'];
//				$info['weightto'] = $flightInfo['weightto'];
//				$info['weightlndg'] = $flightInfo['weightlndg'];
			}
//		else if($bruType == 'ER_BSTO_An-148(158)') 
//			{
//				$info['voyage'] = $flightInfo['voyage'];
//				$info['departureAirport'] = $flightInfo['departureAirport'];
//				$info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//			}
//			else if($bruType == 'RPP_Fly_An-148(158)') 
//			{
//				$info['voyage'] = $flightInfo['voyage'];
//				$info['departureAirport'] = $flightInfo['departureAirport'];
//				$info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//			}
	}
		
		foreach ($idsArr as $flightId) {
			$Fl->UpdateFlightInfo($flightId, $info);
		}
		
		unset($Fl);
		
		return true;
	}
	
	public function ProcessFlight($extId)
	{
		if(is_int($extId))
		{
			$flightId = $extId;
				
			$U = new User();
			$avaliableFlights = $U->GetAvaliableFlights($this->username);
			unset($U);

			if(in_array($flightId, $avaliableFlights))
			{				
// 				$tempFile = $extTempFileName;
// 				$tempFilePath = UPLOADED_FILES_PATH . "proccessStatus/" . $tempFile;
				
// 				$tmpProccStatusFilesDir = UPLOADED_FILES_PATH . "proccessStatus";
// 				if (!is_dir($tmpProccStatusFilesDir)) {
// 					mkdir($tmpProccStatusFilesDir);
// 				}
					
// 				$tmpStatus = $this->lang->startFlExcProcc;
// 				$fp = fopen($tempFilePath, "w");
// 				fwrite($fp, json_encode($tmpStatus));
// 				fclose($fp);
					
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
				
					//perform proc be cached table
					for($i = 0; $i < count($exList); $i++)
					{
// 						$fp = fopen($tempFilePath, "w");
// 						fwrite($fp, json_encode($exList[$i]["code"]));
// 								fclose($fp);
							
						$curExList = $exList[$i];
						$FEx->PerformProcessingByExceptions($curExList, 
								$flightInfo, $flightExTableName,
								$apTableName, $bpTableName, 
								$startCopyTime, $stepLength);
					}
				
					error_reporting(E_ALL);
				
// 					unlink($tempFilePath);
				}
				else
				{
// 					unlink($tempFilePath);
				}
				
				unset($Bru);
				$result = true;
				return $result;
			}
			else
			{
				error_log("Not avaliable for current user. ProcessFlight id - " . $id . ". " .
						"Username - " . $this->username . ". Page FlightsModel.php");
				$result['status'] = false;
				return $result['status'];
			}
		}
		else
		{
			error_log("Incorrect input data. DeleteFlight id - " . json_encode($extId) . ". Page FlightsModel.php");
			$result['status'] = false;
			return $result['status'];
		}
	}
	
	public function GetUserInfo()
	{
		$U = new User();
		$uId = $U->GetUserIdByName($this->username);
		$userInfo = $U->GetUserInfo($uId);
		unset($U);
		
		return $userInfo;
	}
	
	public function GetLastViewType()
	{
		$viewTypes = array(
			$this->flightActions["flightTwoColumnsListByPathes"],
			$this->flightActions["flightListTree"],
			$this->flightActions["flightListTable"]
		);
		
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		$lastView = $U->GetLastActionFromRange($userId, $viewTypes);
		unset($U);
		
		return $lastView;
	}
	
	public function GetLastFlightTwoColumnsListPathes()
	{
		$viewType = $this->flightActions["flightTwoColumnsListByPathes"];
		
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		$actionsInfo = $U->GetLastAction($userId, $viewType);
		unset($U);
		
		return $actionsInfo;
	}
	
	public function GetLastViewedFolder()
	{
		$viewType = $this->flightActions["showFolderContent"];
	
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		$actionsInfo = $U->GetLastAction($userId, $viewType);
		unset($U);
		
		return $actionsInfo;
	}
	
	public function RegisterActionExecution($extAction, $extStatus, 
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
		
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
			$senderId, $senderName, $targetId, $targetName);
		
		unset($U);
	}
	
	public function RegisterActionReject($extAction, $extStatus,
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
				$senderId, $senderName, $targetId, $targetName);
		
		unset($U);
	}
	
	public function BuildFlightsInTree($extFolder)
	{
		$shownFolderId = $extFolder;
	
		$flightColumn = "";
		
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		unset($U);
	
		$Fd = new Folder();
		$shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
		$shownFolder = $shownFolderInfo['name'];
		unset($Fd);

		$flightColumn .= "<div class='FlightsListTileView'>" .  
				"<div id='jstree' class='Tree'></div>".
				"<div id='jstreeContent' class='TreeContent'></div>".
				"</div>";
	
		return $flightColumn;
	}
	
	public function PrepareTree($extFolder)
	{
		$shownFolderId = $extFolder;
	
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		unset($U);
	
		$Fd = new Folder();
		$content = $Fd->GetAvaliableContent($shownFolderId, $userId);
		unset($Fd);
		
		$relatedNodes = false;
		if(count($content) > 0)
		{
			$relatedNodes = $this->makeRecursive($content);
		}
		
		return $relatedNodes;
	}
	
	public function BuildSelectedFolderContent($extFolder)
	{
		$shownFolderId = $extFolder;
	
		$flightColumn = "";
		
		$Fd = new Folder();
		$flightsInPath = (array)$this->GetFlightsByPath($shownFolderId);
		$subFolders = (array)$this->GetFoldersByPath($shownFolderId);		
		$shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
		$shownFolder = $shownFolderInfo['name'];
		unset($Fd);
		
		foreach($subFolders as $key => $val)
		{
			$input = '<input class="ItemsCheck" type="checkbox" data-type="folder" data-folderpath="'.$shownFolderId.'" data-folderdestination="'.$val['id'].'">';
			$flightColumn .= "<div class='JstreeContentItemFolder'><label>" . $input . " " . $val['name']."</label></div>";
		}
		
		foreach($flightsInPath as $key => $val)
		{
			$name = $val['bort'] . ", " .  $val['voyage']  . ", " . date('d/m/y H:i', $val['startCopyTime'])  .
			", " . $val['bruType']  . ", " . $val['departureAirport']  . "-" . $val['arrivalAirport'] ;
			
			$input = '<input class="ItemsCheck" type="checkbox" data-type="flight" data-folderpath="'.$shownFolderId.'" data-flightid="'.$val['id'].'">';
			$flightColumn .= "<div class='JstreeContentItemFlight'><label>" . $input . " " . $name . "</label></div>";
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
	
	public function BuildTableSegment($extOrderColumn, $extOrderType)
	{
		$orderColumn = $extOrderColumn;
		$orderType = $extOrderType;
		
		$username = $this->username;
		$U = new User();
		$avaliableFlightIds = $U->GetAvaliableFlights($username);
		unset($U);
		$Fl = new Flight();
		$flights = $Fl->GetFlights($avaliableFlightIds, $orderColumn, $orderType);
		unset($Fl);
		
		$tableSegment = array();
		
		foreach($flights as $flight)
		{
			$execution = "-";
			if($flight['exTableName'] != '')
			{
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
		
		return $tableSegment;
	}
	
	public function GetLastSortTableType()
	{
		$viewType = $this->flightActions["segmentTable"];
	
		$U = new User();
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
		$actionsInfo = $U->GetLastAction($userId, $viewType);
		unset($U);
	
		return $actionsInfo;
	}
	
}