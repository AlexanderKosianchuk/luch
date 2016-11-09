<?php

require_once("includes.php");

interface iViewer
{
	function GetLanguage();
	public function IsAppLoggedIn();
	public function ShowLoginForm();
	public function PutCharset();
	public function PutTitle();
	public function PutStyleSheets();
	public function GetUserPrivilege();
	public function PutHeader();
	public function PutMainMenu();
	public function PutScripts();
	public function PutFooter();
}
//================================================================
//╔══╗────╔╗
//╚╣╠╝────║║
//─║║╔═╗╔═╝╠══╦╗╔╗
//─║║║╔╗╣╔╗║║═╬╬╬╝
//╔╣╠╣║║║╚╝║║═╬╬╬╗
//╚══╩╝╚╩══╩══╩╝╚╝
//================================================================
class IndexView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'indexPage';
	private $ulogin;
	private $uloginMsg;
	private $username;
	public $privilege;
	public $lang;

	function __construct()
	{	
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		
		//check COOKIE for autorization posibisity
		$this->ulogin->Autologin();
		//if no autorization info in COOKIE check POST
		if(!$this->ulogin->IsAuthSuccess())
		{
			//if not autorized check POST if it was POST
			$this->TryLoggin();
		} // else set SESSION vars
		else
		{
			$this->AppLogin();
		}
	}
	
	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Index.php");
						error_log("No language object in file for current page. Index.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Index.php");
					error_log("No language object in file for current page. Index.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Index.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Index.php");
				exit();
			}
		}
	}
	
	private function AppLogin()
	{
		$uid = $this->ulogin->AuthResult;
		$this->username = $this->ulogin->Username($this->ulogin->AuthResult);
		
		$_SESSION['uid'] = $uid;
		$_SESSION['username'] = $this->username;
		$_SESSION['loggedIn'] = true;
		
		if (isset($_SESSION['appRememberMeRequested']) && ($_SESSION['appRememberMeRequested'] === true))
		{
			$this->ulogin->SetAutologin($this->username, true);
			unset($_SESSION['appRememberMeRequested']);
		}
		else
		{
			$this->ulogin->SetAutologin($this->username, false);
		}
	}
	
	private function AppLoginFail()
	{
		$this->uloginMsg = $this->lang->loginFailed;
	}
	
	public function TryLoggin()
	{
		if (isset($_POST['nonce']) && ulNonce::Verify('login', $_POST['nonce']))
		{
			if (isset($_POST['autologin']))
			{
				$_SESSION['appRememberMeRequested'] = true;
			}
			else
			{
				unset($_SESSION['appRememberMeRequested']);
			}

			if(isset($_POST['user']) && isset($_POST['pwd']))
			{
				$this->ulogin->Authenticate($_POST['user'],  $_POST['pwd']);
				if($this->ulogin->IsAuthSuccess())
				{
					$this->AppLogin();
				}
				else 
				{
					$this->AppLoginFail();					
				}
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{		
		printf("<div align='center'><p class='Label'>%s</p>
			<label style='color:darkred;'>%s</label></br></br>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
			
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm, 
		$this->uloginMsg,
		$this->lang->userName,
		$this->lang->pass,
		$this->lang->rememberMe,
		ulNonce::Create('login'), 
		
		$this->lang->login);

		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		printf("<title>%s</title>", $this->lang->title);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link rel='stylesheet' href='stylesheets/jquery-ui-1.10.3.custom.min.css'/>
				<link rel='stylesheet' href='stylesheets/jquery.fileupload.css'>
				<link rel='stylesheet' href='stylesheets/style.css' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);	
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}

	public function PutMainMenu()
	{
		printf("<div class='MainMenu'>");
		
		$Usr = new User();
		
		if(in_array($Usr->flightPrivilegeArr[0], $this->privilege) ||
			in_array($Usr->flightPrivilegeArr[1], $this->privilege) ||
			in_array($Usr->flightPrivilegeArr[2], $this->privilege) ||
			in_array($Usr->flightPrivilegeArr[3], $this->privilege) ||
			in_array($Usr->flightPrivilegeArr[4], $this->privilege) ||
			in_array($Usr->flightPrivilegeArr[5], $this->privilege))
		{
			printf("<img id='flight' src='stylesheets/basicImg/flight.png'></img>");
		}
		
		if(in_array($Usr->slicePrivilegeArr[0], $this->privilege) ||
			in_array($Usr->slicePrivilegeArr[1], $this->privilege) ||
			in_array($Usr->slicePrivilegeArr[2], $this->privilege) ||
			in_array($Usr->slicePrivilegeArr[3], $this->privilege))
		{
			printf("<img id='slice' src='stylesheets/basicImg/slice.png'></img>");
		}

		if(in_array($Usr->enginePrivilegeArr[0], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[1], $this->privilege) ||
				in_array($Usr->enginePrivilegeArr[2], $this->privilege))
		{
			printf("<img id='engine' src='stylesheets/basicImg/engine.png'></img>");
		}
		
		if(in_array($Usr->bruTypesPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[3], $this->privilege))
		{
			printf("<img id='bruType' src='stylesheets/basicImg/bru.png'></img>");
		}
		
		/*if(in_array($Usr->docsPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->docsPrivilegeArr[3], $this->privilege))
		{
			printf("<img id='docs' src='stylesheets/basicImg/doc.png'></img>");
		}*/
		
		if(in_array($Usr->userPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[3], $this->privilege) ||
				in_array($Usr->userPrivilegeArr[4], $this->privilege))
		{
			printf("<img id='user' src='stylesheets/basicImg/user.png'></img>");
		}
		
		unset($Usr);
		
		printf("</div>");
		
		$this->FlightSubMenu();
		$this->SliceSubMenu();
		$this->EngineSubMenu();
		$this->BruTypesSubMenu();
		$this->DocsSubMenu();
		$this->UserSubMenu();
	}

	private function FlightSubMenu()
	{
		printf("<div class='FlightSubMenu'>");
		
		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $this->privilege))
		{
			printf("<img id='viewFlight' src='stylesheets/basicImg/flightView.png' title='%s'></img>",
				$this->lang->flightView);
		}
					
		if(in_array(PRIVILEGE_FOLLOW_FLIGHTS, $this->privilege))
		{
			printf("<img id='followFlight' src='stylesheets/basicImg/flightFollow.png' title='%s'></img>",
				$this->lang->flightFollow);
		}
		
		if(in_array(PRIVILEGE_ADD_FLIGHTS, $this->privilege))
		{
			printf("<img id='addFlight' src='stylesheets/basicImg/flightUpload.png' title='%s'></img>",
				$this->lang->flightUpload);
			printf("<img id='impFlight' src='stylesheets/basicImg/flightImp.png' title='%s'></img>",
				$this->lang->flightImport);
		}
					
		if(in_array(PRIVILEGE_DEL_FLIGHTS, $this->privilege))
		{
			printf("<img id='delFlight' src='stylesheets/basicImg/flightDel.png' title='%s'></img>",
				$this->lang->flightDelete);
			printf("<img id='expFlight' src='stylesheets/basicImg/flightExp.png' title='%s'></img>",
				$this->lang->flightExport);
		}
		
		printf("</div>");
	}

	private function SliceSubMenu()
	{
		printf("<div class='SliceSubMenu'>");
				
		if(in_array(PRIVILEGE_VIEW_SLICES, $this->privilege))
		{
			printf("<img id='calcSlice' src='stylesheets/basicImg/sliceCalc.png'></img>");
		}
		
		if(in_array(PRIVILEGE_EDIT_SLICES, $this->privilege))
		{
			printf("<img id='etalonSlice' src='stylesheets/basicImg/sliceEtalon.png'></img>");
			printf("<img id='chooseSlice' src='stylesheets/basicImg/sliceChoose.png'></img>");
			printf("<img id='appendSlice' src='stylesheets/basicImg/sliceAppend.png' hidden='true'></img>");
		}
		
		//this button has more relation to engines but also operates slices
		if(in_array(PRIVILEGE_EDIT_ENGINES, $this->privilege))
		{
			printf("<img id='compareSlice' src='stylesheets/basicImg/sliceCompare.png' hidden='true'></img>");
		}
		
		if(in_array(PRIVILEGE_ADD_SLICES, $this->privilege))
		{
			printf("<img id='createSlice' src='stylesheets/basicImg/sliceCreate.png'></img>");
		}
		
		if(in_array(PRIVILEGE_DEL_SLICES, $this->privilege))
		{
			printf("<img id='delSlice' src='stylesheets/basicImg/sliceDel.png'></img>");
		}
				
		printf("</div>");
	}
	
	private function EngineSubMenu()
	{
		printf("<div class='EngineSubMenu'>");
				
		if(in_array(PRIVILEGE_VIEW_ENGINES, $this->privilege))
		{
			printf("<img id='engineDiagnostic' src='stylesheets/basicImg/engineDiagnostic.png'></img>");
		}
		
		if(in_array(PRIVILEGE_DEL_ENGINES, $this->privilege))
		{
			printf("<img id='engineDel' src='stylesheets/basicImg/engineDel.png'></img>");
		}

		printf("</div> ");
	}
	
	private function BruTypesSubMenu()
	{
		printf("<div class='BruTypesSubMenu'>");
				
						
		if(in_array(PRIVILEGE_VIEW_BRUTYPES, $this->privilege))
		{
			printf("<img id='bruTypeView' src='stylesheets/basicImg/bruTypeView.png'></img>");
		}
		
		if(in_array(PRIVILEGE_EDIT_BRUTYPES, $this->privilege))
		{
			printf("<img id='bruTypeEdit' src='stylesheets/basicImg/bruTypeEdit.png'></img>");
		}
		
		if(in_array(PRIVILEGE_ADD_BRUTYPES, $this->privilege))
		{
			printf("<img id='bruTypeAdd' src='stylesheets/basicImg/bruTypeAdd.png'></img>");
		}
		
		if(in_array(PRIVILEGE_DEL_BRUTYPES, $this->privilege))
		{
			printf("<img id='bruTypeDel' src='stylesheets/basicImg/bruTypeDel.png'></img>");
		}

		printf("</div>");
	}
	
	private function DocsSubMenu()
	{
		/*printf("<div class='DocsSubMenu'>");
		
		if(in_array(PRIVILEGE_VIEW_DOCS, $this->privilege))
		{
			printf("<img id='docView' src='stylesheets/basicImg/docView.png'></img>");
		}
		
		if(in_array(PRIVILEGE_EDIT_DOCS, $this->privilege))
		{
			printf("<img id='docEdit' src='stylesheets/basicImg/docEdit.png'></img>");
		}
		
		if(in_array(PRIVILEGE_ADD_DOCS, $this->privilege))
		{
			printf("<img id='docAdd' src='stylesheets/basicImg/docAdd.png'></img>");
		}
		
		if(in_array(PRIVILEGE_DEL_DOCS, $this->privilege))
		{
			printf("<img id='docDel' src='stylesheets/basicImg/docDel.png'></img>");
		}
		
		printf("</div>");*/
	}
	
	private function UserSubMenu()
	{
		printf("<div class='UserSubMenu'>");
		
		if(in_array(PRIVILEGE_OPTIONS_USERS, $this->privilege))
		{
			//printf("<img id='userOptions' src='stylesheets/basicImg/userOptions.png'></img>");
			printf("<img id='userExit' src='stylesheets/basicImg/userExit.png' title='%s'></img>",
				$this->lang->userExit);
		}
		
		if(in_array(PRIVILEGE_VIEW_USERS, $this->privilege))
		{
			printf("<img id='userView' src='stylesheets/basicImg/userView.png'></img>");
		}
		
		if(in_array(PRIVILEGE_EDIT_USERS, $this->privilege))
		{
			printf("<img id='userEdit' src='stylesheets/basicImg/userEdit.png'></img>");
		}
		
		if(in_array(PRIVILEGE_ADD_USERS, $this->privilege))
		{
			printf("<img id='userAdd' src='stylesheets/basicImg/userAdd.png'></img>");
		}
		
		if(in_array(PRIVILEGE_DEL_USERS, $this->privilege))
		{
			printf("<img id='userDel' src='stylesheets/basicImg/userDel.png'></img>");
		}
		
		printf("</div>");
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
		
		printf("<div id='fileUpload' class='OptionBlock' title='%s'>
				<form action='fileUploader.php' method='post' enctype='multipart/form-data'>
				<select id='bruType' name='bruType' class='FlightUploadingInputs' style='margin-left:11px;'>%s</select>
				<input name='uploadingFile[]' type='file' style='width:250px; margin:10px' multiple='multiple'/></br>
				<input name='submitUploadingFile' value='%s' class='Button' type='submit'/>
				</form></div>", $this->lang->flightUpload, $optionString, $this->lang->send);
	}
	
	public function FileImportBlock()
	{	
		printf("<div id='fileImport' class='OptionBlock' title='%s'><br>
			<span class='btn btn-success fileinput-button'>
			<i class='glyphicon'>%s</i>
			<input id='fileImportBut' type='file' name='files[]' multiple>
			</span>
			<br>
			<br>
			<div id='progress' class='progress'>
					<div class='progress-bar progress-bar-success'></div>
   			</div>
			<div id='files' class='files'></div>
			<br></div>", $this->lang->fileImport, $this->lang->chooseFile);
	}

	public function SliceCreationBlock()
	{
		$Sl = new Slice();
		$slTypesList = $Sl->GetSliceTypesList();
		unset($Sl);
		$optionString = '';
		for($i = 0; $i < count($slTypesList); $i++)
		{
			$optionString .= "<option>".$slTypesList[$i]['code']."</option>";
		}

		printf("<div id='sliceCreation' class='OptionBlock' title='%s'>
				<form action='sliceUploader.php' method='post' enctype='multipart/form-data'>
				<label><input name='name' type='text' style='margin:10px;'/>
				%s
				</label></br>
				<label>
				<select name='code' size='1' style='width:145px; margin:11px'>
				%s
				</select>%s
				</label></br>
				<input name='action' value='%s' style='display:none'/>
				<input name='submitUploadingFile' value='%s' class='Button'
				type='submit'/>
				</form></div>", $this->lang->sliceCreation,
		$this->lang->sliceName,
		$optionString,
		$this->lang->sliceCode,
		SLICE_CREALE,
		$this->lang->create);
	}

	public function ShowSearchBox()
	{
		printf("<div class='SearchBox'>
				<a class='Label'>%s</a>
				<form action='searchEngine.php' method='post'
				enctype='multipart/form-data' name='SearchBox'>
				&nbsp;&nbsp;
				<input id='query' name='query' style='width:400px' disabled/>
				&nbsp;&nbsp;
				<button id='find' class='Button' disabled>%s</button>
				</form></div>",
		$this->lang->search,
		$this->lang->find);
	}

	public function ShowFlightList()
	{
		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $this->privilege))
		{
			$Usr = new User();
			$avalibleFlights = $Usr->GetAvaliableFlights($this->username);
			unset($Usr);
			
			$Fl = new Flight();
			$Fl->CreateFlightTable();
			$listFlights = (array)$Fl->PrepareFlightsList($avalibleFlights);
			unset($Fl);
	
			printf("<div class='FlightList NotSelectable' style='margin-top:-8px;'>
					<a class='Label'>%s</a>
					<form action='tuner.php' method='post'
						enctype='multipart/form-data'
					id='flightList'>
					<table border='0'>", $this->lang->flightList);
	
			$i = 0;
	
			while($i < count($listFlights))
			{
				$flight = (array)$listFlights[$i];
				//error_log(json_encode($flight));
				if($flight['exceptionsSearchPerformed'] == true)
				{
					$flight['exceptionsSearchPerformed'] = $this->lang->performed;
				}
				else
				{
					$flight['exceptionsSearchPerformed'] = $this->lang->notPerformed;
				}
	
				printf("<tr id='flightRow' data-flightid='%s'>
						<td class='FlightListCell'>%s</td>
						<td class='FlightListCell'>
						<input id='flightIdRadioBut' name='radioBut' type='radio' value='%s'
						style='margin-left:20px; margin-right:20px;'></td>
						<td class='FlightListCell' style='width:%s'> %s - %s. %s - %s</br>
						%s - %s </br>
						%s - %s </br>
						%s - %s </br>
						%s - %s; %s - %s </br>
						%s - %s</br>
						%s - %s</td>
						</tr>",
				$flight['bort'], $flight['cellNum'],
				$flight['cellNum'], "100%",
				$this->lang->bort, $flight['bort'], $this->lang->voyage, $flight['voyage'],
				$this->lang->uploadTime, $flight['uploadDate'],
				$this->lang->flightTime, $flight['flightDate'],
				$this->lang->bruType, $flight['bruType'],
				$this->lang->departureAirport, $flight['departureAirport'], $this->lang->arrivalAirport, $flight['arrivalAirport'],
				$this->lang->performer, $flight['performer'],
				$this->lang->status, $flight['exceptionsSearchPerformed']);
				$i++;
			}
	
			printf("</table></form></div>");
		}
	}

	public function ShowSliceList()
	{
		if(in_array(PRIVILEGE_VIEW_SLICES, $this->privilege))
		{
			$Usr = new User();
			$avalibleSlices = $Usr->GetAvaliableSlices($this->username);
			unset($Usr);
			
			$Sl = new Slice();
			$Sl->CreateSliceTable();
			$slList = (array)$Sl->GetSliceList($avalibleSlices);
	
			printf("<div class='SlicesList'>
					<a class='Label'>%s</a>
					<form action='sliceUploader.php' method='post'
					enctype='multipart/form-data' id='sliceList'>
					<table border='0'>", $this->lang->sliceList);
	
			for($i = 0; $i < count($slList); $i++)
			{
				$slice = (array)$slList[$i];
				$sliceTypeInfo = $Sl->GetSliceTypeInfo($slice['code']);
				$slice['bruType'] = $sliceTypeInfo['bruType'];
				
				if($slice['etalonTableName'] != "")
				{
					//if etalon - show engines that formed in etalon
					$status = $this->lang->sliceSetAsEtalon . "; ";
					$etalonPairs = $Sl->GetEtalonEngineSlicesPairs($slice['etalonTableName'], $slice['id']);
					
					for($j = 0; $j < count($etalonPairs); $j++)
					{
						$status .= "</br>" . $etalonPairs[$j]['engineSerial'] . " - " . $etalonPairs[$j]['sliceCode'] . ";";
					}
				}
				else 
				{
					//if just slice - show engines that appended to slice
					$status = $this->lang->sliceNotSetAsEtalon;
					$slicePairs = $Sl->GetSliceEngineSlicesFlightCountPairs($slice['sliceTableName'], $slice['id']);
					
					for($j = 0; $j < count($slicePairs); $j++)
					{
						$status .= "</br>" . $slicePairs[$j]['engineSerial'] . " - " . $slicePairs[$j]['sliceCode'] . " - " . $slicePairs[$j]['flightCount'] . ";";
					}
				}
	
				printf("<tr>
						<td class='FlightListCell'>%s</td>
						<td class='FlightListCell'>
						<input name='sliceId' type='radio' value='%s'
						style='margin-left:20px; margin-right:20px;'></td>
						<td class='FlightListCell'> %s - %s. %s - %s</br>
						%s - %s </br>
						%s - %s </br>
						%s - %s </br>
						%s - %s</td>
						</tr>",
				$slice['id'],
				$slice['id'],
				$this->lang->sliceName, $slice['name'], $this->lang->sliceCode, $slice['code'],
				$this->lang->bruType, $slice['bruType'],
				$this->lang->sliceStatusAsEtalon, $status,
				$this->lang->sliceCreationTime, $slice['creationTime'],
				$this->lang->sliceLastModifyTime, $slice['lastModifyTime']);
			}
	
			printf("</table>
					<input id='flightId' name='flightId' value='' style='display:none'/>
					<input id='sliceUploaderAction' name='action' value='' style='display:none'/>
					</form></div>");
			unset($Sl);
		}
	}
	
	public function ShowEngineList()
	{
		if(in_array(PRIVILEGE_VIEW_ENGINES, $this->privilege))
		{
			$Usr = new User();
			$avalibleEngines = $Usr->GetAvaliableEngines($this->username);
			unset($Usr);
			
			$Eng = new Engine();
			$Eng->CreateEngineDiscrepTable();
			$engineSerialsByEtalonsArr = (array)$Eng->SelectEnginesSerialsByEtalonsList($avalibleEngines);
		
			printf("<div class='EnginesList'>
					<a class='Label'>%s</a>
					<form action='diagnostic.php' method='post'
						enctype='multipart/form-data' id='enginesList'>				
					<table border='0'>", $this->lang->engineList);
			
			$Sl = new Slice();
			$num = 1;
			$curEgineSerial = 0;
			foreach ($engineSerialsByEtalonsArr as $etalonId => $engineSerials)
			{
				$curSliceInfo = $Sl->GetSliceInfo($etalonId);
				
				for($i = 0; $i < count($engineSerials); $i++)
				{
					$curEgineSerial = $engineSerials[$i];
					
					$engineInfo = $Eng->GetEngineInfoBySerialAndEtalon($etalonId, $curEgineSerial);
					printf("<tr>
					<td class='FlightListCell'>%s</td>
					<td class='FlightListCell'>
					<input name='etalonId' type='radio' value='%s' data-engineserial='%s'
						style='margin-left:20px; margin-right:20px;'></td>
					<td class='FlightListCell'> %s - %s </br>
					%s - %s </br>
					%s - %s </br>
					%s - %s </br>
					%s - %s</td>
					</tr>",
						$num,
						$etalonId, $curEgineSerial,
						$this->lang->engineSerial, $curEgineSerial,
						$this->lang->etalonName, $curSliceInfo["name"],
						$this->lang->engineFlightLastTime, date("Y-m-d H:i:s", $engineInfo["flightDate"]),
						$this->lang->engineSliceCodes, $engineInfo["sliceCode"],
						$this->lang->engineDescreps, $engineInfo["discrepCode"]);
					
					$num++;
				}
			}
		
			printf("</table>
				<input id='engineAction' name='engineAction' value='%s' style='display:none'>
				<input id='engineSerial' name='engineSerial' value='%s' style='display:none'>
				</form></div>", $curEgineSerial, ENGINE_DIAGNOSTIC);
			unset($Sl);
			unset($Eng);
		}
	}
	
	public function ShowBruTypesList()
	{
		if(in_array(PRIVILEGE_VIEW_BRUTYPES, $this->privilege))
		{
			$Usr = new User();
			$avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->username);
			
			$Bru = new Bru();
			$Bru->CreateBruTypeTable();
			
			$bruTypeList = (array)$Bru->GetBruList($avalibleBruTypes);
	
			printf("<div class='BruTypesList'>
				<a class='Label'>%s</a>
				<form action='bruTypeManager.php' method='post'
					enctype='multipart/form-data' id='bruTypeList'>
				<table border='0'>", $this->lang->bruTypesList);
				
			$num = 1;
			foreach ($bruTypeList as $bruType)
			{
				$aditionalInfo = $bruType['aditionalInfo'];
				
				if(strlen($aditionalInfo) > 0)
				{
					$aditionalInfoArr = explode(";", $aditionalInfo);
					
					$aditionalInfoLangAdopted = '';
					foreach ($aditionalInfoArr as $aditionalInfoVal)
					{
						if(property_exists($this->lang, $aditionalInfoVal))
						{
							$aditionalInfoLangAdopted .= $this->lang->$aditionalInfoVal . ", ";
						}
						else
						{
							$aditionalInfoLangAdopted .=  $aditionalInfoVal . ", ";
						}
					}
					
					$aditionalInfoLangAdopted = substr($aditionalInfoLangAdopted, 0, -2);
				}
				else
				{
					$aditionalInfoLangAdopted = $this->lang->bruTypesNoAditionalInfo;
				}
				
				printf("<tr>
				<td class='FlightListCell'>%s</td>
					<td class='FlightListCell'>
					<input name='bruTypeId' type='radio' value='%s' data-bruTypeId='%s'
						style='margin-left:20px; margin-right:20px;'></td>
					<td class='FlightListCell'> %s - %s </br>
					%s - %s </br>
					%s - %s </br>
					%s - %s </br>
					%s - %s </br>
					</tr>",
						$num, $bruType['id'], $bruType['id'],
						$this->lang->bruTypesName, $bruType['bruType'],
						$this->lang->bruTypesStepLenth, $bruType['stepLength'],
						$this->lang->bruTypesFrameLength, $bruType['frameLength'],
						$this->lang->bruTypesWordLength, $bruType['wordLength'],
						$this->lang->bruTypesAditionalInfo, $aditionalInfoLangAdopted);
	
				$num++;
			}
	
			printf("</table>
				<input id='bruTypeAction' name='bruTypeAction' value='%s' style='display:none'>
				</form></div>", BRUTYPE_VIEW);
				
			unset($Usr);
		}
	}
	
	public function ShowUsersList()
	{
		if(in_array(PRIVILEGE_VIEW_USERS, $this->privilege))
		{
			$Usr = new User();
			$Usr->CreateUsersTables();
			
			$avalibleUsers = $Usr->GetAvaliableUsers($this->username);
			
			$userList = (array)$Usr->GetUsersList($avalibleUsers);
		
			printf("<div class='UsersList'>
					<a class='Label'>%s</a>
					<form action='userManager.php' method='post'
						enctype='multipart/form-data' id='usersList'>
					<table border='0'>", $this->lang->usersList);
			
			$num = 1;
			foreach ($userList as $user)
			{
				$privilegeChecked = $Usr->CheckPrivilege(explode(",", $user['privilege']));
				
				//just to prevent to long string
				if(strlen($privilegeChecked) > 100)
				{
					$pos = strrpos(substr($privilegeChecked, 0, 100), ",", 0);
					if ($pos !== false) 
					{
						$privilegeChecked = substr_replace($privilegeChecked, "<br>", $pos, 1);
					}
				}
				
				$subscribers = '';
				if($user['subscribers'] == '')
				{
					$subscribers = $this->lang->userNoSubscribers;
				}
				else
				{
					$subscribers = $user['subscribers'];
				}
				
				printf("<tr>
				<td class='FlightListCell'>%s</td>
					<td class='FlightListCell'>
					<input name='userId' type='radio' value='%s' data-userId='%s'
						style='margin-left:20px; margin-right:20px;'></td>
					<td class='FlightListCell'> %s - %s </br>
					%s - %s </br>
					%s - %s </br>
					%s - %s </br>
					</tr>",
				$num, $user['id'], $user['id'],
				$this->lang->usersLogin, $user['login'],
				$this->lang->usersCompany, $user['company'],
				$this->lang->usersPrivilege, $privilegeChecked,
				$this->lang->usersSubscribers, $subscribers);
	
				$num++;
			}
		
			printf("</table>
				<input id='userAction' name='userAction' value='%s' style='display:none'>
				</form></div>", USER_CREATE);
			
			unset($Usr);
		}
	}
	
	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}
	
	public function PutExportLink()
	{
		printf("<div id='exportLink'></div>");
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>");
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>");
		//The jQuery UI widget factory, can be omitted if jQuery UI is already included
		printf("<script type='text/javascript' src='scripts/include/fileUploader/vendor/jquery.ui.widget.js'></script>");
		//The Iframe Transport is required for browsers without support for XHR file uploads
		printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.iframe-transport.js'></script>");
		//The basic File Upload plugin
		printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.fileupload.js'></script>");
		printf("<script type='text/javascript' src='scripts/index.js'></script>");

	}

	public function PutFooter() 
	{
		printf("</body></html>");
	}

}
//================================================================
//╔════╗
//║╔╗╔╗║
//╚╝║║╠╣╔╦═╗╔══╦═╗
//──║║║║║║╔╗╣║═╣╔╝
//──║║║╚╝║║║║║═╣║
//──╚╝╚══╩╝╚╩══╩╝
//================================================================
class TunerView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'tunerPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	private static $cheBoxVariants = array(
			"ToCreate",
			"ToEdit",
			"[]",
			"");
			
	function __construct($post)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['radioBut']) && ($post['radioBut'] != NULL))
		{
			
			$this->info['flightId'] = $_POST['radioBut'];
			$this->SetInfo();
		}
		else
		{
			exit("Flight is not selected on index page");
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Tuner.php");
						error_log("No language object in file for current page. Tuner.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Tuner.php");
					error_log("No language object in file for current page. Tuner.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Tuner.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Tuner.php");
				exit();
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('H:i:s d-m-Y', $this->info['startCopyTime']);

		printf("<title>%s: %s. %s: %s. %s: %s</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery.colorpicker.css' rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){ }

	public function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		/*$apTableName = $flightInfo['apTableName'];
		 $bpTableName = $flightInfo['bpTableName'];
		 $bruType = $flightInfo['bruType'];*/
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		/*$excListTableName = $bruInfo['excListTableName'];
		 $bpGradiTableName = $bruInfo['gradiBpTableName'];
		 $PSTTableName = $bruInfo['paramSetTemplateListTableName'];
		 $stepLength = $bruInfo['stepLength'];
		 $excEventsTableName = $flightInfo['exTableName'];*/
		$this->info['flightApHeaders'] =
			$Bru->GetBruApHeaders($flightInfo['bruType']);
		$this->info['flightBpHeaders'] =
			$Bru->GetBruBpHeaders($flightInfo['bruType']);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}
	
	public function PutInfo()
	{
		printf("<input id='flightId' name='flightId' type='hidden' value='%s' />
				<input id='framesCount' type='hidden' value='%s' />
				<input id='stepLength' type='hidden' value='%s' />
				<input id='username' type='hidden' value='%s' />",
					$this->info['flightId'],
					$this->info['framesCount'],
					$this->info['stepLength'],
					$this->username);
	}

	public function ShowFlightEventsTable()
	{
		//$exTableName = $this->info['exTableName'];
		//$excListTableName = $this->info['excListTableName'];

		if($this->info['exTableName'] != "")
		{
			$FEx = new FlightException();
			$excEventsList = $FEx->GetFlightEventsList($this->info['exTableName']);

			$Frame = new Frame();
			//change frame num to time
			for($i = 0; $i < count($excEventsList); $i++)
			{
				$event = $excEventsList[$i];
				/*$excEventsList[$i]['start'] = $Frame->FrameNumToTime(
					$event['frameNum'],
					$this->info['stepLength'],
					$this->info['startCopyTime']);*/
				
				$excEventsList[$i]['start'] = date("H:i:s", $excEventsList[$i]['startTime'] / 1000);
				$reliability = "checked";
				//converting false alarm to reliability
				if($excEventsList[$i]['falseAlarm'] == 0)
				{
					$reliability = "checked";
				}
				else
				{
					$reliability = "";
				}
				$excEventsList[$i]['reliability'] = $reliability;
				
				/*$excEventsList[$i]['end'] = $Frame->FrameNumToTime(
					$event['endFrameNum'],
					$this->info['stepLength'],
					$this->info['startCopyTime']);*/
				
				$excEventsList[$i]['end'] = date("H:i:s", $excEventsList[$i]['endTime'] / 1000);
				
				/*$excEventsList[$i]['duration'] = $Frame->FrameCountToDuration(
					($event['endFrameNum'] - $event['frameNum']),
					$this->info['stepLength']);*/
				
				$excEventsList[$i]['duration'] = $Frame->TimeStampToDuration(
					$excEventsList[$i]['endTime'] - $excEventsList[$i]['startTime']);
			}
			unset($Frame);

			//if isset events
			if(!(empty($excEventsList)))
			{

				printf("<h3 class='tunerAccordionHeader'>%s</h3><div class='tunerAccordion'>",$this->lang->eventsList);
				
				printf("<div style='width:%s; text-align:center;'><label class='Label'>%s
						</label></br>", "100%", $this->lang->pasport);
				
				$Fr = new Frame();
				$flightDuration = $Fr->FrameCountToDuration($this->info['framesCount'], $this->info['stepLength']);
				unset($Fr);
				
				printf("<label>%s - %s; </label>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>
						<label>%s : %s. </label></br>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>", 
					$this->lang->bort, $this->info['bort'],
					$this->lang->voyage, $this->info['voyage'],
					$this->lang->bruType, $this->info['bruType'],
					
					$this->lang->route, $this->info['departureAirport'] . ' - ' . $this->info['arrivalAirport'],
					
					$this->lang->flightDate, date("H:i:s d-m-Y", $this->info['startCopyTime']),
					$this->lang->duration, $flightDuration,
					$this->lang->performer, $this->info['performer'],
					$this->lang->uploadTime, date("H:i:s d-m-Y", $this->info['uploadingCopyTime']));
				
				/*printf("<label>%s - %s; </label>
						<label>%s - %s; </label>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>
						<label>%s - %s; </label>
						<label>%s - %s. </label></br>
						<label>%s - %s. </label></br>",
							$this->lang->bort, $this->info['bort'],
							$this->lang->voyage, $this->info['voyage'],
							$this->lang->bruType, $this->info['bruType'],
							$this->lang->engines, $this->info['engines'],
								
							$this->lang->departureAirport, $this->info['departureAirport'],
							$this->lang->arrivalAirport, $this->info['arrivalAirport'],
								
							$this->lang->flightDate, date("H:i:s d-m-Y", $this->info['startCopyTime']),
							$this->lang->duration, $flightDuration,
							$this->lang->performer, $this->info['performer']);*/
				
				if(strpos($this->info['aditionalInfo'], ";") >= 0)
				{
					$counterNeedBrake = false;
					$aditionalInfoArr = explode(";", $this->info['flightAditionalInfo']);
					foreach($aditionalInfoArr as $aditionalInfo)
					{
						if($aditionalInfo != "")
						{
							$nameVal = explode(":", $aditionalInfo);
							$name = $nameVal[0];
							$val = $nameVal[1];
							
							if($counterNeedBrake)
							{
								printf("<label>%s - %s </label></br>",
									$this->lang->$name, $val);
								$counterNeedBrake = !$counterNeedBrake;
							}
							else
							{
								printf("<label>%s - %s </label>",
									$this->lang->$name, $val);
								$counterNeedBrake = !$counterNeedBrake;
							}
						}
					}
				}
				
				printf("</div></br>");
				
				printf("<table align='center' class='ExeptionsTable NotSelectable'>
						<tr class='ExeptionsTableHeader'><td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell' width='210px'> %s </td>
						<td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell'> %s </td>
						<td class='ExeptionsCell' width='50px'> %s </td>
						<td class='ExeptionsCell' width='210px'> %s </td></tr>",
				$this->lang->start,
				$this->lang->end,
				$this->lang->duration,
				$this->lang->code,
				$this->lang->eventName,
				$this->lang->algText,
				$this->lang->aditionalInfo,
				$this->lang->reliability,
				$this->lang->comment);

				for($i = 0; $i < count($excEventsList); $i++)
				{
					$event = $excEventsList[$i];
					$excInfo = $FEx->GetExcInfo($this->info['excListTableName'],
						$event['refParam'], $event['code']);

					if($excInfo['status'] == "C")
					{
						$style = "background-color:LightCoral";
					}
					else if($excInfo['status'] == "D")
					{
						$style = "background-color:LightYellow";
					}
					else if($excInfo['status'] == "E")
					{
						$style = "background-color:LightGreen";
					}
					else
					{
						$style = "background-color:none;";
					}
					
					$excAditionalInfo = $event['excAditionalInfo'];
					$excAditionalInfo = str_replace(";", ";</br>", $excAditionalInfo);

					printf("<tr style='%s' class='ExceptionTableRow' 
								data-refparam='%s' 
								data-startframe='%s'
								data-endframe='%s'><td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell'> %s </td>
							<td class='ExeptionsCell' style='text-align:center;'>
								<input id='reliability' data-excid='%s' type='checkbox' %s></input>
							</td>
							<td class='ExeptionsCell' id='userComment' data-excid='%s'> %s </td></tr>",
					$style,
					$event['refParam'],
					$event['frameNum'],
					$event['endFrameNum'],
					$event['start'],
					$event['end'],
					$event['duration'],
					$event['code'],
					$excInfo['comment'],
					$excInfo['algText'],
					$excAditionalInfo,
					$event['id'],
					$event['reliability'],
					$event['id'],
					$event['userComment']);
				}
				printf("</table>");
				unset($FEx);
									
				printf("</br><button id='printColor' class='Button'>%s</button>&nbsp;&nbsp;
						<button id='printBlack' class='Button'>%s</button></br>",
					$this->lang->saveColor, $this->lang->saveBlack);
				
				printf("<form id='toChartByException' action='chart.php' method='post'
							enctype='multipart/form-data' style='display:none'>
						<input id='excFlightId' name='flightId' value=''/>
						<input id='excTpls' name='tpls' value=''/>
						<input id='excStartFrame' name='startFrame' value=''/>
						<input id='excEndFrame' name='endFrame' value=''/>
						</form></br>");		

				printf("<form id='printEvents' action='print.php' target='_blank' method='post'
							enctype='multipart/form-data' style='display:none'>
						<input id='action' name='action' value='%s'/>
						<input id='flightId' name='flightId' value='%s'/>
						</form></br>", PRINT_COLOR_EVENTS, $this->info['flightId']);
				
				printf("</div>");
			}
			else
			{
				printf("<h3 class='tunerAccordionHeader'>%s</h3><div class='tunerAccordion'>
						<table border='1' align='center' style='padding:2px'>
						<tr><td>&nbsp;%s&nbsp;</td></tr>
						</table></div>",
				$this->lang->eventsList,
				$this->lang->noEvents);
			}
		}
		//if no table event search was not performed
	}

	public function ShowTempltList()
	{
		$PSTempl = new PSTempl();
		//if no template table - create it
		$PSTTableName = $this->info['paramSetTemplateListTableName'];
		if($PSTTableName == "")
		{
			$dummy = substr($this->info['gradiApTableName'], 0, -3);
			$this->info['paramSetTemplateListTableName'] = $dummy . "_pst";
			$PSTTableName = $this->info['paramSetTemplateListTableName'];
			$PSTempl->CreatePSTTable($PSTTableName);
			$PSTempl->AddPSTTable($this->info['bruType'], $PSTTableName);
		}

		//if isset excListTable create list to add template
		$excEventsParamsList = array();
		if($this->info['exTableName'] != "")
		{
			$FEx = new FlightException();
			$excEventsList =
			$FEx->GetFlightEventsParamsList($this->info['exTableName']);
			unset($FEx);
		}

		printf("<h3 class='tunerAccordionHeader'>%s</h3>
				<div class='tunerAccordion'>
				<form id='showTpl' action='chart.php' method='post'
					enctype='multipart/form-data'>
				<input id='flightId' name='flightId' type='hidden' value='%s' />
				<input id='framesCount' type='hidden' value='%s' />
				<input id='stepLength' type='hidden' value='%s' />
				<input id='startFrameFromTpl' name='startFrame' type='hidden' value='' />
				<input id='endFrameFromTpl' name='endFrame' type='hidden' value='' />
				<input id='tplNames' name='tpls' type='hidden' value='' />
				<table class='TemplatesListTable'>
				<tr><td class='TemplatesListColl'>

				<select id='tplList' size='10' class='TemplatesListColl' multiple>",
		$this->lang->tplList,
		$this->info['flightId'],
		$this->info['framesCount'],
		$this->info['stepLength']);

		//here builds template options list
		$this->BuildTplOptionList($this::$cheBoxVariants[3]);

		$foundedEventsTplName = $this->lang->foundedEventsTplName;

		//if performed exception search and isset events
		if(!(empty($excEventsList)))
		{
			$params	= "";
			$paramsToAdd = array();
			for($i = 0; $i < count($excEventsList); $i++)
			{
				$params .= $excEventsList[$i] . ", ";
				$paramsToAdd[] = $excEventsList[$i];
			}
			$params = substr($params, 0, -2);

			printf("<option id='tplOption' name='%s' data-comment='' data-params='%s' data-name='%s' selected>
					%s - %s</option>",
			EVENTS_TPL_NAME,
			$params,
			EVENTS_TPL_NAME,
			$foundedEventsTplName,
			$params);

			$gradiApTableName = $this->info['gradiApTableName'];
			$gradiBpTableName = $this->info['gradiBpTableName'];
			$apTableName = $this->info['apTableName'];

			$PSTempl->DeleteTemplate($PSTTableName, EVENTS_TPL_NAME, $this->username);
			
			//insert tpl with event params and distribute them	
			$paramsWithType = array();
			$Ch = new Channel();
			$Bru = new Bru();
			for($i = 0; $i < count($paramsToAdd); $i++)
			{
				$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramsToAdd[$i]);
				if($paramInfo['paramType'] == PARAM_TYPE_AP)
				{
					$apTableNameWithPrefix = $apTableName . "_" . $paramInfo['prefix'];
					$paramMinMax = $Ch->GetParamMinMax($apTableNameWithPrefix,
						$paramsToAdd[$i]);
				
					$paramsWithType[PARAM_TYPE_AP][] = array(
						'code' => $paramsToAdd[$i],
						'min' => $paramMinMax['min'],
						'max' => $paramMinMax['max']
					);
				}
				else if($paramInfo['paramType'] == PARAM_TYPE_BP)
				{
					$paramsWithType[PARAM_TYPE_BP][] = array(
						'code' => $paramsToAdd[$i],
					);
				}
			}
			unset($Bru);
				
			$apCount = count($paramsWithType[PARAM_TYPE_AP]);
				
			for($i = 0; $i < count($paramsWithType[PARAM_TYPE_AP]); $i++)
			{
				$paramCode = $paramsWithType[PARAM_TYPE_AP][$i];
				$yMax = $paramsWithType[PARAM_TYPE_AP][$i]['max'];
				$yMin = $paramsWithType[PARAM_TYPE_AP][$i]['min'];
				$curCorridor = 0;
					
				if($yMax > 0)
				{
					$curCorridor = ($yMax - $yMin);
				} 
				else 
				{
					$curCorridor = -($yMin - $yMax);
				}
					
				$axisMax = $yMax + ($i * $curCorridor);
				$axisMin = $yMin - (($apCount - $i) * $curCorridor);
				
				$PSTempl->AddParamToTemplateWithMinMax($PSTTableName,
					EVENTS_TPL_NAME, $paramsToAdd[$i], $axisMin, $axisMax, $this->username);
			}
				
			if(isset($paramsWithType[PARAM_TYPE_BP]))
			{
				$busyCorridor = (($apCount - 1) / $apCount * 100);
				$freeCorridor = 100 - $busyCorridor;//100%
				
				$bpCount = count($paramsWithType[PARAM_TYPE_BP]);
				$curCorridor = $freeCorridor / $bpCount;
				$j = 0;
				
				for($i = $apCount; $i < $apCount + $bpCount; $i++)
				{			
					$axisMax = 100 - ($curCorridor * $j);
					$axisMin = 0 - ($curCorridor * $j);
						
					$PSTempl->AddParamToTemplateWithMinMax($PSTTableName,
						EVENTS_TPL_NAME, $paramsWithType[PARAM_TYPE_BP][$j]['code'], $axisMin, $axisMax, $this->username);
					$j++;
				};
			}		
			unset($Ch);			
		}

		unset($PSTempl);

		printf("</select></td><td class='TemplatesCommentColl'>
				<textarea id='tplComment' class='TemplatesCommentColl'
				rows='10' readonly/></textarea>
				</td></tr></table>%s
				</br>
				<button id='showChartFromTplBut' class='Button' type='button'/>%s</button>&nbsp;&nbsp;&nbsp;
				<button id='showTableFromTplBut' class='Button' type='button'/>%s</button>
				</form></div>",
		$this->ShowSlider(),
		$this->lang->displayChart,
		$this->lang->displayTable);

	}

	private function BuildTplOptionList($optId)
	{
		$PSTempl = new PSTempl();
		$PSTList = $PSTempl->GetPSTList($this->info['paramSetTemplateListTableName'], $this->username);
		$defaultPSTName = $PSTempl->GetDefaultPST($this->info['paramSetTemplateListTableName'], $this->username);
		unset($PSTempl);

		for($i = 0; $i < count($PSTList); $i++)
		{
			$PSTRow = $PSTList[$i];
			$params = implode(", ", $PSTRow[1]);

			if($PSTRow[0] == $defaultPSTName)
			{
				printf("<option id='tplOption%s' name='%s' data-comment='' data-params='%s' data-name='%s' data-defaulttpl='true' selected>
						%s - %s</option>",
				$optId, $PSTRow[0], $params, $PSTRow[0], "(".$this->lang->defaultTpl.") ".$PSTRow[0], $params);
			}
			else if($PSTRow[0] == PARAMS_TPL_NAME)
			{
				//cant edit last selected params tpl
				if($optId != $this::$cheBoxVariants[1])
				{
					printf("<option id='tplOption%s' name='%s' data-comment='' data-params='%s' data-name='%s' data-defaulttpl='false' selected>
							%s - %s</option>",
					$optId, $PSTRow[0], $params, $PSTRow[0], $this->lang->lastTpl, $params);
				}
			}
			else
			{
				if($PSTRow[0] != EVENTS_TPL_NAME)
				{
					printf("<option id='tplOption%s' name='%s' data-comment='' data-params='%s' data-name='%s' data-defaulttpl='false'>
							%s - %s</option>",
					$optId, $PSTRow[0], $params, $PSTRow[0], $PSTRow[0], $params);
				}
			}
		}
	}

	private function ShowSlider()
	{
		return $slider = "<div></br>
		<p>
			<label for='amount'>".$this->lang->timeRange .":</label>
			<input type='text' id='amount'
			style='border: 0; font-weight: bold;' readonly/>
		</p>
		<div id='slider-range'></div></div>";
	}

	public function ShowParamsListToCreateTemplt()
	{
		printf("<h3 class='tunerAccordionHeader'>%s</h3><div class='tunerAccordion'>
				<form id='createTpl' action='#'>
				<input name='flightId'
				type='hidden' value='%s' />",
		$this->lang->createTpl, $this->info['flightId']);
		
		$this->BuildParamsCheckBoxGroup($this::$cheBoxVariants[0]);

		printf("<label>%s &nbsp;&nbsp;&nbsp;
				<input id='tplName' type='text' /></label>&nbsp;&nbsp;
				<button id='createTplBut' class='Button' type='button'/>
				%s</button></br></form></div>",
		$this->lang->tplName, $this->lang->create);
	}

	public function BuildParamsCheckBoxGroup($cheBoxId)
	{
		printf("<div class='ListContainer'><div class='BpList'>");

		for ($i = 0; $i < count($this->info['flightBpHeaders']); $i++)
		{
			printf("<label>
				<input type='checkbox' id='bpCheckboxGroup%s' value='%s'/>
				%s, %s</label></br>",
			$cheBoxId,
			$this->info['flightBpHeaders'][$i]['code'],
			$this->info['flightBpHeaders'][$i]['name'],
			$this->info['flightBpHeaders'][$i]['code']);
		}

		printf("</div><div class='ApList'>");

		for ($i = 0; $i < count($this->info['flightApHeaders']); $i++)
		{
			printf("<label>
				<input type='checkbox' id='apCheckboxGroup%s' value='%s'/>
				%s, %s</label></br>",
			$cheBoxId,
			$this->info['flightApHeaders'][$i]['code'],
			$this->info['flightApHeaders'][$i]['name'],
			$this->info['flightApHeaders'][$i]['code']);
		}

		printf("</div></div></br>");
	}
	
	public function BuildParamsCheckBoxGroupWithColors($cheBoxId)
	{
		printf("<div class='ListContainer'><div class='BpList'>");
	
		for ($i = 0; $i < count($this->info['flightBpHeaders']); $i++)
		{
		printf("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s' 
					data-colorpicker='false' readonly/>
				<label style='display:inline;'>
				<input type='checkbox' id='bpCheckboxGroup%s' class='bpCheckboxGroup' value='%s'/>
				%s, %s</label></br>",
					$this->info['flightBpHeaders'][$i]['color'],
					$this->info['flightBpHeaders'][$i]['color'],
					$this->info['flightBpHeaders'][$i]['code'],
					$this->info['flightBpHeaders'][$i]['color'],
					$cheBoxId,
					$this->info['flightBpHeaders'][$i]['code'],					
					$this->info['flightBpHeaders'][$i]['name'],
					$this->info['flightBpHeaders'][$i]['code']);
		}
	
		printf("</div><div class='ApList'>");
	
		for ($i = 0; $i < count($this->info['flightApHeaders']); $i++)
		{
		printf("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
					data-colorpicker='false' readonly/>
				<label style='display:inline;'>				
				<input type='checkbox' id='apCheckboxGroup%s' class='apCheckboxGroup' value='%s'/>
				%s, %s</label></br>",	
					$this->info['flightApHeaders'][$i]['color'],
					$this->info['flightApHeaders'][$i]['color'],
					$this->info['flightApHeaders'][$i]['code'],
					$this->info['flightApHeaders'][$i]['color'],
					$cheBoxId,
					$this->info['flightApHeaders'][$i]['code'],
					$this->info['flightApHeaders'][$i]['name'],
					$this->info['flightApHeaders'][$i]['code']);
		}
	
		printf("</div></div></br>");
	}

	public function ShowParamsListToEditTemplt()
	{
		printf("<h3 class='tunerAccordionHeader'>%s</h3>", $this->lang->tplEditor);

		printf("<div class='tunerAccordion'>
				<form id='editTpl' action='#'>
				<select id='tplListToEdit' size='10' style='width:700px;'>");

		$this->BuildTplOptionList($this::$cheBoxVariants[1]);

		printf("</select></br></br>");

		printf("<button id='editTplBut' class='Button'
				type='button'/>%s</button>&nbsp;&nbsp;&nbsp;
				<button id='defaultTplBut' class='Button'
				type='button'/>%s</button>&nbsp;&nbsp;&nbsp;
				<button id='delTplBut' class='Button'
				type='button'/>%s</button></br></br>",
		$this->lang->apply, $this->lang->default, $this->lang->delete);

		$this->BuildParamsCheckBoxGroup($this::$cheBoxVariants[1]);

		printf("</form></div>");

	}

	public function ShowParamsList()
	{
		//attention input#tplNamesShowParams here hardly coded to PARAMS_TPL_NAME
		printf("<h3 class='tunerAccordionHeader'>%s</h3><div class='tunerAccordion'>
				<form id='showParams' action='chart.php' method='post' enctype='multipart/form-data'>
				<input name='flightId' type='hidden' value='%s' />
				<input id='bruType' type='hidden' value='%s' />
				<input id='tplNamesShowParams' name='tpls' type='hidden' value='%s' />",
		$this->lang->paramsListToDisplay,
		$this->info['flightId'],
		$this->info['bruType'],
		PARAMS_TPL_NAME);

		$this->BuildParamsCheckBoxGroupWithColors($this::$cheBoxVariants[3]);

		printf("<button id='showParamsOnChartBut' class='Button'>%s</button>&nbsp;&nbsp;",
		$this->lang->displayChart);

		printf("<button id='showParamsOnTableBut' class='Button'>%s</button>&nbsp;&nbsp;", $this->lang->displayTable);

		printf("<a id='fileHref' style='visibility:hidden;'></a></div>", $this->lang->saveInFile);
	}

	public function PutMessageWindow()
	{
		printf("<div id='dialog' title='%s'>
		  <p></p></div>", $this->lang->message);
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.colorpicker.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-parser.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-crayola.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-memory.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-pantone.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-ral-classic.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-percentage-parser.js'></script>
				
				<script type='text/javascript' src='scripts/proto/progressBar.proto.js'></script>
				<script type='text/javascript' src='scripts/proto/paramSetTemplate.proto.js'></script>
				<script type='text/javascript' src='scripts/tuner.js'></script>");

	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
}
//================================================================
//╔═══╗─────╔╗
//║╔═╗║────╔╝╚╗
//║╚═╝╠═╦╦═╬╗╔╬══╦═╗
//║╔══╣╔╬╣╔╗╣║║║═╣╔╝
//║║──║║║║║║║╚╣║═╣║
//╚╝──╚╝╚╩╝╚╩═╩══╩╝
//================================================================
class PrinterView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'printerPage';
	private $ulogin;
	public $lang;
	public $info;
	private $username;
	public $privilege;

	function __construct($post, $session)
	{
		require_once("/tcpdf/tcpdf.php");
		require_once("/tcpdf/config/tcpdf_config.php");
		
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['action']) && $post['action'] != null)
		{
			$this->action = $post['action'];
			if(isset($post['flightId']) && ($post['flightId'] != NULL))
			{
				
				$this->info['flightId'] = $_POST['flightId'];
				$this->SetInfo();
			}
			else
			{
				exit("Flight is not selected on index page");
			}
		}
		else
		{
			exit("Action is not set");
		}
		
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Tuner.php");
						error_log("No language object in file for current page. Tuner.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Tuner.php");
					error_log("No language object in file for current page. Tuner.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Tuner.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Tuner.php");
				exit();
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('H:i:s d-m-Y', $this->info['startCopyTime']);

		printf("<title>%s: %s. %s: %s. %s: %s</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css' rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){ }

	public function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$this->info['flightApHeaders'] =
			$Bru->GetBruApHeaders($flightInfo['bruType']);
		$this->info['flightBpHeaders'] =
			$Bru->GetBruBpHeaders($flightInfo['bruType']);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}
	
	public function ConstructColorFlightEventsList()
	{
		$user = $this->username;

		// create new PDF document
		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator($user);
		$pdf->SetAuthor($user);
		$pdf->SetTitle('Flight events list');
		$pdf->SetSubject('Flight events list');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
		
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$bruType = $this->info['bruType'];
		$copyDate = date('H:i:s d-m-Y', $this->info['startCopyTime']);
		
		$Fr = new Frame();
		$flightDuration = $Fr->FrameCountToDuration($this->info['framesCount'], $this->info['stepLength']);
		unset($Fr);
		
		$Usr = new User();
		$usrInfo = $Usr->GetUsersInfo($user);
		unset($Usr);
		
		$headerStr = $usrInfo['company'];
		// set default header data
		$pdf->SetHeaderData(/*PDF_HEADER_LOGO*/ "", 
			/*PDF_HEADER_LOGO_WIDTH*/ "",
			/*HEADER_TITLE*/ $headerStr, 
			/*HEADER_STRING*/ "", 
			array(0,10,50), 
			array(0,10,50));
		
		$pdf->setFooterData(array(0,10,50), array(0,10,50));
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array('dejavusans', '', 11));
		
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', 12, '', true);
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();
		
		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		
		//Pasport
		$strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(0, 10, 64);";
		$str = '<p style="'.$strStyle.'">' . $this->lang->pasport . '</p>';
		
		$pdf->writeHTML($str, true, false, false, false, '');

		//Pasport info
		$strStyle = "text-align:center;";
		$str = '<p style="'.$strStyle.'">' . $this->lang->bruType . ' - ' . $this->info['bruType'] . '. <br>' .
			$this->lang->bort . ' - ' . $this->info['bort'] . '; ' .
			$this->lang->voyage . ' - ' . $this->info['voyage'] . '; ' .
			
			$this->lang->route . ' : ' . $this->info['departureAirport'] . ' - ' . $this->info['arrivalAirport'] . '. <br>' .
			$this->lang->flightDate . ' - ' . date('H:i:s d-m-Y', $this->info['startCopyTime']) . '; ' .
			$this->lang->duration . ' - ' .  $flightDuration . '. <br>';
		
		if(strpos($this->info['aditionalInfo'], ";") >= 0)
		{
			$counterNeedBrake = false;
			$aditionalInfoArr = explode(";", $this->info['flightAditionalInfo']);
			foreach($aditionalInfoArr as $aditionalInfo)
			{
				if($aditionalInfo != "")
				{
					$nameVal = explode(":", $aditionalInfo);
					$name = $nameVal[0];
					$val = $nameVal[1];

					if($counterNeedBrake)
					{
						$str .= $this->lang->$name." - ".$val.";</br>";
						$counterNeedBrake = !$counterNeedBrake;
					}
					else
					{
						$str .= $this->lang->$name." - ".$val."; ";
						$counterNeedBrake = !$counterNeedBrake;
					}
					
				}
			}
		}
		
		$str .= "</p>";
		
		$pdf->writeHTML($str, true, false, false, false, '');

		if($this->info['exTableName'] != "")
		{
			$FEx = new FlightException();
			$excEventsList = $FEx->GetFlightEventsList($this->info['exTableName']);

			$Frame = new Frame();
			//change frame num to time
			for($i = 0; $i < count($excEventsList); $i++)
			{
				$event = $excEventsList[$i];
				
				$excEventsList[$i]['start'] = date("H:i:s", $excEventsList[$i]['startTime'] / 1000);
				$reliability = "checked";
				//converting false alarm to reliability
				if($excEventsList[$i]['falseAlarm'] == 0)
				{
					$reliability = true;
				}
				else
				{
					$reliability = false;
				}
				
				$excEventsList[$i]['reliability'] = $reliability;
				$excEventsList[$i]['end'] = date("H:i:s", $excEventsList[$i]['endTime'] / 1000);
				$excEventsList[$i]['duration'] = $Frame->TimeStampToDuration(
					$excEventsList[$i]['endTime'] - $excEventsList[$i]['startTime']);
			}
			unset($Frame);

			//if isset events
			if(!(empty($excEventsList)))
			{
				$pdf->SetFont('dejavusans', '', 9, '', true);
				
				$strStyle = 'style="text-align:center; font-weight: bold; background-color:#708090; color:#FFF"';
				$str = '<p><table border="1" cellpadding="1" cellspacing="1">' .
						'<tr '.$strStyle.'><td width="70"> ' . $this->lang->start . '</td>' .
						'<td width="70">' . $this->lang->end . '</td>' .
						'<td width="70">' . $this->lang->duration . '</td>' .
						'<td width="70">' . $this->lang->code . '</td>' .
						'<td width="260">' . $this->lang->eventName . '</td>' .
						'<td width="110">' . $this->lang->algText . '</td>' .
						'<td width="180">' . $this->lang->aditionalInfo . '</td>' .
						'<td width="110">' . $this->lang->comment . '</td></tr>';
				
				
				for($i = 0; $i < count($excEventsList); $i++)
				{
					$event = $excEventsList[$i];
					$excInfo = $FEx->GetExcInfo($this->info['excListTableName'],
						$event['refParam'], $event['code']);
					
					

					if($event['reliability'])
					{
						if($excInfo['status'] == "C")
						{
							$style = "background-color:LightCoral";
						}
						else if($excInfo['status'] == "D")
						{
							$style = "background-color:LightYellow";
						}
						else if($excInfo['status'] == "E")
						{
							$style = "background-color:LightGreen";
						}
						else
						{
							$style = "background-color:none;";
						}
						
						$excAditionalInfo = $event['excAditionalInfo'];
						$excAditionalInfo = str_replace(";", ";<br>", $excAditionalInfo);
						
						$excInfo['algText'] = str_replace('<', "less", $excInfo['algText']);
						
						$str .= '<tr style="'.$style.'" nobr="true">' .
							'<td width="70" style="text-align:center;">' . $event['start'] . '</td>' .
							'<td width="70" style="text-align:center;">' . $event['end'] . '</td>' .
							'<td width="70" style="text-align:center;">' . $event['duration'] . '</td>' .
							'<td width="70" style="text-align:center;">' . $event['code'] . '</td>' .
							'<td width="260" style="text-align:center;">' . $excInfo['comment'] . '</td>' .
							'<td width="110" style="text-align:center;">' . $excInfo['algText'] . '</td>' .
							'<td width="180" style="text-align:center;">' . $excAditionalInfo . '</td>' .
							'<td width="110" style="text-align:center;"> ' . $event['userComment'] . '</td></tr>';								
					}
				}
				
				unset($FEx);
								
				$str .= "</table></p>";
				
				$pdf->writeHTML($str, false, false, false, false, '');
				
				$pdf->SetFont('dejavusans', '', 12, '', true);
				$str = "</br></br>" .
						$this->lang->performer . ' - ' . 
						$this->info['performer'] . ', ' . date('d-m-Y') . '';
				
				$pdf->writeHTML($str, false, false, false, false, '');
				
			}
			else
			{
				$strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(128, 10, 0);";
				$str = '<p style="'.$strStyle.'">' . $this->lang->noEvents . '</p>';
				
				$pdf->writeHTML($str, false, false, false, false, '');
			}
			
			$pdf->Output('', 'I');
		}	
	}
	public function ConstructBlackFlightEventsList() {
		$user = $this->username;
		
		// create new PDF document
		$pdf = new TCPDF ( 'L', 'mm', 'A4', true, 'UTF-8', false );
		
		// set document information
		$pdf->SetCreator ( $user );
		$pdf->SetAuthor ( $user );
		$pdf->SetTitle ( 'Flight events list' );
		$pdf->SetSubject ( 'Flight events list' );
		// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
		
		$bort = $this->info ['bort'];
		$voyage = $this->info ['voyage'];
		$bruType = $this->info ['bruType'];
		$copyDate = date ( 'H:i:s d-m-Y', $this->info ['startCopyTime'] );
		
		$Fr = new Frame ();
		$flightDuration = $Fr->FrameCountToDuration ( $this->info ['framesCount'], $this->info ['stepLength'] );
		unset ( $Fr );
		
		$Usr = new User ();
		$usrInfo = $Usr->GetUsersInfo ( $user );
		unset ( $Usr );
		
		$headerStr = $usrInfo ['company'];
		// set default header data
		$pdf->SetHeaderData(/*PDF_HEADER_LOGO*/ "",
				/*PDF_HEADER_LOGO_WIDTH*/ "",
				/*HEADER_TITLE*/ $headerStr,
				/*HEADER_STRING*/ "", array (
				0,
				10,
				50 
		), array (
				0,
				10,
				50 
		) );
		
		$pdf->setFooterData ( array (
				0,
				10,
				50 
		), array (
				0,
				10,
				50 
		) );
		
		// set header and footer fonts
		$pdf->setHeaderFont ( Array (
				'dejavusans',
				'',
				11 
		) );
		
		$pdf->setFooterFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
		
		// set margins
		$pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
		$pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
		$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
		
		// set auto page breaks
		$pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );
		
		// set image scale factor
		$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting ( true );
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont ( 'dejavusans', '', 12, '', true );
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage ();
		
		// set text shadow effect
		$pdf->setTextShadow ( array (
				'enabled' => true,
				'depth_w' => 0.2,
				'depth_h' => 0.2,
				'color' => array (
						196,
						196,
						196 
				),
				'opacity' => 1,
				'blend_mode' => 'Normal' 
		) );
		
		// Pasport
		$strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(0, 10, 64);";
		$str = '<p style="' . $strStyle . '">' . $this->lang->pasport . '</p>';
		
		$pdf->writeHTML ( $str, true, false, false, false, '' );
		
		// Pasport info
		$strStyle = "text-align:center;";
		$str = '<p style="' . $strStyle . '">' . $this->lang->bruType . ' - ' . $this->info ['bruType'] . '. <br>' . $this->lang->bort . ' - ' . $this->info ['bort'] . '; ' . $this->lang->voyage . ' - ' . $this->info ['voyage'] . '; ' . 

		$this->lang->route . ' : ' . $this->info ['departureAirport'] . ' - ' . $this->info ['arrivalAirport'] . '. <br>' . $this->lang->flightDate . ' - ' . date ( 'H:i:s d-m-Y', $this->info ['startCopyTime'] ) . '; ' . $this->lang->duration . ' - ' . $flightDuration . '. <br>';
		
		if (strpos ( $this->info ['aditionalInfo'], ";" ) >= 0) {
			$counterNeedBrake = false;
			$aditionalInfoArr = explode ( ";", $this->info ['flightAditionalInfo'] );
			foreach ( $aditionalInfoArr as $aditionalInfo ) {
				if ($aditionalInfo != "") {
					$nameVal = explode ( ":", $aditionalInfo );
					$name = $nameVal [0];
					$val = $nameVal [1];
					
					if ($counterNeedBrake) {
						$str .= $this->lang->$name . " - " . $val . ";</br>";
						$counterNeedBrake = ! $counterNeedBrake;
					} else {
						$str .= $this->lang->$name . " - " . $val . "; ";
						$counterNeedBrake = ! $counterNeedBrake;
					}
				}
			}
		}
		
		$str .= "</p>";
		
		$pdf->writeHTML ( $str, true, false, false, false, '' );
		
		if ($this->info ['exTableName'] != "") {
			$FEx = new FlightException ();
			$excEventsList = $FEx->GetFlightEventsList ( $this->info ['exTableName'] );
			
			$Frame = new Frame ();
			// change frame num to time
			for($i = 0; $i < count ( $excEventsList ); $i ++) {
				$event = $excEventsList [$i];
				
				$excEventsList [$i] ['start'] = date ( "H:i:s", $excEventsList [$i] ['startTime'] / 1000 );
				$reliability = "checked";
				// converting false alarm to reliability
				if ($excEventsList [$i] ['falseAlarm'] == 0) {
					$reliability = true;
				} else {
					$reliability = false;
				}
				
				$excEventsList [$i] ['reliability'] = $reliability;
				$excEventsList [$i] ['end'] = date ( "H:i:s", $excEventsList [$i] ['endTime'] / 1000 );
				$excEventsList [$i] ['duration'] = $Frame->TimeStampToDuration ( $excEventsList [$i] ['endTime'] - $excEventsList [$i] ['startTime'] );
			}
			unset ( $Frame );
			
			// if isset events
			if (! (empty ( $excEventsList ))) {
				$pdf->SetFont ( 'dejavusans', '', 9, '', true );
				
				$strStyle = 'style="text-align:center; font-weight: bold; background-color:#708090; color:#FFF"';
				$str = '<p><table border="1" cellpadding="1" cellspacing="1">' . '<tr ' . $strStyle . '><td width="70"> ' . $this->lang->start . '</td>' . '<td width="70">' . $this->lang->end . '</td>' . '<td width="70">' . $this->lang->duration . '</td>' . '<td width="70">' . $this->lang->code . '</td>' . '<td width="260">' . $this->lang->eventName . '</td>' . '<td width="110">' . $this->lang->algText . '</td>' . '<td width="180">' . $this->lang->aditionalInfo . '</td>' . '<td width="110">' . $this->lang->comment . '</td></tr>';
				
				for($i = 0; $i < count ( $excEventsList ); $i ++) {
					$event = $excEventsList [$i];
					$excInfo = $FEx->GetExcInfo ( $this->info ['excListTableName'], $event ['refParam'], $event ['code'] );
					
					if ($event ['reliability']) {
						
						$excAditionalInfo = $event ['excAditionalInfo'];
						$excAditionalInfo = str_replace ( ";", ";<br>", $excAditionalInfo );
						
						$excInfo ['algText'] = str_replace ( '<', "less", $excInfo ['algText'] );
						
						$str .= '<tr nobr="true">' . '<td width="70" style="text-align:center;">' . $event ['start'] . '</td>' . '<td width="70" style="text-align:center;">' . $event ['end'] . '</td>' . '<td width="70" style="text-align:center;">' . $event ['duration'] . '</td>' . '<td width="70" style="text-align:center;">' . $event ['code'] . '</td>' . '<td width="260" style="text-align:center;">' . $excInfo ['comment'] . '</td>' . '<td width="110" style="text-align:center;">' . $excInfo ['algText'] . '</td>' . '<td width="180" style="text-align:center;">' . $excAditionalInfo . '</td>' . '<td width="110" style="text-align:center;"> ' . $event ['userComment'] . '</td></tr>';
					}
				}
				
				unset ( $FEx );
				
				$str .= "</table></p>";
				
				$pdf->writeHTML ( $str, false, false, false, false, '' );
				
				$pdf->SetFont ( 'dejavusans', '', 12, '', true );
				$str = "</br></br>" . $this->lang->performer . ' - ' . $this->info ['performer'] . ', ' . date ( 'd-m-Y' ) . '';
				
				$pdf->writeHTML ( $str, false, false, false, false, '' );
			} else {
				$strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(128, 10, 0);";
				$str = '<p style="' . $strStyle . '">' . $this->lang->noEvents . '</p>';
				
				$pdf->writeHTML ( $str, false, false, false, false, '' );
			}
			
			$pdf->Output ( '', 'I' );
		}
	}


	public function PutMessageWindow()
	{
		printf("<div id='dialog' title='%s'>
		  <p></p></div>", $this->lang->message);
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.colorpicker.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-parser.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-crayola.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-memory.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-pantone.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-ral-classic.js'></script>
				<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-percentage-parser.js'></script>
				
				<script type='text/javascript' src='scripts/proto/progressBar.proto.js'></script>
				<script type='text/javascript' src='scripts/proto/paramSetTemplate.proto.js'></script>
				<script type='text/javascript' src='scripts/tuner.js'></script>");

	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
}
//================================================================
//╔╗─╔╗──╔╗────────╔╗
//║║─║║──║║────────║║
//║║─║╠══╣║╔══╦══╦═╝╠══╦═╗
//║║─║║╔╗║║║╔╗║╔╗║╔╗║║═╣╔╝
//║╚═╝║╚╝║╚╣╚╝║╔╗║╚╝║║═╣║
//╚═══╣╔═╩═╩══╩╝╚╩══╩══╩╝
//────║║
//────╚╝
//================================================================
class UploaderView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'uploaderPage';
	private $ulogin;
	public $lang;
	private $fileInfo;
	private $flightId;
	public $action;
	private $username;
	public $privilege;

	function __construct($files, $post, $session)
	{
		$this->GetLanguage();
		
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
		if(((isset($files['uploadingFile'])) && (($files['uploadingFile']['name'][0]) != "") || 
			isset($post['uploadingFile'])) && 
			(isset($post['bruType']) && ($post['bruType'] != '')))
		{
			if(isset($post['uploadingFile']))
			{
				$this->fileInfo['uploadingFile']['name'] = $post['uploadingFile'];
				$this->fileInfo['uploadingFile']['onServer'] = true;
			}
			else 
			{
				$this->fileInfo['uploadingFile'] = $files['uploadingFile'];
				$this->fileInfo['uploadingFile']['onServer'] = false;
			}
			$this->fileInfo['bruType'] = $post['bruType'];
			$this->action = FILE_UPLOAD;
		}
		else if(isset($post['radioBut']) && ($post['radioBut'] != ''))
		{
			$this->flightId = $post['radioBut'];
			
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($this->flightId);
			$this->fileInfo['bruType'] = $flightInfo['bruType'];
			$this->fileInfo['uploadingFile']['name'] = "";
			
			$this->action = FILE_DELETE;
		}
		else
		{
			exit("File not selected");
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Uploader.php");
						error_log("No language object in file for current page. Uploader.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Uploader.php");
					error_log("No language object in file for current page. Uploader.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Uploader.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Uploader.php");
				exit();
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$names = '';
		
		for($i = 0; $i < count($this->fileInfo['uploadingFile']['name']); $i++)
		{
			if(isset($this->fileInfo['uploadingFile']['name'][$i]))
			{
				$names .= $this->fileInfo['uploadingFile']['name'][$i] . ", ";
			}
		}
		
		$names = substr($names, 0, -2);
		
		printf("<title>%s - %s</title>",
		$this->lang->uploadingFile, $names);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){ }

	public function CopyFiles()
	{
		$fileSize = '';
		$filePathes = '';
		for($i = 0; $i < count($this->fileInfo['uploadingFile']['name']); $i++)
		{
			$Fr = new Frame();
			if(!$this->fileInfo['uploadingFile']['onServer'])
			{
				$uploadedFile = $Fr->MoveUploadingFile($this->fileInfo['uploadingFile']['name'][$i], $this->fileInfo['uploadingFile']['tmp_name'][$i]);
			}
			else 
			{
				$uploadedFile = $this->fileInfo['uploadingFile']['name'];
			}
			
			$filePathes .= $uploadedFile . ",";
			$fileDesc = $Fr->OpenFile($uploadedFile);
			$fileSize .= $Fr->GetFileSize($uploadedFile) . ",";
			unset($Fr);
		}
		$fileSize = substr($fileSize, 0, -1);
		$filePathes = substr($filePathes, 0, -1);
		printf("<input id='fileSize' type='hidden' value=".$fileSize.">");
		printf("<input id='filePath' type='hidden' value=".$filePathes.">");

	}
	
	public function PutInfo()
	{
		$bruType = $this->fileInfo['bruType'];
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$previewParams = trim($bruInfo['previewParams']);
		unset($Bru);
		
		printf("<input id='bruType' type='hidden' value='%s' />
				<input id='previewParams' type='hidden' value='%s' />",
					$bruType,
					$previewParams);
	}

	public function ShowFlightParams()
	{

		$bruType = $this->fileInfo['bruType']; 
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$previewParams = $bruInfo['previewParams'];
		unset($Bru);

		printf("<div class='FlightUploadingInfo' align='center'>
				<form action='#' id='showFlightParams'>
				<a class='Label'>%s</a>
				</br>
				<table border='0' style='margin-top:15px; margin-bottom:15px;'>
				<tr>
				<td>%s</td>
				<td>
				<input id='bruType' name='bruType' class='FlightUploadingInputs' value='%s' readonly /></td>
					</tr><tr>
				<td>%s</td>
				<td><input id='bort' name='bort' type='text' class='FlightUploadingInputs'/></td>
					</tr><tr>
				<td>%s</td>
				<td><input id='voyage' name='voyage' type='text' class='FlightUploadingInputs'/></td>
					</tr><tr>
				<td>%s</td>
				<td><input id='departureAirport' name='departureAirport' type='text' class='FlightUploadingInputs'/></td>
				</tr>
				<tr>
				<td>%s</td>
				<td><input id='arrivalAirport' name='arrivalAirport' type='text' class='FlightUploadingInputs'/></td>
					</tr><tr>
				<td>%s</td>
				<td><input id='copyCreationTime' name='copyCreationTime' type='time' width='150px'/> &nbsp;&nbsp;
				<input id='copyCreationDate' name='copyCreationDate' type='date' /></td>
					</tr><tr>
				<td>%s</td>
				<td><input id='performer' name='performer' type='text' class='FlightUploadingInputs' value='%s'/></td>
				</tr>",
		$this->lang->enterFlightDetails,
		$this->lang->bruType,
		$bruType,
		$this->lang->bortNum,
		$this->lang->voyage,
		$this->lang->departureAirport,
		$this->lang->arrivalAirport,
		$this->lang->flightDate,
		$this->lang->performer,
		$this->username);
		
		if($bruInfo['aditionalInfo'] != '')
		{
			if (strpos($bruInfo['aditionalInfo'], ";") !== 0) 
			{
				$aditionalInfo = explode(";", $bruInfo['aditionalInfo']);
				$aditionalInfo  = array_map('trim', $aditionalInfo);
			}
			else 
			{
				$aditionalInfo = (array)trim($bruInfo['aditionalInfo']);
			}
					
			for($i = 0; $i < count($aditionalInfo); $i++)
			{

				if(property_exists($this->lang, $aditionalInfo[$i]))
				{
					$labelsArr = get_object_vars($this->lang);
					$label = $labelsArr[$aditionalInfo[$i]];
				}
				else
				{
					if(!(property_exists($this->lang, 'aditionalInfo')))
					{
						$this->lang->aditionalInfo = "Aditional info";
					}
					
					$label = $this->lang->aditionalInfo;
				}
				
				printf("<tr>
				<td>%s</td>
				<td><input id='%s' name='%s' type='text' class='FlightUploadingInputsAditionalInfo'/></td>
				</tr>", $label, $aditionalInfo[$i], "aditionalInfo" . $i);
			}
		}
		
		if(in_array(PRIVILEGE_EDIT_ENGINES, $this->privilege))
		{
			$Usr = new User();
			$avalibleSlices = $Usr->GetAvaliableSlices($this->username);
			unset($Usr);
				
			$Sl = new Slice();
			$Sl->CreateSliceTable();
			$sliceList = (array)$Sl->GetSliceList($avalibleSlices);

			$etalonsFound = false;
			$etalonsArr = array();
		
			foreach ($sliceList as $sliceInfo)
			{
				if($sliceInfo["etalonTableName"] != "")
				{
					$etalonsArr[] = $sliceInfo;
				}
			}
		
			$etalonsOptions = "<option data-sliceId='".ETALON_DO_NOT_COMPARE."' selected='selected'>".$this->lang->doNotCompate."</option>";
			foreach ($etalonsArr as $sliceInfo)
			{
				$curSliceTypeInfo = $Sl->GetSliceTypeInfo($sliceInfo["code"]);
				if($curSliceTypeInfo["bruType"] == $bruType)
				{
					$etalonsOptions .= "<option data-sliceId='".$sliceInfo["id"]."'>".$sliceInfo["name"]."</option>";
				}
			}
			
			printf("<tr>
				<td>%s</td>
				<td> <select id='compareToEtalon' name='compareToEtalon' class='FlightUploadingInputs'>%s</select> </td>
				</tr>", 
				$this->lang->compareToEtalon, $etalonsOptions);
		}
		
		$actionOptions = "<option data-action='".UPLOADER_TO_MAIN."'>".$this->lang->toMain."</option>";
		$actionOptions .= "<option data-action='".UPLOADER_TO_TUNER."'>".$this->lang->toTuner."</option>";
		$actionOptions .= "<option data-action='".UPLOADER_TO_CHART."' selected='selected'>".$this->lang->toChart."</option>";
		$actionOptions .= "<option data-action='".UPLOADER_TO_DIAGNOSTIC."'>".$this->lang->toDiagnostic."</option>";
		
		printf("<tr>
			<td>%s</td>
			<td> <select id='actionAfterUpload' name='actionAfterUpload' class='FlightUploadingInputs'>%s</select> </td>
				</tr>", $this->lang->actionAfterComplete, $actionOptions);
		
		if(in_array(PRIVILEGE_TUNE_FLIGHTS, $this->privilege))
		{
			printf("<tr><td>%s</td>
				<td><input id='execProc' name='execProc' type='checkbox' class='FlightUploadingInputs'/></td>
					</tr>", $this->lang->execProc);
		}
		
		
		printf("</table>");
		printf("<input id='submitInputFlightInfoButt' type='button' class='Button' value='%s'/>&nbsp;",
			$this->lang->continue);
		
		$previewParams = trim($previewParams);
		if($previewParams != '')
		{
			printf("<input id='sliceFlightButt' type='button' class='Button' value='%s'/>",
				$this->lang->slice);
		}
		
		printf("</br></form></div>");
		
		if($previewParams != '')
		{
			$this->PutPreviewChartContainer();
			$this->SlicedFlightsForm();
		}
	}

	public function PutPreviewChartContainer()
	{
		print("<div id='previewChartContainer' style='width:95%; border:0;'>
				<table><tr><td><div id='previewChartPlaceholder'></div></td><td>
				<div id='previewChartLegend'></div></td></tr></table>
				</div>");
	}

	public function DeleteFlight()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->flightId);
		$bruType = $flightInfo["bruType"];

		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$prefixArr = $Bru->GetBruApGradiPrefixes($bruType);

		$Fl->DeleteFlight($this->flightId, $prefixArr);
		
		$Usr = new User();
		$Usr->UnsetFlightAvaliable($this->flightId);
		unset($Usr);
		
		unset($Fl);
	}
	
	public function PutRedirectForm()
	{
		printf("<form id='redirectForm' action='index.php' method='post' enctype='multipart/form-data' style='visibility: hidden'>
		<input id='option1' name='name1' value='val'/>
		<input id='option2' name='name2' value='val'/>
		<input id='option3' name='name2' value='val'/>
		</form>");		
	}
	
	public function SlicedFlightsForm()
	{
		printf("<form id='slicedFlightsForm' action='fileUploader.php' 
				method='post' enctype='multipart/form-data' style='visibility: hidden'>
		<input id='slicedUploadingFile' name='uploadingFile' value='%s'/>
		<input id='slicedBruType' name='bruType' value='%s'/>
		</form>", $this->fileInfo['uploadingFile'], $this->fileInfo['bruType']);
	}

	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}
	
	public function PutLoadingBox()
	{
		$bruType = $this->fileInfo['bruType'];
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$previewParams = trim($bruInfo['previewParams']);
		unset($Bru);
		
		if($previewParams != '')
		{
			printf("<div id='loadingBox' class='LoadingBox' width='%s'>
					<img style='margin:0px auto 0px;' src='stylesheets/basicImg/loading.gif'/>
					</div>",'100%');
		}
	}

	public function PutDragProgressBar()
	{
		printf("<div id='draggableProgressContainer'>
				<a class='Label'>Процесс загрузки</a>
				<div id='progressbar'></div>
				<div id='progressLabel'  data-receivedinfo=''>Инициализация...</div>
				</div>");
	}

	public function PutScripts()
	{
		printf("<script language='javascript' type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script language='javascript' type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				<script language='javascript' type='text/javascript' src='scripts/proto/progressBar.proto.js'></script>
				<script language='javascript' type='text/javascript' src='scripts/fileUploader.js'></script>");
		
		printf("<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>-->

			<!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
	
}
//================================================================
//╔═══╦╗
//║╔═╗║║
//║╚══╣║╔╦══╦══╗
//╚══╗║║╠╣╔═╣║═╣
//║╚═╝║╚╣║╚═╣║═╣
//╚═══╩═╩╩══╩══╝
//================================================================
class SliceView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'slicePage';
	private $ulogin;
	public $lang;
	private $sliceInfo;
	private $fligthId;
	public $action;
	private $username;
	public $privilege;

	function __construct($post)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['action']) && (($post['action']) == SLICE_CREALE))
		{
			$this->action = SLICE_CREALE;
			if(isset($post['name']) && (($post['name']) != "") &&
			isset($post['code']) && (($post['code']) != ""))
			{
				$this->sliceInfo['name'] = $post['name'];
				$this->sliceInfo['code'] = $post['code'];
			}
			else
			{
				exit("Input error during " . $this->action);
			}
		}
		else if(isset($post['action']) && (($post['action']) == SLICE_APPEND))
		{
			$this->action = SLICE_APPEND;
			if(isset($post['flightId']) && (($post['flightId']) != "") &&
			isset($post['sliceId']) && (($post['sliceId']) != ""))
			{
				$this->sliceInfo['id'] = $post['sliceId'];
				$this->fligthId = $post['flightId'];

				$Sl = new Slice();
				$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
				unset($Sl);

				if($this->sliceInfo["etalonTableName"] != "")
				{
					exit("Etalon already created.Error during action " . $this->action);
				}
			}
			else
			{
				exit("Input error during " . $this->action);
			}
		}
		else if(isset($post['action']) && (($post['action']) == SLICE_COMPARE))
		{
			$this->action = SLICE_COMPARE;
			if(isset($post['flightId']) && (($post['flightId']) != "") &&
			isset($post['sliceId']) && (($post['sliceId']) != ""))
			{
				$this->sliceInfo['id'] = $post['sliceId'];
				$this->fligthId = $post['flightId'];

				$Sl = new Slice();
				$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
				unset($Sl);

				if($this->sliceInfo["etalonTableName"] == "")
				{
					exit("Etalon not created.Error during action " . $this->action);
				}
			}
			else
			{
				exit("Input error during " . $this->action);
			}
		}
		else if(isset($post['action']) && (($post['action']) == SLICE_DEL))
		{
			$this->action = SLICE_DEL;
			if((!isset($post['flightId'])) && //flight id not set
			isset($post['sliceId']) && (($post['sliceId']) != ""))
			{
				$this->sliceInfo['id'] = $post['sliceId'];

				$Sl = new Slice();
				$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
				unset($Sl);
			}
			else
			{
				exit("Input error during " . $this->action);
			}
		}
		else if(isset($post['action']) && (($post['action']) == SLICE_ETALON))
		{
			$this->action = SLICE_ETALON;
			if(isset($post['sliceId']) && (($post['sliceId']) != ""))
			{
				$this->sliceInfo['id'] = $post['sliceId'];
					
				$Sl = new Slice();
				$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
				unset($Sl);
			}
			else
			{
				exit("Input error during " . $this->action);
			}
			
			if($this->sliceInfo["etalonTableName"] != "")
			{
				exit("Selected slice is already etalon");
			}
		}
		else if(isset($post['action']) && (($post['action']) == SLICE_SHOW))
		{
			$this->action = SLICE_SHOW;
			if(isset($post['sliceId']) && (($post['sliceId']) != ""))
			{
				$this->sliceInfo['id'] = $post['sliceId'];
					
				$Sl = new Slice();
				$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
				unset($Sl);
			}
			else
			{
				exit("Input error during " . $this->action);
			}
		}
		else
		{
			exit("Unexpected action");
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Slice.php");
						error_log("No language object in file for current page. Slice.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Slice.php");
					error_log("No language object in file for current page. Slice.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Slice.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Slice.php");
				exit();
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		printf("<title>%s-%s</title>",
		$this->lang->sliceName, $this->sliceInfo['name']);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu() {}

	public function InsertSlice()
	{
		$Sl = new Slice();
		$sliceInfo = $Sl->CreateSlice($this->sliceInfo['name'], $this->sliceInfo['code'], $this->username);
		
		$Usr = new User();
		$Usr->SetSliceAvaliable($this->username, $sliceInfo['id']);
		unset($Usr);
		
		unset($Sl);
	}

	public function DeleteSlice()
	{
		$Sl = new Slice();
		$Sl->DeleteSlice($this->sliceInfo['id']);
		$Sl->DropEngineEtalonModel($this->sliceInfo['sliceTableName']);
		
		$Usr = new User();
		$Usr->UnsetSliceAvaliable($this->sliceInfo['id']);
		unset($Usr);
		
		unset($Sl);
	}

	public function AppendFligthToSlice()
	{
		$Sl = new Slice();
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);

		if($sliceTypeInfo['children'] != '')
		{
			$childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
			$childCodesArray = array_filter($childCodesArray);
			$childCodesArray = array_map('trim', $childCodesArray);

			for($j = 0; $j < count($childCodesArray); $j++)
			{
				$childCode = $childCodesArray[$j];
				$childSliceTypeInfo = $Sl->GetSliceTypeInfo($childCode);

				$sliceCode = $childCode;
				$sliceTypeInfo = $childSliceTypeInfo;
				$this->AppendCodeReginToSlice($sliceTypeInfo, $sliceCode);
			}

		}
		else
		{
			$sliceCode = $this->sliceInfo['code'];
			$this->AppendCodeReginToSlice($sliceTypeInfo, $sliceCode);
		}
		unset($Sl);
	}

	public function AppendCodeReginToSlice($sliceTypeInfo, $sliceCode)
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->fligthId);
		unset($Fl);

		$apTableName = $flightInfo["apTableName"];
		$bpTableName = $flightInfo["bpTableName"];

		$sliceAlgAp = trim($sliceTypeInfo['algAp']);
		$sliceAlgApArray = (array)explode("#", $sliceAlgAp);
			
		$sliceAlgBp = trim($sliceTypeInfo['algBp']);
		$sliceAlgBpArray = (array)explode("#", $sliceAlgBp);
			
		$bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
		$bpParamNamesArray = array_filter($bpParamNamesArray);
		$bpParamNamesArray = array_map('trim', $bpParamNamesArray);
			
		//replacing |paramName| and [ap] with actual names
		$bru = new Bru();
		$bruInfo = $bru->GetBruInfo($flightInfo["bruType"]);
		$gradiApTableName = $bruInfo["gradiApTableName"];
		$gradiBpTableName = $bruInfo["gradiBpTableName"];
		$sliceAlgApArrayPrepared = array();
		$apParamNamesArray = array();
		$Sl = new Slice();
		foreach($sliceAlgApArray as $apAlg)
		{
			$paramCode = $Sl->GetInnerSubstring($apAlg);
			//because only one substr element required
			$paramCode = $paramCode[0];
			$apParamNamesArray[] = $paramCode;
			$paramInfo = $bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);
			//in ap still can be [bp] to find nessesary flight stage
			$apAlg = str_replace("[bp]", "`".$bpTableName."`", $apAlg);
			$apAlg = str_replace("[ap]", "`".$apTableName."_".$paramInfo["prefix"]."`", $apAlg);
			$apAlg = str_replace("|".$paramCode."|", $paramCode, $apAlg);
			$sliceAlgApArrayPrepared[] = $apAlg;
		}
		unset($bru);
			
		$sliceAlgBpArrayPrepared = array();
		foreach($sliceAlgBpArray as $bpAlg)
		{
			$bpAlg = str_replace("[bp]", "`".$bpTableName."`", $bpAlg);
			$sliceAlgBpArrayPrepared[] = $bpAlg;
		}
		
		//we know that after word "engine" in $sliceCode there is engine num
		$engineNumArr = explode("Engine", $sliceCode);
		$engineNum = $engineNumArr[1];
		//also know that in flight info engine serials has been written coma separated
		$engineSerialsArr = explode(',', str_replace(' ', '', $flightInfo["engines"]));
		$engineSerial = $engineSerialsArr[$engineNum - 1];

		$sliceData = $Sl->FormSliceData($this->fligthId, $engineSerial, $sliceCode,
		$sliceAlgApArrayPrepared, $sliceAlgBpArrayPrepared,
		$apParamNamesArray, $bpParamNamesArray);
			
		$Sl->InsertSliceData($sliceData, $this->sliceInfo['sliceTableName'],
		$apParamNamesArray, $bpParamNamesArray);
		unset($Sl);
	}

	public function UpdateSliceTime()
	{
		$Sl = new Slice();
		$Sl->UpdateSliceTime($this->sliceInfo['id']);
		unset($Sl);
	}

	public function ShowSliceComment()
	{
		$Sl = new Slice();
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);
		$flightIds = (array)$Sl->GetFlightsFromSliceRaw($this->sliceInfo['sliceTableName']);
		$borts = array();
		$voyages = array();
		$engines = array();

		$Fl = new Flight();

		foreach($flightIds as $value)
		{
			$flightInfo = $Fl->GetFlightInfo($value);
			$borts[] = $flightInfo['bort'];
			$voyages[] = $flightInfo['voyage'];
			$engines[] = $flightInfo['engines'];
		}
		unset($Fl);
		$borts = implode(", ", $borts);
		$borts = explode(",", $borts);
		$borts = array_map('trim', $borts);
		$borts = array_unique($borts);
		$borts = array_filter($borts);
		$borts = implode(", ", $borts);

		$voyages = implode(", ", $voyages);
		$voyages = explode(",", $voyages);
		$voyages = array_map('trim', $voyages);
		$voyages = array_unique($voyages);
		$voyages = array_filter($voyages);
		$voyages = implode(", ", $voyages);

		$engines = implode(", ", $engines);
		$engines = explode(",", $engines);
		$engines = array_map('trim', $engines);
		$engines = array_unique($engines);
		$engines = array_filter($engines);
		$engines = implode(", ", $engines);

		printf("<div><a class='Label'>%s</a> - %s</div>", $this->lang->bort, $borts);
		printf("<div><a class='Label'>%s</a> - %s</div>", $this->lang->voyage, $voyages);
		printf("<div><a class='Label'>%s</a> - %s</div></br>", $this->lang->engines, $engines);

		unset($Sl);
	}

	public function ShowSliceSummer()
	{
		$Sl = new Slice();
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);
		if($sliceTypeInfo['children'] != '')
		{
			$childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
			$childCodesArray = array_filter($childCodesArray);
			$childCodesArray = array_map('trim', $childCodesArray);

			$childCode = $childCodesArray[0];
			$childSliceTypeInfo = $Sl->GetSliceTypeInfo($childCode);

			$summerList = $Sl->GetSliceSummer($childCode);

			printf("<table width='%s' border='1px'><tr>", (count($summerList) + 1) * 155 . 'px');
			printf("<td style='font-weight:bold;' width='150px'>%s</td>", $this->lang->sliceName);

			for($i = 0; $i < count($summerList); $i++)
			{
				printf("<td style='font-weight:bold;' width='150px'>%s (%s)</td>", $summerList[$i]['name'], $summerList[$i]['code']);
			}
			printf("</tr>");

			for($j = 0; $j < count($childCodesArray); $j++)
			{
				$childCode = $childCodesArray[$j];
				$childSliceTypeInfo = $Sl->GetSliceTypeInfo($childCode);
				$summerList = $Sl->GetSliceSummer($childCode);
				printf("<tr><td>%s</td>",$childCode);

				for($i = 0; $i < count($summerList); $i++)
				{
					$p = $Sl->CalcSummerItem($summerList[$i],
					$this->sliceInfo['sliceTableName'],
					$childCode);
					printf("<td>%s</td>",$p);
				}
				printf("</tr>");
			}

			printf("</table>");
		}
		else
		{
			$summerList = $Sl->GetSliceSummer($this->sliceInfo['code']);

			printf("<table width='%s' border='1px'><tr>", count($summerList) * 155 . 'px');
			printf("<td style='font-weight:bold;' width='150px'>%s</td>", $this->lang->sliceName);

			for($i = 0; $i < count($summerList); $i++)
			{
				printf("<td style='font-weight:bold;' width='150px'>%s (%s)</td>", $summerList[$i]['name'], $summerList[$i]['code']);
			}
			printf("</tr><tr><td>%s</td>", $this->sliceInfo['code']);
			for($i = 0; $i < count($summerList); $i++)
			{
				$p = $Sl->CalcSummerItem($summerList[$i],
				$this->sliceInfo['sliceTableName'],
				$this->sliceInfo['code']);
				printf("<td>%s</td>",$p);
			}
			printf("</tr></table>");
		}

		unset($Sl);
	}

	public function CreateSliceEtalon()
	{
		$Sl = new Slice();
		$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);
		$Sl->DropEngineEtalonModel($this->sliceInfo['sliceTableName']);
		$etalonTableName = $Sl->CreateEngineEtalonModel($this->sliceInfo['sliceTableName']);
		if($etalonTableName == -1)
		{
			error_log("Error during etalon table creation");
			exit("Error during etalon table creation");
		}
		$Sl->SetEtalonTableName($this->sliceInfo['id'], $etalonTableName);
		unset($Sl);
	}

	public function CalcSliceEtalonRealValueBased()
	{
		$Sl = new Slice();
		$this->sliceInfo = $Sl->GetSliceInfo($this->sliceInfo['id']);
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);
		$etalonTableName = $Sl->GetEngineEtalonTableName($this->sliceInfo['sliceTableName']);
		
		$neuralNetworkStagesToUse = unserialize(NN_STAGES_TO_USE);

		//if not exist than create
		if($etalonTableName == -1)
		{
			$etalonTableName = $Sl->CreateEngineEtalonModel($this->sliceInfo['sliceTableName']);
		}

		if($sliceTypeInfo['children'] != '')
		{
			$childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
			$childCodesArray = array_filter($childCodesArray);
			$childCodesArray = array_map('trim', $childCodesArray);

			for($j = 0; $j < count($childCodesArray); $j++)
			{
				$sliceCode = $childCodesArray[$j];
				$this->CalcSliceEtalonCodeRegion($sliceCode, $etalonTableName);
				
				//if neural network stage to use, than run it
				if(in_array($sliceCode, $neuralNetworkStagesToUse))
				{
					$this->RunNeurelNetworkForEtalonCandidate($sliceCode);
				}
			}
		}
		else
		{
			$sliceCode = $this->sliceInfo['code'];
			$this->CalcSliceEtalonCodeRegion($sliceCode, $etalonTableName);
			
			//if neural network stage to use, than run it
			if(in_array($sliceCode, $neuralNetworkStagesToUse))
			{
				$this->RunNeurelNetworkForEtalonCandidate($sliceCode);
			}
		}
		$sliceEtalonParamsList = $Sl->GetSliceEtalonParamsList($this->sliceInfo['code']);
		unset($Sl);
	}

	public function CalcSliceEtalonCodeRegion($sliceCode, $etalonTableName)
	{
		$Sl = new Slice();
		$summerList = $Sl->GetSliceSummer($sliceCode);
		$sliceEtalonParamsList = $Sl->GetSliceEtalonParamsList($sliceCode);
		
		for($k = 0; $k < count($sliceEtalonParamsList); $k++)
		{
			//get all engine serials and work with each separately
			$engineSerialsArr = $Sl->GetEngineSerialsInSlice($this->sliceInfo['sliceTableName'], $sliceCode);
			
			for($l = 0; $l < count($engineSerialsArr); $l++)
			{
				$curEngineSerial = $engineSerialsArr[$l];
				$sliceEtalonParams = $sliceEtalonParamsList[$k];
				
				$sliceSummerXCode = $Sl->GetSliceSummer($sliceCode, $sliceEtalonParams['XCode']);
				$avgFlightValuesXCode = $Sl->CalcSummerItemForEtalon($sliceSummerXCode,
					$this->sliceInfo['sliceTableName'],	$sliceCode, $curEngineSerial);
	
				$sliceSummerYCode = $Sl->GetSliceSummer($sliceCode, $sliceEtalonParams['YCode']);
				$avgFlightValuesYCode = $Sl->CalcSummerItemForEtalon($sliceSummerYCode,
					$this->sliceInfo['sliceTableName'],	$sliceCode, $curEngineSerial);
				
				//if less than 3 not possible to perform calculations
				if((count($avgFlightValuesXCode) > 3) && (count($avgFlightValuesYCode) > 3))
				{
					$a = array(
							"11" => 0,
							"12" => 0,
							"13" => 0,
							"21" => 0,
							"22" => 0,
							"23" => 0,
							"31" => 0,
							"32" => 0,
							"33" => 0
					);
		
					for($i = 0; $i < count($avgFlightValuesXCode); $i++)
					{
						$a["11"] +=	pow($avgFlightValuesXCode[$i], 4);
						$a["12"] +=	pow($avgFlightValuesXCode[$i], 3);
						$a["13"] +=	pow($avgFlightValuesXCode[$i], 2);
						$a["23"] +=	$avgFlightValuesXCode[$i];
					}
		
					$a["21"] +=	$a["12"];
					$a["22"] +=	$a["13"];
					$a["31"] +=	$a["13"];
					$a["32"] +=	$a["23"];
					$a["33"] +=	count($avgFlightValuesXCode);
		
					$b = array(
							"1" => 0,
							"2" => 0,
							"3" => 0
					);
		
					for($i = 0; $i < count($avgFlightValuesYCode); $i++)
					{
						$b["1"] +=	$avgFlightValuesYCode[$i] * pow($avgFlightValuesXCode[$i], 2);
						$b["2"] +=	$avgFlightValuesYCode[$i] * $avgFlightValuesXCode[$i];
						$b["3"] +=	$avgFlightValuesYCode[$i];
					}
		
					$D = $a["11"] * $a["22"] * $a["33"] +
					$a["12"] * $a["23"] * $a["31"] +
					$a["21"] * $a["32"] * $a["13"] -
					$a["13"] * $a["22"] * $a["31"] -
					$a["12"] * $a["21"] * $a["33"] -
					$a["11"] * $a["23"] * $a["32"];
		
					$Dx = $b["1"] * $a["22"] * $a["33"] +
					$a["12"] * $a["23"] * $b["3"] +
					$b["2"] * $a["32"] * $a["13"] -
					$a["13"] * $a["22"] * $b["3"] -
					$a["12"] * $b["2"] * $a["33"] -
					$b["1"] * $a["23"] * $a["32"];
		
					$Dy = $a["11"] * $b["2"] * $a["33"] +
					$b["1"] * $a["23"] * $a["31"] +
					$a["21"] * $b["3"] * $a["13"] -
					$a["13"] * $b["2"] * $a["31"] -
					$b["1"] * $a["21"] * $a["33"] -
					$a["11"] * $a["23"] * $b["3"];
		
					$Dz = $a["11"] * $a["22"] * $b["3"] +
					$a["12"] * $b["2"] * $a["31"] +
					$a["21"] * $a["32"] * $b["1"] -
					$b["1"] * $a["22"] * $a["31"] -
					$a["12"] * $a["21"] * $b["3"] -
					$a["11"] * $b["2"] * $a["32"];
		
					$A = $Dx / $D;
					$B = $Dy / $D;
					$C = $Dz / $D;
		
					$XAvgVal = array_sum($avgFlightValuesXCode)/count($avgFlightValuesXCode);
					$YAvgVal = array_sum($avgFlightValuesYCode)/count($avgFlightValuesYCode);
		
					$Sl->InsertEtalonItem($etalonTableName,
						$this->sliceInfo['id'],
						$sliceCode, $curEngineSerial,
						$sliceEtalonParams['XCode'],
						$sliceEtalonParams['YCode'],
						$avgFlightValuesXCode,
						$avgFlightValuesYCode,
						$XAvgVal, $YAvgVal,
						$A, $B, $C);
				}
			}
		}
		unset($Sl);

	}

	public function CalcComparingSliceWithEtalon()
	{		
		$Sl = new Slice();
		$sliceTypeInfo = $Sl->GetSliceTypeInfo($this->sliceInfo['code']);

		if($sliceTypeInfo['children'] != '')
		{
			$childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
			$childCodesArray = array_filter($childCodesArray);
			$childCodesArray = array_map('trim', $childCodesArray);

			for($j = 0; $j < count($childCodesArray); $j++)
			{
				$sliceCode = $childCodesArray[$j];
				$sliceTypeInfo = $Sl->GetSliceTypeInfo($sliceCode);
				//echo("</br>" . $sliceCode . "</br>");
				$this->CompareSliceToEtalon($this->fligthId, $this->sliceInfo, $sliceTypeInfo, $sliceCode);
			}
		}
		else
		{
			$sliceCode = $this->sliceInfo['code'];
			$this->CompareSliceToEtalon($this->fligthId, $this->sliceInfo, $sliceTypeInfo, $sliceCode);
		}
	}

	public function CompareSliceToEtalon($fligthId, $sliceInfo, $sliceTypeInfo, $sliceCode)
	{
		$discrepYArr = unserialize(DISCREP_Y_ARR);
		$discrepX = unserialize(DISCREP_X);
		
		$descrepNamedArr = array();
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($fligthId);
		unset($Fl);
		$apTableName = $flightInfo["apTableName"];
		$bpTableName = $flightInfo["bpTableName"];

		$Sl = new Slice();

		$sliceAlgAp = trim($sliceTypeInfo['algAp']);
		$sliceAlgApArray = (array)explode("#", $sliceAlgAp);

		$sliceAlgBp = trim($sliceTypeInfo['algBp']);
		$sliceAlgBpArray = (array)explode("#", $sliceAlgBp);

		$bpParamNamesArray = (array)explode(",", $sliceTypeInfo['bpParamNames']);
		$bpParamNamesArray = array_filter($bpParamNamesArray);
		$bpParamNamesArray = array_map('trim', $bpParamNamesArray);

		//replacing |paramName| and [ap] with actual names
		$bru = new Bru();
		$bruInfo = $bru->GetBruInfo($flightInfo["bruType"]);
		$gradiApTableName = $bruInfo["gradiApTableName"];
		$gradiBpTableName = $bruInfo["gradiBpTableName"];
		$sliceAlgApArrayPrepared = array();
		$apParamNamesArray = array();
		foreach($sliceAlgApArray as $apAlg)
		{
			$paramCode = $Sl->GetInnerSubstring($apAlg);
			//because only one substr element required
			$paramCode = $paramCode[0];
			$apParamNamesArray[] = $paramCode;
			$paramInfo = $bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

			//in ap still can be [bp] to find nessesary flight stage
			$apAlg = str_replace("[bp]", "`".$bpTableName."`", $apAlg);
			$apAlg = str_replace("[ap]", "`".$apTableName."_".$paramInfo["prefix"]."`", $apAlg);
			$apAlg = str_replace("|".$paramCode."|", $paramCode, $apAlg);
			$sliceAlgApArrayPrepared[] = $apAlg;
		}
		unset($bru);

		$sliceAlgBpArrayPrepared = array();
		foreach($sliceAlgBpArray as $bpAlg)
		{
			$bpAlg = str_replace("[bp]", "`".$bpTableName."`", $bpAlg);
			$sliceAlgBpArrayPrepared[] = $bpAlg;
		}
		
		//we know that after word "engine" in $sliceCode there is engine num
		$engineNumArr = explode("Engine", $sliceCode);
		$engineNum = $engineNumArr[1];
		//also know that in flight info engine serials has been written coma separated
		$engineSerialsArr = explode(',', str_replace(' ', '',$flightInfo["engines"]));
		$engineSerial = $engineSerialsArr[$engineNum - 1];

		$sliceData = $Sl->FormSliceData($fligthId, $engineSerial, $sliceCode,
		$sliceAlgApArrayPrepared, $sliceAlgBpArrayPrepared,
		$apParamNamesArray, $bpParamNamesArray);

		$tmpSliceInfo = $Sl->CreateTmpSlice($sliceInfo['name'], $sliceCode);

		$Sl->InsertSliceData($sliceData, $tmpSliceInfo['tableName'],
		$apParamNamesArray, $bpParamNamesArray);
		$summerList = $Sl->GetSliceSummer($sliceCode);

		$comparingFlightSummery = array();
		for($i = 0; $i < count($summerList); $i++)
		{
			$p = $Sl->CalcSummerItem($summerList[$i],
			$tmpSliceInfo['tableName'],
			$sliceCode);
			$comparingFlightSummery[$summerList[$i]["code"]] = $p;
		}

		//here we begin to use $discrepYArr and $discrepX
		foreach($discrepYArr as $val)
		{
			$etalonRow = $Sl->GetEtalonRow($sliceInfo["etalonTableName"],
				$sliceInfo["id"], $engineSerial,
				$sliceCode,
				$discrepX,
				$val);
			
			if($etalonRow != null)
			{
				$Xij = $comparingFlightSummery[$val];
				$SjE = $comparingFlightSummery["S"];
				$A = $etalonRow["A"];
				$B = $etalonRow["B"];
				$C = $etalonRow["C"];
				$XijE = $A * $SjE * $SjE + $B * $SjE + $C;
				$XESj = $etalonRow["YAvgGeneral"];
				//count is equal of this two arrays
				$XFlightArr = explode(",", $etalonRow["XAvgFlightValues"]);
				$YFlightArr = explode(",", $etalonRow["YAvgFlightValues"]);
	
				//calc delta
				$deltaArr = array();
				$deltaSum = 0;
				for($i = 0; $i < count($YFlightArr); $i++)
				{
					$deltaXijE = $A * $XFlightArr[$i] * $XFlightArr[$i] +
					$B * $XFlightArr[$i] +
					$C;
					$delta = ($YFlightArr[$i] - $deltaXijE) / $XESj;
					array_push($deltaArr, $delta);
					$deltaSum += $delta;
				}
	
				$deltaiAvg = $deltaSum / count($deltaArr);
	
				//calc sigma
				$sigmaSum = 0;
				for($i = 0; $i < count($deltaArr); $i++)
				{
					$sigmaSum += pow(($deltaArr[$i] - $deltaiAvg), 2);
				}
				$sigma = sqrt($sigmaSum / count($deltaArr));
	
				//calc DP
				$DPsum = 0;
				for($i = 0; $i < count($YFlightArr); $i++)
				{
					$deltaXijE = $A * $XFlightArr[$i] * $XFlightArr[$i] +
					$B * $XFlightArr[$i] +
					$C;
					$delta = ($YFlightArr[$i] - $deltaXijE) / ($XESj * $sigma);
					$DPsum += $delta;
				}
				//echo("DPsum " . $DPsum . "<br>");
				$DP = $DPsum / count($YFlightArr);
	
				//final discrep calc
				$discrepFirstType = ($Xij - $XijE) / ($XESj * $sigma) - $DP;
				$descrepNamedArr[$val] = $discrepFirstType;
				//printf("<tr><td>П " . $val . "</td><td>" . $discrepFirstType . "<td></tr>");
			}
		}

		//useless now
		$Sl->DeleteTmpSlice($tmpSliceInfo['tableName']);
		
		//we know that after word "engine" in $sliceCode there is engine num
		$engineNumArr = explode("Engine", $sliceCode);
		$engineNum = $engineNumArr[1];
		//also know that in flight info engine serials has been written coma separated
		$engineSerialsArr = explode(',', str_replace(' ', '',$flightInfo["engines"]));
		$engineSerial = $engineSerialsArr[$engineNum - 1];
		
		$Eng = new Engine();
		$Eng->CreateEngineDiscrepTable();
		
		foreach ($descrepNamedArr as $key => $val)
		{
			$discrepRow = $Eng->GetEngineDiscrep($engineSerial, $sliceCode, $fligthId, $key);
			
			//check it wasnt already calculated and inserted
			if(($discrepRow["engineSerial"] != $engineSerial) && 
					($discrepRow["flightId"] != $fligthId) && 
					($discrepRow["sliceCode"] != $sliceCode) && 
					($discrepRow["discrepCode"] != $key))
			{
				$Eng->InsertEngineDiscrep($engineSerial,
					$fligthId,
					$flightInfo["startCopyTime"],
					$sliceCode,
					$sliceInfo['id'],
					$key,
					$val);
			}
		}
		
		unset($Eng);
		unset($Sl);
	}

	public function PutScripts()
	{
		/*printf("<script language='javascript' type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
		 <script language='javascript' type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
		 <script language='javascript' type='text/javascript' src='scripts/proto/progressBar.proto.js'></script>
		 <script language='javascript' type='text/javascript' src='scripts/fileUploader.js'></script>");
		 */
	}

	public function PutFooter() 
	{
		printf("</body></html>");
	}
}
//=========================================================
//╔═══╦╗──────╔╗
//║╔═╗║║─────╔╝╚╗
//║║─╚╣╚═╦══╦╩╗╔╝
//║║─╔╣╔╗║╔╗║╔╣║
//║╚═╝║║║║╔╗║║║╚╗
//╚═══╩╝╚╩╝╚╩╝╚═╝
//=========================================================
class ChartView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'chartPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post, $get)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['flightId']) && isset($post['tpls']))
		{			
			$this->info['flightId'] = $post['flightId'];
			$this->info['tpls'] = $post['tpls'];

			$this->SetInfo();

			if(isset($post['startFrame']))
			{
				$this->info['startFrame'] = $post['startFrame'];
			}
			else
			{
				$this->info['startFrame'] = 0;
			}

			if(isset($post['endFrame']))
			{
				$this->info['endFrame'] = $post['endFrame'];
			}
			else
			{
				$this->info['endFrame'] = $this->info['framesCount'];
			}
			
			$this->info['fastView'] = false;
		}
		else if (isset($post['radioBut']))
		{			
			$this->info['flightId'] = $post['radioBut'];
			
			$this->SetInfo();
		
			if(($this->info['paramSetTemplateListTableName'] != '') &&
				($this->info['paramSetTemplateListTableName'] != ' '))
			{							
				$this->info['startFrame'] = 0;
				$this->info['endFrame'] = $this->info['framesCount'];
				$this->info['fastView'] = true;
			}
			else 
			{
				exit("No template to be viewed");
			}
		}
		else
		{
			exit("Flight or flight params not selected");
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Chart.php");
						error_log("No language object in file for current page. Chart.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Chart.php");
					error_log("No language object in file for current page. Chart.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Chart.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Chart.php");
				exit();
			}
		}
	}

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);

		$usedTpls = "";
		if(isset($this->info['tpls']) && ($this->info['tpls'] != null))
		{
			$usedTpls = $this->lang->tpl.": ".$this->info['tpls'];
		}

		printf("<title>%s: %s. %s: %s. %s: %s. %s</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate,
		$usedTpls);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutChartContainter()
	{
		print("<div id='graphContainer' class='Container' style='width:100%; border:0;'>
				<div id='placeholder'></div>
				<div id='legend'></div>
				</div>");
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){ }

	public function PutInfo()
	{
		printf("<input id='flightId' value='%s' type='hidden' />", 
		$this->info['flightId']);
		printf("<input id='username' type='hidden' value='%s' />", $this->username);
			$startCopyTime = $this->info['startCopyTime'] * 1000;
		printf("<input id='startCopyTime' value='%s' type='hidden' />", 
			$startCopyTime);
		printf("<input id='stepLength' value='%s' type='hidden' />", 
			$this->info['stepLength']);
		printf("<input id='startFrame' value='%s' type='hidden' />", 
			$this->info['startFrame']);
		printf("<input id='endFrame' value='%s' type='hidden' />", 
			$this->info['endFrame']);
		printf("<input id='startFrameTime' value='%s' type='hidden' />", 
			$this->info['startFrame'] * $this->info['stepLength'] + $this->info['startCopyTime']);
		printf("<input id='endFrameTime' value='%s' type='hidden' />", 
			$this->info['endFrame'] * $this->info['stepLength'] + $this->info['startCopyTime']);

		$pst = new PSTempl();
		
		if($this->info['fastView'] == true)
		{
			$this->info['tpls'] = $pst->GetDefaultTemplateName(
					$this->info['paramSetTemplateListTableName'], $this->username);
			
			//if no default try to use last
			if($this->info['tpls'] == "")
			{
				$this->info['tpls'] = $pst->GetLastTemplateName(
						$this->info['paramSetTemplateListTableName'], $this->username);
			}
		}
		
		printf("<input id='tplname' value='%s' type='hidden' />", 
			$this->info['tpls']);
		
		if($this->info['tpls'] != "")
		{
			$tpls = explode(",", $this->info['tpls']);
			$tpls = array_map("trim", $tpls);
			$tpls = array_filter($tpls);
			$selectedParams = array();
	
			foreach ($tpls as $elem => $item)
			{
				$selectedParamsForCurTpl = $pst->GetPSTParams(
					$this->info["paramSetTemplateListTableName"], 
					$item,
					$this->username);
				$selectedParams = array_merge($selectedParams, $selectedParamsForCurTpl);
			}
			
			$selectedParams = array_unique($selectedParams);
			$selectedParams = array_filter($selectedParams);
			$selectedParams = array_values($selectedParams);
	
			unset($pst);
	
			$selectedAp = array();
			$selectedBp = array();
	
			$Bru = new Bru();
			for($i = 0; $i < count($selectedParams); $i++)
			{
				$paramType = $Bru->GetParamType($selectedParams[$i],
				$this->info['gradiApTableName'], $this->info['gradiBpTableName']);
				if($paramType == PARAM_TYPE_AP)
				{
					array_push($selectedAp, $selectedParams[$i]);
				}
				else if($paramType == PARAM_TYPE_BP)
				{
					array_push($selectedBp, $selectedParams[$i]);
				}
			}
			unset($Bru);
	
			$apJson = json_encode($selectedAp);
			$bpJson = json_encode($selectedBp);
			printf("<textarea id=\"apParams\" type=\"text\" style=\"display:none; position:absolute; left:0px; top:0px;\">%s</textarea>", $apJson);
			printf("<textarea id=\"bpParams\" type=\"text\" style=\"display:none; position:absolute; left:0px; top:0px;\">%s</textarea>", $bpJson);
		}
		else
		{
			exit("No template to be viewed");
		}

	}

	// 	public function PutTable()
	// 	{
	// 		if(!(property_exists($this->lang, 'time')))
	// 		{
	// 			$this->lang->time = "Time";
	// 		}

	// 		printf("<div id='tableContainer'>
	// 				<table cellpadding='0' cellspacing='0' border='0' id='tableHolder'>
	// 				<thead><tr>");

	// 		$Bru = new Bru();
	// 		printf("<th>%s</th>", $this->lang->time);
	// 		for($i = 0; $i < count($selectedAp); $i++)
	// 		{
	// 			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiApTableName'],
	// 			$this->info['gradiApTableName'], PARAM_TYPE_AP,
	// 			$selectedAp[$i]);
	// 			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
	// 		}
	// 		for($i = 0; $i < count($selectedBp); $i++)
	// 		{
	// 			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiBpTableName'],
	// 			$this->info['gradiBpTableName'], PARAM_TYPE_BP,
	// 			$selectedBp[$i]);
	// 			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
	// 		}
	// 		printf("</tr></thead><tbody></tbody><tfoot>");

	// 		printf("</tr></thead><tbody></tbody><tfoot>");
	// 		for($i = 0; $i < count($selectedAp) + count($selectedAp) + 1; $i++)
	// 		{
	// 			printf("<th></th>");
	// 		}
	// 		printf("</tfoot></table></div>");
	// 	}

	public function PutInfoForm()
	{		
		printf("<div id='infoForm' title='%s' style='visibility:hidden;'>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>
				<p>%s</p>		
				</div>",
					$this->lang->chartControl,
					$this->lang->V_verticalVizir,
					$this->lang->H_horizontalLine,
					$this->lang->N_names,
					$this->lang->T_tabel,
					$this->lang->M_map,
					$this->lang->D_distribute,
					$this->lang->F_freezeVizir,
					$this->lang->E_exacly,
					$this->lang->L_labels,
					$this->lang->I_info,
					$this->lang->G_googleEarth,
					$this->lang->S_simulator,
					$this->lang->wheelToZoomX,
					$this->lang->shiftAndWheelToZoomY);
	}
	
	public function PutFormToOpenTable()
	{
		printf("<form id='openTableForm' action='table.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input name='startFrame' value='%s'/>
				<input name='endFrame' value='%s'/>
				<input name='tpls' value='%s'/>
				<input type='submit'/></form>",
		$this->info['flightId'],
		$this->info['startFrame'],
		$this->info['endFrame'],
		$this->info['tpls']);
	}
	
	public function PutFormToOpenMap()
	{
		printf("<form id='openMapForm' action='map.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input name='tpls' value='%s'/>
				<input type='submit'/></form>",
				$this->info['flightId'],
				$this->info['tpls']);
	}
	
	public function PutFormToOpenModel()
	{
		printf("<form id='openModelForm' action='model.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input type='submit'/></form>",
					$this->info['flightId']);
	}
	
	public function PutFormToOpenGoogleEarth()
	{
		printf("<form id='openGoogleEarthForm' action='googleEarth.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input type='submit'/></form>",
					$this->info['flightId']);
	}
	
	public function PutLoadingBox()
	{
		printf("<div id='loadingBox' class='LoadingBox' width='%s'>
				<img style='margin:0px auto 0px;' src='stylesheets/basicImg/loading.gif'/>
				</div>",'100%');
	}

	public function PutScripts()
	{
		printf("
			<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
			<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>

			<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/base64.js'></script>
			<script type='text/javascript' src='scripts/include/flot/canvas2image.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.saveAsImage.js'></script>
				
			<!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->

			<script type='text/javascript' src='scripts/include/dataTables/jquery.dataTables.min.js'></script>

			<script type='text/javascript' src='scripts/proto/gAxesWorker.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gException.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gLegend.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gParam.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gTable.proto.js'></script>
			<script type='text/javascript' src='scripts/chart.js'></script>");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}

}
//=========================================================
//╔═══╗──╔╗╔╗
//║╔══╝──║║║║
//║╚══╦══╣║║║╔══╦╗╔╗╔╦══╦═╗
//║╔══╣╔╗║║║║║╔╗║╚╝╚╝║║═╣╔╝
//║║──║╚╝║╚╣╚╣╚╝╠╗╔╗╔╣║═╣║
//╚╝──╚══╩═╩═╩══╝╚╝╚╝╚══╩╝
//=========================================================
class FollowerView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'followerPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post, $get)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['radioBut']))
		{
			$this->info['flightId'] = $post['radioBut'];

			$this->SetInfo();
		}
		else
		{
			exit("Flight not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Follower.php");
						error_log("No language object in file for current page. Follower.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Follower.php");
					error_log("No language object in file for current page. Follower.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Follower.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Follower.php");
				exit();
			}
		}
	}

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
		
		$this->info['startFrame'] = 0;
		$this->info['endFrame'] = $this->info['framesCount'];
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);

		printf("<title>%s: %s. %s: %s. %s: %s</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutChartContainter()
	{
		print("<div id='graphContainer' class='Container' style='width:100%; border:0;'>
				<div id='placeholder'></div>
				<div id='legend'></div>
				</div>");
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){}

	public function PutInfo()
	{
		printf("<input id='flightId' type='hidden' value='%s'>", $this->info['flightId']);
		$startCopyTime = $this->info['startCopyTime'] * 1000;
		printf("<input id='startCopyTime' type='hidden' value='%s'>", $startCopyTime);
		printf("<input id='stepLength' type='hidden' value='%s'>", $this->info['stepLength']);
		printf("<input id='startFrame' type='hidden' value='%s' />", $this->info['startFrame']);
		printf("<input id='endFrame' type='hidden' value='%s' />", $this->info['endFrame']);
		
		$pst = new PSTempl();
		$PSTListTableName = $this->info['paramSetTemplateListTableName'];
		$selectedParams = $pst->GetDefaultTemplateParams($PSTListTableName);
		$this->info['tpls'] = $pst->GetDefaultTemplateName($PSTListTableName);
		unset($pst);

		$selectedAp = array();
		$selectedBp = array();

		$Bru = new Bru();
		for($i = 0; $i < count($selectedParams); $i++)
		{
			$paramType = $Bru->GetParamType($selectedParams[$i],
			$this->info['gradiApTableName'], $this->info['gradiBpTableName']);
			if($paramType == PARAM_TYPE_AP)
			{
				array_push($selectedAp, $selectedParams[$i]);
			}
			else if($paramType == PARAM_TYPE_BP)
			{
				array_push($selectedBp, $selectedParams[$i]);
			}
		}
		unset($Bru);

		$apJson = json_encode($selectedAp);
		$bpJson = json_encode($selectedBp);
		printf("<textarea id=\"apParams\" type=\"text\" style=\"display:none;\">%s</textarea>", $apJson);
		printf("<textarea id=\"bpParams\" type=\"text\" style=\"display:none;\">%s</textarea>", $bpJson);

	}

	// 	public function PutTable()
	// 	{
	// 		if(!(property_exists($this->lang, 'time')))
	// 		{
	// 			$this->lang->time = "Time";
	// 		}

	// 		printf("<div id='tableContainer'>
	// 				<table cellpadding='0' cellspacing='0' border='0' id='tableHolder'>
	// 				<thead><tr>");

	// 		$Bru = new Bru();
	// 		printf("<th>%s</th>", $this->lang->time);
	// 		for($i = 0; $i < count($selectedAp); $i++)
	// 		{
	// 			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiApTableName'],
	// 			$this->info['gradiApTableName'], PARAM_TYPE_AP,
	// 			$selectedAp[$i]);
	// 			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
	// 		}
	// 		for($i = 0; $i < count($selectedBp); $i++)
	// 		{
	// 			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiBpTableName'],
	// 			$this->info['gradiBpTableName'], PARAM_TYPE_BP,
	// 			$selectedBp[$i]);
	// 			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
	// 		}
	// 		printf("</tr></thead><tbody></tbody><tfoot>");

	// 		printf("</tr></thead><tbody></tbody><tfoot>");
	// 		for($i = 0; $i < count($selectedAp) + count($selectedAp) + 1; $i++)
	// 		{
	// 			printf("<th></th>");
	// 		}
	// 		printf("</tfoot></table></div>");
	// 	}

	public function PutFormToOpenTable()
	{
		printf("<form id='openTableForm' action='table.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input name='tpls' value='%s'/>
				<input type='submit'/></form>",
		$this->info['flightId'],
		$this->info['tpls']);
	}
	
	public function PutFormToOpenMap()
	{
		printf("<form id='openMapForm' action='map.php'  target='_blank'
				method='post' enctype='multipart/form-data'
				style='visibility:hidden;'>
				<input name='flightId' value='%s'/>
				<input name='tpls' value='%s'/>
				<input type='submit'/></form>",
				$this->info['flightId'],
				$this->info['tpls']);
	}

	public function PutScripts()
	{
		printf("
			<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
			<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>

			<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>
			<!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->

			<script type='text/javascript' src='scripts/include/dataTables/jquery.dataTables.min.js'></script>

			<script type='text/javascript' src='scripts/proto/gAxesWorker.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gException.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gLegend.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gParam.proto.js'></script>
			<script type='text/javascript' src='scripts/proto/gTable.proto.js'></script>
			<script type='text/javascript' src='scripts/follower.js'></script>
			");

	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
}
//=========================================================
//╔═╗╔═╗
//║║╚╝║║
//║╔╗╔╗╠══╦══╗
//║║║║║║╔╗║╔╗║
//║║║║║║╔╗║╚╝║
//╚╝╚╝╚╩╝╚╣╔═╝
//────────║║
//────────╚╝
//=========================================================
class MapView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'mapPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post, $get)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['flightId']) && isset($post['tpls']))
		{
			$this->info['flightId'] = $post['flightId'];

			$this->SetInfo();

			if(isset($post['startFrame']))
			{
				$this->info['startFrame'] = $post['startFrame'];
			}
			else
			{
				$this->info['startFrame'] = 0;
			}

			if(isset($post['endFrame']))
			{
				$this->info['endFrame'] = $post['endFrame'];
			}
			else
			{
				$this->info['endFrame'] = $this->info['framesCount'];
			}

		}
		else
		{
			exit("Flight or flight params not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Map.php");
						error_log("No language object in file for current page. Map.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Map.php");
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Map.php");
				exit();
			}
		}
	}	

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);

		printf("<title>%s: %s. %s: %s. %s: %s</title>",
				$this->lang->bort, $bort,
				$this->lang->voyage, $voyage,
				$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutMapContainter()
	{
		print("<div id='map_canvas' class='MapCanvas'></div>");
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){
	}

	public function PutInfo()
	{
		printf("<input id='flightId' type='hidden' value='%s'>", $this->info['flightId']);
		printf("<input id='startFrame' type='hidden' value='%s' />", $this->info['startFrame']);
		printf("<input id='endFrame' type='hidden' value='%s' />", $this->info['endFrame']);
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false'></script>
				<script type='text/javascript' src='scripts/proto/gCoordinate.proto.js'></script>
				<script type='text/javascript' src='scripts/map.js'></script>");
	}
	
	public function PutFooter() 
	{
		printf("</body></html>");
	}
}
//=========================================================
//╔═╗╔═╗────╔╗──╔╗
//║║╚╝║║────║║──║║
//║╔╗╔╗╠══╦═╝╠══╣║
//║║║║║║╔╗║╔╗║║═╣║
//║║║║║║╚╝║╚╝║║═╣╚╗
//╚╝╚╝╚╩══╩══╩══╩═╝
//=========================================================
class ModelView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'modelPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post, $get)
	{
		$this->GetLanguage();

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();

		if(isset($post['flightId']) || isset($get['flightId']))
		{
			if(isset($post['flightId']))
			{
				$this->info['flightId'] = $post['flightId'];
			}
			else
			{
				$this->info['flightId'] = $get['flightId'];
			}

			$this->SetInfo();

			if(isset($post['startFrame']))
			{
				$this->info['startFrame'] = $post['startFrame'];
			}
			else
			{
				$this->info['startFrame'] = 0;
			}

			if(isset($post['endFrame']))
			{
				$this->info['endFrame'] = $post['endFrame'];
			}
			else
			{
				$this->info['endFrame'] = $this->info['framesCount'];
			}

		}
		else
		{
			exit("Flight or flight params not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);

		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Map.php");
						error_log("No language object in file for current page. Map.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Map.php");
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Map.php");
				exit();
			}
		}
	}

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}

	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}

	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>

			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
		$this->lang->userName,
		$this->lang->pass,
		$this->lang->rememberMe,
		ulNonce::Create('login'),

		$this->lang->login);

		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);


		printf("<title>%s: %s. %s: %s. %s: %s.</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}

	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{
		printf("<body style='overflow:hidden; padding:0px; margin:0px;'>");
	}

	public function PutMainMenu() { }

	public function PutInfo()
	{
		printf("<input id='flightId' type='hidden' value='%s'>", $this->info['flightId']);
		printf("<input id='startFrame' type='hidden' value='%s' />", $this->info['startFrame']);
		printf("<input id='endFrame' type='hidden' value='%s' />", $this->info['endFrame']);
		printf("<input id='stepLenght' type='hidden' value='%s' />", $this->info['stepLength']);
	}
	
	public function PutModelControl()
	{
		print("<div id='toolbar' class='ui-widget-header ui-corner-all'>
				
				<button id='play'>play</button>
 				<button id='stop'>stop</button>&nbsp;
				<div style='display: inline-block; 
						position:relative; 
						margin-left:10px; 
						padding-top:3px;
						width:90%;'>
					<div id='slider'></div>
				</div>
				</div>");
	}
	
	public function PutModelContainter()
	{
		print("<div id='model' class='Model'></div>");
	}
	
	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				
				<script type='text/javascript' src='scripts/include/three/three.min.js'></script>
				<script type='text/javascript' src='scripts/include/three/Detector.js'></script>
				<script type='text/javascript' src='scripts/include/three/ColladaLoader.js'></script>
				
				<script type='text/javascript' src='scripts/proto/gCoordinate.proto.js'></script>
				<script type='text/javascript' src='scripts/model.js'></script>");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
}
//=========================================================
//╔═══╗────────╔╗───╔═══╗────╔╗╔╗
//║╔═╗║────────║║───║╔══╝───╔╝╚╣║
//║║─╚╬══╦══╦══╣║╔══╣╚══╦══╦╩╗╔╣╚═╗
//║║╔═╣╔╗║╔╗║╔╗║║║║═╣╔══╣╔╗║╔╣║║╔╗║
//║╚╩═║╚╝║╚╝║╚╝║╚╣║═╣╚══╣╔╗║║║╚╣║║║
//╚═══╩══╩══╩═╗╠═╩══╩═══╩╝╚╩╝╚═╩╝╚╝
//──────────╔═╝║
//──────────╚══╝
//=========================================================
class GoogleEarthView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'googleEarthPage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post, $get)
	{
		$this->GetLanguage();

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();

		if(isset($post['flightId']) || isset($get['flightId']))
		{
			if(isset($post['flightId']))
			{
				$this->info['flightId'] = $post['flightId'];
			}
			else
			{
				$this->info['flightId'] = $get['flightId'];
			}

			$this->SetInfo();

			if(isset($post['startFrame']))
			{
				$this->info['startFrame'] = $post['startFrame'];
			}
			else
			{
				$this->info['startFrame'] = 0;
			}

			if(isset($post['endFrame']))
			{
				$this->info['endFrame'] = $post['endFrame'];
			}
			else
			{
				$this->info['endFrame'] = $this->info['framesCount'];
			}

		}
		else
		{
			exit("Flight or flight params not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);

		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Map.php");
						error_log("No language object in file for current page. Map.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Map.php");
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Map.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Map.php");
				exit();
			}
		}
	}

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}

	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}

	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>

			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
		$this->lang->userName,
		$this->lang->pass,
		$this->lang->rememberMe,
		ulNonce::Create('login'),

		$this->lang->login);

		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}
	
	public function PutGoogleEarthScrSrc()
	{
		printf("<script type='text/javascript' src='https://www.google.com/jsapi'></script>");
	}
	

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);


		printf("<title>%s: %s. %s: %s. %s: %s.</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}

	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{
		printf("<body style='overflow:hidden; padding:0px; margin:0px;'>");
	}

	public function PutMainMenu() { }

	public function PutInfo()
	{
		printf("<input id='flightId' type='hidden' value='%s'>", $this->info['flightId']);
		printf("<input id='startFrame' type='hidden' value='%s' />", $this->info['startFrame']);
		printf("<input id='endFrame' type='hidden' value='%s' />", $this->info['endFrame']);
		printf("<input id='stepLenght' type='hidden' value='%s' />", $this->info['stepLength']);
	}

	public function PutModelControl()
	{
		print("<div id='toolbar' class='ui-widget-header ui-corner-all'>

				<button id='play'>play</button>
 				<button id='stop'>stop</button>&nbsp;
				<div style='display: inline-block;
						position:relative;
						margin-left:10px;
						padding-top:3px;
						width:90%;'>
					<div id='slider'></div>
				</div>
				</div>");
	}

	public function PutPluginContainer()
	{
		print("<div id='map3d' style='height: 400px; width: 600px;'></div>");
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>

				<script type='text/javascript' src='scripts/proto/gCoordinate.proto.js'></script>
				<script type='text/javascript' src='scripts/earth.js'></script>");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}
}
//=========================================================
//╔════╗─╔╗─╔╗
//║╔╗╔╗║─║║─║║
//╚╝║║╠╩═╣╚═╣║╔══╗
//──║║║╔╗║╔╗║║║║═╣
//──║║║╔╗║╚╝║╚╣║═╣
//──╚╝╚╝╚╩══╩═╩══╝
//=========================================================
class TableView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'tablePage';
	private $ulogin;
	public $lang;
	private $info;
	private $username;
	public $privilege;

	function __construct($post)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['flightId']) && isset($post['tpls']))
		{
			$this->info['flightId'] = $post['flightId'];
			$this->info['tpls'] = $post['tpls'];

			$this->SetInfo();

			if(isset($post['startFrame']))
			{
				$this->info['startFrame'] = $post['startFrame'];
			}
			else
			{
				$this->info['startFrame'] = 0;
			}

			if(isset($post['endFrame']))
			{
				$this->info['endFrame'] = $post['endFrame'];
			}
			else
			{
				$this->info['endFrame'] = $this->info['framesCount'];
			}
		}
		else
		{
			exit("Flight or flight options not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Table.php");
						error_log("No language object in file for current page. Table.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Table.php");
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Table.php");
				exit();
			}
		}
	}	

	private function SetInfo()
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($this->info['flightId']);
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		$this->info = array_merge($this->info, $flightInfo, $bruInfo);

		$Frame = new Frame();
		$this->info['framesCount'] = $Frame->GetFramesCount($this->info['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		$bort = $this->info['bort'];
		$voyage = $this->info['voyage'];
		$copyDate = date('d-m-y', $this->info['startCopyTime']);

		$usedTpls = "";
		if($this->info['tpls'] != null)
		{
			$usedTpls = $this->lang->tpl.": ".$this->info['tpls'];
		}

		printf("<title>%s: %s. %s: %s. %s: %s. %s</title>",
		$this->lang->bort, $bort,
		$this->lang->voyage, $voyage,
		$this->lang->flightDate, $copyDate,
		$usedTpls);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery.dataTables.css' rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){
	}

	public function PutInfo()
	{
		$username = $this->username;
		printf("<input id='flightId' type='hidden' value='%s'>", $this->info['flightId']);
		$startCopyTime = $this->info['startCopyTime'] * 1000;
		printf("<input id='startCopyTime' type='hidden' value='%s'>", $startCopyTime);
		printf("<input id='stepLength' type='hidden' value='%s'>", $this->info['stepLength']);
		printf("<input id='startFrame' type='hidden' value='%s' />", $this->info['startFrame']);
		printf("<input id='endFrame' type='hidden' value='%s' />", $this->info['endFrame']);
		printf("<input id='totalFramesCount' type='hidden' value='%s' />", $this->info['framesCount']);
		printf("<input id='stepDivider' type='hidden' value='%s'>", $this->info['stepDivider']);

		$Pst = new PSTempl();
		$tpls = explode(",", $this->info['tpls']);
		$tpls = array_map("trim", $tpls);
		$tpls = array_filter($tpls);
		$selectedParams = array();

		foreach ($tpls as $elem => $item)
		{
			$selectedParamsForCurTpl = $Pst->GetPSTParams($this->info['paramSetTemplateListTableName'], $item, $username);
			$selectedParams = array_merge($selectedParams, $selectedParamsForCurTpl);
		}
		$selectedParams = array_unique($selectedParams);
		$selectedParams = array_filter($selectedParams);
		$selectedParams = array_values($selectedParams);
		unset($Pst);

		$selectedAp = array();
		$selectedBp = array();

		$Bru = new Bru();
		for($i = 0; $i < count($selectedParams); $i++)
		{
			$paramType = $Bru->GetParamType($selectedParams[$i],
			$this->info['gradiApTableName'], $this->info['gradiBpTableName']);
			if($paramType == PARAM_TYPE_AP)
			{
				array_push($selectedAp, $selectedParams[$i]);
			}
			else if($paramType == PARAM_TYPE_BP)
			{
				array_push($selectedBp, $selectedParams[$i]);
			}
		}
		unset($Bru);

		$this->info["selectedAp"] = $selectedAp;
		$this->info["selectedBp"] = $selectedBp;

		$apJson = json_encode($selectedAp);
		$bpJson = json_encode($selectedBp);
		printf("<textarea id=\"apParams\" type=\"text\" style=\"display:none;\">%s</textarea>", $apJson);
		printf("<textarea id=\"bpParams\" type=\"text\" style=\"display:none;\">%s</textarea>", $bpJson);
	}

	public function PutTable()
	{

		$selectedAp = $this->info["selectedAp"];
		$selectedBp = $this->info["selectedBp"];

		printf("<div id='tableContainer' align='center'>
				<table cellpadding='0' cellspacing='0' border='0' id='tableHolder'>
				<thead><tr>");

		$Bru = new Bru();
		printf("<th>%s</th>", $this->lang->time);
		for($i = 0; $i < count($selectedAp); $i++)
		{
			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiApTableName'],
			$this->info['gradiApTableName'], $selectedAp[$i], PARAM_TYPE_AP);
			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
		}
		for($i = 0; $i < count($selectedBp); $i++)
		{
			$paramInfo = $Bru->GetParamInfoByCode($this->info['gradiApTableName'],
			$this->info['gradiBpTableName'], $selectedBp[$i], PARAM_TYPE_BP);
			printf("<th>%s (%s)</th>", $paramInfo['name'], $paramInfo['code']);
		}
		printf("</tr></thead><tfoot><tr>");

		for($i = 0; $i < count($selectedAp) + count($selectedBp) + 1; $i++)
		{
			printf("<th></th>");
		}
		printf("</tr></tfoot><tbody></tbody></table></div>");
	}

	public function PutScripts()
	{
		printf("

				<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>

				<script type='text/javascript' src='scripts/include/dataTables/jquery.dataTables.min.js'></script>
				<script type='text/javascript' src='scripts/include/dataTables/dataTables.fixedHeader.min.js'></script>

				<script type='text/javascript' src='scripts/proto/gTable.proto.js'></script>
				<script type='text/javascript' src='scripts/table.js'></script>");

	}

	public function PutFooter() 
	{
		printf("</body></html>");
	}

}

//=========================================================
//╔═══╗───────────────╔╗
//╚╗╔╗║──────────────╔╝╚╗
//─║║║╠╦══╦══╦═╗╔══╦═╩╗╔╬╦══╗
//─║║║╠╣╔╗║╔╗║╔╗╣╔╗║══╣║╠╣╔═╝
//╔╝╚╝║║╔╗║╚╝║║║║╚╝╠══║╚╣║╚═╗
//╚═══╩╩╝╚╩═╗╠╝╚╩══╩══╩═╩╩══╝
//────────╔═╝║
//────────╚══╝
//=========================================================
class DiagnosticView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'diagnosticPage';
	private $ulogin;
	public $lang;
	private $info;
	public $action;
	private $username;
	public $privilege;

	function __construct($post)
	{
		$this->GetLanguage();
		
		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		
		if(isset($post['engines']) && isset($post['etalonId']) && isset($post['engineAction']))
		{	
			$pos = strrpos($post['engines'], ",");
			if ($pos === false) 
			{ 
				$this->info['engineSerial'] = (array)$post['engines'];
			}
			else 
			{
				$this->info['engineSerial'] = explode(",", $post['engines']);
			}
			
			$this->info['etalonId'] = $post['etalonId'];
			$this->action = $post['engineAction'];
			
			//error_log($this->info['engineSerial'] . " " . $this->info['etalonId']);
		}
		else if(isset($post['etalonId']) && isset($post['engineAction']))
		{		
			$this->info['etalonId'] = $post['etalonId'];
			$this->action = $post['engineAction'];
			
			$Eng = new Engine();
			$etalonsEngineSerialPairs = (array)$Eng->SelectEnginesSerialsByEtalonsList();
			unset($Eng);
			
			//select first available
			$this->info['engineSerial'] = (array)$etalonsEngineSerialPairs[$this->info['etalonId']][0];
				
			//error_log($this->info['engineSerial'] . " " . $this->info['etalonId']);
		}
		else
		{
			exit("Engine not selected");
			exit();
		}
	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);
		
		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Diagnostic.php");
						error_log("No language object in file for current page. Diagnostic.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Diagnostic.php");
					error_log("No language object in file for current page. Diagnostic.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Diagnostic.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Diagnostic.php");
				exit();
			}
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>
		
			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
			$this->lang->userName,
			$this->lang->pass,
			$this->lang->rememberMe,
			ulNonce::Create('login'),
	
			$this->lang->login);
	
		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		printf("<title>%s: %s</title>",
		$this->lang->engineSerial, implode(",", $this->info['engineSerial']));
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css'
				rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu(){ }
	
	public function PutDiagnosticReport()
	{
		//accordion open
		printf("<h3 class='diagnosticAccordionHeader'>%s</h3><div class='diagnosticAccordion'>",
			$this->lang->accordionReport);
		
		$Eng = new Engine();
		$etalonsEngineSerialPairs = (array)$Eng->SelectEnginesSerialsByEtalonsList();
		unset($Eng);
		
		//===================================================================
		//etalons id
		//===================================================================
		$Sl = new Slice();
		$etalonNamesOptionList = "<select id='etalonListReport' 
				multiline size='5' style='width:230px; margin:5px;'>";
		$curSliceInfo = $Sl->GetSliceInfo($this->info['etalonId']);
		$selectedEtalonName = $curSliceInfo['name'];
		$etalonNamesOptionList .= "<option data-etalonid='".$this->info['etalonId']."' selected='true'>".$curSliceInfo['name']."</option>";
		foreach ($etalonsEngineSerialPairs as $etalonId => $curEngSerial)
		{
			if($etalonId != $this->info['etalonId'])
			{
				$curSliceInfo = $Sl->GetSliceInfo($etalonId);
				$etalonNamesOptionList .= "<option data-etalonid='".$etalonId."'>".$curSliceInfo['name']."</option>";
			}
		}
		$etalonNamesOptionList .= "<option data-etalonid='".DIAGNOSTIC_IGNORE_ETALON."'>".$this->lang->etalonIgnore."</option>";
		$etalonNamesOptionList .= "</select>";
		
		//===================================================================
		//engineSerialList
		//===================================================================		
		$engineSerialsList = $etalonsEngineSerialPairs[$this->info['etalonId']];
		
		$engineSerialOptionList = "<select id='engineSerialListReport' 
				multiline size='5' multiple style='width:230px; margin:5px;'>";
		
		$engineSerialsSentArr = array_map('trim', $this->info['engineSerial']);

		foreach ($engineSerialsList as $curEngSerial)
		{
			$foundInConvertedEngines = false;
			foreach($engineSerialsSentArr as $convertedSerial)
			{
				error_log("convertedSerial " . $convertedSerial);
				error_log("curEngSerial " . $curEngSerial);
				if($convertedSerial == $curEngSerial)
				{
					$foundInConvertedEngines = true;
				}
			}
			
			if($foundInConvertedEngines)
			{
				error_log("in array " . $curEngSerial);
				$engineSerialOptionList .= "<option selected='true'>".$curEngSerial."</option>";
			}
			else 
			{
				error_log("not " . $curEngSerial);
				$engineSerialOptionList .= "<option>".$curEngSerial."</option>";
			}
		}
		$engineSerialOptionList .= "</select>";
		
		//===================================================================
		//discrep limits
		//===================================================================
		
		$discrepLimitsList = "<select id='limitsReport' 
				multiline size='5' multiple style='width:230px; margin:5px;'>";

		$discrepLimitsList .= "<option data-type='0' title='".$this->lang->limitType0."'>".$this->lang->limitType0."</option>";
		$discrepLimitsList .= "<option data-type='1' title='".$this->lang->limitType1."'>".$this->lang->limitType1."</option>";
		$discrepLimitsList .= "<option data-type='2' selected='true' title='".$this->lang->limitType2."'>".$this->lang->limitType2."</option>";
		$discrepLimitsList .= "<option data-type='3' selected='true' title='".$this->lang->limitType3."'>".$this->lang->limitType3."</option>";

		$discrepLimitsList .= "</select>";
		
		//===================================================================
		//report table
		//===================================================================
		
		$reportTable = "<table id='ReportTable' class='ReportTable'>
				<tr class='ReportTableHeader'>
					<td>".$this->lang->reportFlightDate."</td>
					<td>".$this->lang->reportEtalon."</td>
					<td>".$this->lang->reportEngine."</td>
					<td>".$this->lang->reportSlice."</td>
					<td>".$this->lang->reportDiscrep."</td>
					<td>".$this->lang->reportDiscrepVal."</td>
					<td>".$this->lang->reportLimitType."</td>
					<td>".$this->lang->reportLimits."</td>
					<td>".$this->lang->reportComment."</td>
				</tr>
				</table>";
		
		$serviceTableBox = "<div id='reportTableMessage' " .
				"style='width:100%; text-align:center; margin-top:20px;'>".$this->lang->uploadingData."</div>";
		
		//===================================================================
		//MenuContaider
		//===================================================================		
		printf("<table><tr><td style='width:250px; vertical-align:top;'><div class='ReportMenuContaider'>
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				<input id='fromDate' type='text' style='width:230px; margin:5px;'/ >
				<label class='SmallLabel'>%s</label>
				<input id='toDate' type='text' style='width:230px; margin:5px;'/ >
				</br></br>
				</div></td>",
					$this->lang->etalonName, 
					$etalonNamesOptionList,
					$this->lang->engineSerialList,
					$engineSerialOptionList,
					$this->lang->reportDiscrepLimits,
					$discrepLimitsList,
					$this->lang->reportFromDate,
					$this->lang->reportToDate
		);
		
		//===================================================================
		//reportContaider
		//===================================================================
		
		printf("<td style='width:%s; vertical-align:top;'><div class='ReportContaider'>%s
				</div>%s</td></tr></table>", "100%", $reportTable, $serviceTableBox);

		//accordion close
		printf("</div>");
		
	}
	
	public function PutDiagnosticLog()
	{
		//accordion open
		printf("<h3 class='diagnosticAccordionHeader'>%s</h3><div class='diagnosticAccordion'>",
		$this->lang->accordionLog);
	
		printf("</br>Хроника</br>");
		//accordion close
		printf("</div>");
	
	}

	public function PutDiagnosticContainer()
	{
		//accordion open
		printf("<h3 class='diagnosticAccordionHeader'>%s</h3><div class='diagnosticAccordion'>",
			$this->lang->accordionDiagnostic);

		$Eng = new Engine();
		$etalonsEngineSerialPairs = (array)$Eng->SelectEnginesSerialsByEtalonsList();
		unset($Eng);
		
		//===================================================================
		//etalons id
		//===================================================================
		$Sl = new Slice();
		$etalonNamesOptionList = "<select id='etalonList' style='width:230px; margin:5px;'>";
		$curSliceInfo = $Sl->GetSliceInfo($this->info['etalonId']);
		$selectedEtalonName = $curSliceInfo['name'];
		
		foreach ($etalonsEngineSerialPairs as $etalonId => $curEngSerial)
		{
			if($etalonId != $this->info['etalonId'])
			{
				$curSliceInfo = $Sl->GetSliceInfo($etalonId);
				$etalonNamesOptionList .= "<option data-etalonid='".$etalonId."'>".$curSliceInfo['name']."</option>";
			}
			else 
			{
				$etalonNamesOptionList .= "<option data-etalonid='".$this->info['etalonId']."' selected='true'>".
					$curSliceInfo['name']."</option>";
			}
		}
		$etalonNamesOptionList .= "<option data-etalonid='".DIAGNOSTIC_IGNORE_ETALON."'>".$this->lang->etalonIgnore."</option>";
		$etalonNamesOptionList .= "</select>";
		
		//===================================================================
		//engineSerialList
		//===================================================================		
		$engineSerialsList = $etalonsEngineSerialPairs[$this->info['etalonId']];
		
		$engineSerialOptionList = "<select id='engineSerialList' style='width:230px; margin:5px;'>";
		foreach ($engineSerialsList as $curEngSerial)
		{
			if($curEngSerial != $this->info['engineSerial'][0])
			{
				$engineSerialOptionList .= "<option>".$curEngSerial."</option>";
			}
			else 
			{
				$engineSerialOptionList .= "<option selected='true'>".$this->info['engineSerial'][0]."</option>";
			}
		}
		$engineSerialOptionList .= "</select>";
		
		//===================================================================
		//engineSlicesList
		//===================================================================
		
		$Eng = new Engine();
		$engineSerialInfo = $Eng->GetEngineInfoBySerialAndEtalon($this->info['etalonId'], $this->info['engineSerial'][0]);
		unset($Eng);
		
		$engineSlicesOptionList = "<select id='engineSlicesList' style='width:230px; margin:5px;'>";
		$engineSlicesList = explode(", ",$engineSerialInfo["sliceCode"] );
		foreach ($engineSlicesList as $curEngSlice)
		{
			$engineSlicesOptionList .= "<option>".$curEngSlice."</option>";
		}
		$engineSlicesOptionList .= "</select>";
		
		//===================================================================
		//engineAbscissaList (X) engineOrdinateList (Y)
		//===================================================================
		
		$Eng = new Engine();
		$engineDiscrepList = $Eng->GetEngineDiscrepsBySlices($this->info['engineSerial'][0], $engineSlicesList[0], $this->info['etalonId']);
		unset($Eng);
		
		$chartDiscrepAbscissa = "<select id='engineDiscrepAbscissa' style='width:230px; margin:5px;'>";
		//$chartDiscrepOrdinate = "<select id='engineDiscrepOrdinate' multiple size='5' style='width:230px; margin:5px;'>";
		$chartDiscrepOrdinate = "<select id='engineDiscrepOrdinate' multiline size='5' style='width:230px; margin:5px;'>";
		
		//service option. Should carefully managed in js
		$chartDiscrepAbscissa .= "<option data-name='".DIAGNOSTIC_ABSCISSA_FLIGHTS."'>".$this->lang->flights."</option>";
		
		for($i = 0; $i < count($engineDiscrepList); $i++)
		{
			if($i == 0)
			{
				//$chartDiscrepAbscissa .= "<option data-name='".$curEngDiscrep."'>".$curEngDiscrep."</option>";
				$chartDiscrepOrdinate .= "<option data-name='".$engineDiscrepList[$i]."' selected='true'>".$engineDiscrepList[$i]."</option>";
			}
			else 
			{
				//$chartDiscrepAbscissa .= "<option data-name='".$curEngDiscrep."'>".$curEngDiscrep."</option>";
				$chartDiscrepOrdinate .= "<option data-name='".$engineDiscrepList[$i]."'>".$engineDiscrepList[$i]."</option>";
			}
		}
		$chartDiscrepAbscissa .= "</select>";
		
		//===================================================================
		//discrepChartList
		//===================================================================
		
		$chartList = "<select id='chartList' multiline size='7' style='width:230px; margin:5px;'>";
		
		if(count($engineDiscrepList) > 0)
		{
			$chartDescr = $this->lang->flights . " - " . $engineDiscrepList[0] . " / " . $this->info['engineSerial'][0] . " / " . $engineSlicesList[0]  . " / " . $selectedEtalonName;
			$chartList .= "<option title='" . $chartDescr . "' selected='true' data-placeholderid='0'" . 
				"data-slicename='" . $selectedEtalonName . "' " .
				"data-etalonid='" . $this->info['etalonId'] . "' " .
				"data-engineserial='" . $this->info['engineSerial'][0] . "' " .
				"data-slice='" . $engineSlicesList[0] . "' " .
				"data-abscissa='" . DIAGNOSTIC_ABSCISSA_FLIGHTS . "' " .
				"data-ordinate='" . $engineDiscrepList[0] . "' " .
			">" . $chartDescr . "</option>";
		}

		$chartList .= "</select>";
		
		//===================================================================
		//MenuContaider
		//===================================================================
		
		printf("<table><tr><td style='width:250px; vertical-align:top;'><div class='DiagnosticMenuContaider'>
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<label class='SmallLabel'>%s</label>
				%s
				<input id='addChart' type='button' value='%s' style='width:230px; margin:5px;'></input>
				<label class='SmallLabel'>%s</label>
				%s
				<input id='delChart' type='button' value='%s' style='width:230px; margin:5px;'></input>
				<input id='markingOptions' type='button' value='%s' style='width:230px; margin:5px;'></input>
				</div></td>",
					$this->lang->etalonName, 
					$etalonNamesOptionList,
					$this->lang->engineSerialList,
					$engineSerialOptionList,
					$this->lang->engineSlicesList,
					$engineSlicesOptionList,
					$this->lang->engineDiscrepAbscissa,
					$chartDiscrepAbscissa,
					$this->lang->engineDiscrepOrdinate,
					$chartDiscrepOrdinate,
					$this->lang->addChart,
					$this->lang->discrepChartList,
					$chartList,
					$this->lang->delChart,
					$this->lang->editMarkings
		);
		
		printf("<td style='width:%s; vertical-align:top;'><div class='DiagnosticChartContaider'></div></td></tr></table>", "100%");
		
		//accordion close
		printf("</div>");
		
		unset($Sl);
	}
	
	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}
		
	public function DeleteEngine()
	{
		$Eng = new Engine();
		$Eng->DeleteEngineDiscrep($this->info['etalonId'], $this->info['engineSerial'][0]);
		unset($Eng);		
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
				<script type='text/javascript' src='scripts/include/jquery/jquery.ui.datepicker-ru.js'></script>
				
				<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
				<script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
				<script type='text/javascript' src='scripts/diagnostic.js'></script>");
	}

	public function PutFooter() 
	{
		printf("</body></html>");
	}

}


//=========================================================
//╔╗─╔╗───────╔═╗╔═╗
//║║─║║───────║║╚╝║║
//║║─║╠══╦══╦═╣╔╗╔╗╠══╦═╗╔══╦══╦══╦═╗
//║║─║║══╣║═╣╔╣║║║║║╔╗║╔╗╣╔╗║╔╗║║═╣╔╝
//║╚═╝╠══║║═╣║║║║║║║╔╗║║║║╔╗║╚╝║║═╣║
//╚═══╩══╩══╩╝╚╝╚╝╚╩╝╚╩╝╚╩╝╚╩═╗╠══╩╝
//──────────────────────────╔═╝║
//──────────────────────────╚══╝
//=========================================================
class UserManagerView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'userPage';
	private $ulogin;
	private $uloginMsg;
	public $lang;
	private $info;
	public $action;
	private $username;
	public $privilege;

	function __construct($post)
	{
		$this->GetLanguage();

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();

		if(isset($post['userAction']) && $post['userAction'] != null)
		{
			$this->action = $post['userAction'];
			$this->ulogin->uloginMsg = "";
			if(isset($post['userId']) && $post['userId'] != null)
			{
				$this->info->userId = $post['userId'];
			}
		}
		else
		{
			exit("Action is not set");
		}

	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);

		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Table.php");
						error_log("No language object in file for current page. Table.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Table.php");
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Table.php");
				exit();
			}
		}
	}

	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}

	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>

			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
		$this->lang->userName,
		$this->lang->pass,
		$this->lang->rememberMe,
		ulNonce::Create('login'),

		$this->lang->login);

		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		printf("<title>%s</title>",
			$this->lang->userCreation);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css' rel='stylesheet' type='text/css' />");
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{ 
		printf("<body>");
	}
	
	public function PutMainMenu() { }

	public function ShowUserCreationForm()
	{
		$Usr = new User();
		$privilege = $Usr->allPrivilegeArr;

		$privilegeOptions = '';
		foreach ($privilege as $val)
		{
			$privilegeOptions .= "<option id='privilege' data-privilege='".$val."'>".$val."</option>";
		}

		printf("<div align='center'><p class='Label'>%s</p>
			<label id='userCreationInfo' style='color:darkred;'>%s</label></br></br>
			<form>
			<table>
			<tr><td>%s</td><td>
				<input id='login' type='text' name='user' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='company' type='text' name='company' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='pwd1' type='password' name='pwd' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='pwd2' type='password' name='pwd2' size='50'>
			</td></tr>
			<tr><td>%s</td><td align='center'>
				<select id='privilege' multiple size='10' style='width: 335px'>%s<select>
			</td></tr>
			<tr><td>%s</td><td align='center'>
				<input id='mySubscriber' type='checkbox' name='mySubscriber' value='1'>
			</td></tr>
			<tr style='visibility:hidden;'><td>
				Nonce:
			</td><td>
				<input id='nonce' type='text' id='nonce' name='nonce' value='%s'>
			</td></tr>
		</table>
			<input align='center' id='createUserBut' class='Button' type='submit' value='%s'>
			</form>
			</div>", 
			$this->lang->userCreationForm,
			$this->ulogin->uloginMsg,
			$this->lang->userName,
			$this->lang->company,
			$this->lang->pass,
			$this->lang->repeatPass,
			$this->lang->userPrivilege,
			$privilegeOptions,
			$this->lang->userMySubscriber,
			ulNonce::Create('login'),
		 	$this->lang->userCreate);
		
		//==========================================
		//access to flights
		//==========================================
		if(in_array(PRIVILEGE_SHARE_FLIGHTS, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForFlights);
			
			$Fl = new Flight();
			$avaliableFlightIds = $Usr->GetAvaliableFlights($this->username);
			$avaliableFlights = $Fl->PrepareFlightsList($avaliableFlightIds);
					
			if(count($avaliableFlights) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableFlights) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
				
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
				
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bortNum,
						$this->lang->voyage,
						$this->lang->flightDate,
						$this->lang->bruTypeName,
						$this->lang->author,
						$this->lang->departureAirport,
						$this->lang->arrivalAirport,
						$this->lang->access);
				
				$greyHightLight = false;
				foreach ($avaliableFlights as $fligthInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
					
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='flightToAllowAccess' data-flightid='%s' type='checkbox'/>
							</td></tr>", 
						$fligthInfo['bort'],
						$fligthInfo['voyage'],
						$fligthInfo['flightDate'],
						$fligthInfo['bruType'],
						$fligthInfo['performer'],
						$fligthInfo['departureAirport'],
						$fligthInfo['arrivalAirport'],
						$fligthInfo['id']);
				}
				printf("</table>");
				
				if(count($avaliableFlights) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>", 
					$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Fl);
		}
		
		//==========================================
		//access to slices
		//==========================================
		if(in_array(PRIVILEGE_SHARE_SLICES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForSlices);
			
			$Sl = new Slice();
			$avaliableSliceIds = $Usr->GetAvaliableSlices($this->username);
			$avaliableSlices = $Sl->GetSliceList($avaliableSliceIds);
					
			if(count($avaliableSlices) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableSlices) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
				
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
				
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->sliceCode,
						$this->lang->sliceName,
						$this->lang->sliceCreationTime,
						$this->lang->sliceLastModifyTime,
						$this->lang->sliceAuthor,
						$this->lang->sliceIsEtalon,
						$this->lang->access);
				
				$greyHightLight = false;
				foreach ($avaliableSlices as $sliceInfo)
				{
					$isEtalon = "";
					if($sliceInfo['etalonTableName'] != "")
					{
						$isEtalon = "+";
					}
					
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
					
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='sliceToAllowAccess' data-sliceid='%s' type='checkbox'/>
							</td></tr>",
						$sliceInfo['code'],
						$sliceInfo['name'],
						$sliceInfo['creationTime'],
						$sliceInfo['lastModifyTime'],
						$sliceInfo['author'],
						$isEtalon,
						$sliceInfo['id']);
				}
				printf("</table>");
				
				if(count($avaliableSlices) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>", 
					$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Sl);
		}
		
		//==========================================
		//access to engines
		//==========================================
		if(in_array(PRIVILEGE_SHARE_ENGINES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForEngines);
				
			$avaliableEngines = $Usr->GetAvaliableEngines($this->username);
				
			if(count($avaliableEngines) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableEngines) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
		
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
		
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
							$this->lang->engineSerial,
							$this->lang->access);
				
				$greyHightLight = false;
				foreach ($avaliableEngines as $engineSerial)
				{						
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
						
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='engineDescrepToAllowAccess' data-enginedescrepid='%s' type='checkbox'/>
							</td></tr>",
									$engineSerial,
									$engineSerial);
				}
				printf("</table>");
		
				if(count($avaliableEngines) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Sl);
		}
		
		//==========================================
		//access to brutypes
		//==========================================
		if(in_array(PRIVILEGE_SHARE_BRUTYPES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForBruTypes);
		
			$Bru = new Bru();
			$avaliableIds = $Usr->GetAvaliableBruTypes($this->username);
			$avaliableBruTypes = $Bru->GetBruList($avaliableIds);
		
			if(count($avaliableBruTypes) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableBruTypes) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
		
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
		
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bruTypesName,
						$this->lang->bruTypesStepLenth,
						$this->lang->bruTypesFrameLength,
						$this->lang->bruTypesWordLength,
						$this->lang->bruTypesAuthor,
						$this->lang->access);
		
				$greyHightLight = false;
				foreach ($avaliableBruTypes as $bruTypeInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
		
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='bruTypeToAllowAccess' data-brutypeid='%s' type='checkbox'/>
							</td></tr>",
									$bruTypeInfo['bruType'],
									$bruTypeInfo['stepLength'],
									$bruTypeInfo['frameLength'],
									$bruTypeInfo['wordLength'],
									$bruTypeInfo['author'],
									$bruTypeInfo['id']);
				}
				printf("</table>");
		
				if(count($avaliableBruTypes) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Bru);
		}
		
		//==========================================
		//access to users
		//==========================================
		if(in_array(PRIVILEGE_SHARE_USERS, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForUsers);
		
			//$Usr = new User();
			$avaliableIds = $Usr->GetAvaliableUsers($this->username);
			$avaliableUsers = $Usr->GetUsersList($avaliableIds);
		
			if(count($avaliableUsers) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableUsers) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
		
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
		
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
							$this->lang->userLogin,
							$this->lang->userCompany,
							$this->lang->userAuthor,
							$this->lang->access);
		
				$greyHightLight = false;
				foreach ($avaliableUsers as $userInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
		
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='usersToAllowAccess' data-userid='%s' type='checkbox'/>
							</td></tr>",
									$userInfo['login'],
									$userInfo['company'],
									$userInfo['author'],
									$userInfo['id']);
				}
				printf("</table>");
		
				if(count($avaliableUsers) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
		}	

		printf("<input id='author' value='%s' style='visibility:hidden;'/>", $this->username);
		
		unset($Usr);
	}
	
	public function ShowUserEditingForm()
	{
		$Usr = new User();
		$privilege = $Usr->allPrivilegeArr;
	
		$username = $Usr->GetUserNameById($this->info->userId);
		$usrPrivilege = $Usr->GetUserPrivilege($username);
		$usrInfo = $Usr->GetUsersInfo($username);
		$privilegeOptions = '';
		foreach ($privilege as $val)
		{
			if(in_array($val, $usrPrivilege))
			{
				$privilegeOptions .= "<option id='privilege' data-privilege='".$val."' selected='selected'>".$val."</option>";
			}
			else 
			{
				$privilegeOptions .= "<option id='privilege' data-privilege='".$val."'>".$val."</option>";
			}
		}
	
		printf("<div align='center'><p class='Label'>%s</p>
			<label id='userCreationInfo' style='color:darkred;'>%s</label></br></br>
			<form>
			<table>
			<tr><td>%s</td><td>
				<input id='login' type='text' name='user' size='50' value='%s'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='company' type='text' name='company' size='50' value='%s'>
			</td></tr>
			
			<tr><td>%s</td><td align='center'>
				<select id='privilege' multiple size='10' style='width: 335px'>%s<select>
			</td></tr>
			<tr><td>%s</td><td align='center'>
				<input id='mySubscriber' type='checkbox' name='mySubscriber' value='1'>
			</td></tr>
			<tr style='visibility:hidden;'><td>
				Nonce:
			</td><td>
				<input id='nonce' type='text' id='nonce' name='nonce' value='%s'>
			</td></tr>
		</table>
			<input align='center' id='createUserBut' class='Button' type='submit' value='%s'>
			</form>
			</div>",
				$this->lang->userCreationForm,
				$this->ulogin->uloginMsg,
				$this->lang->userName,
				$usrInfo['login'],
				$this->lang->company,
				$usrInfo['company'],
				$this->lang->userPrivilege,
				$privilegeOptions,
				$this->lang->userMySubscriber,
				ulNonce::Create('login'),
				$this->lang->userCreate);
	
		//==========================================
		//access to flights
		//==========================================
		if(in_array(PRIVILEGE_SHARE_FLIGHTS, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForFlights);
				
			$Fl = new Flight();
			$avaliableFlightIds = $Usr->GetAvaliableFlights($this->username);
			$avaliableFlights = $Fl->PrepareFlightsList($avaliableFlightIds);
				
			if(count($avaliableFlights) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableFlights) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bortNum,
						$this->lang->voyage,
						$this->lang->flightDate,
						$this->lang->bruTypeName,
						$this->lang->author,
						$this->lang->departureAirport,
						$this->lang->arrivalAirport,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableFlights as $fligthInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
						
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='flightToAllowAccess' data-flightid='%s' type='checkbox'/>
							</td></tr>",
								$fligthInfo['bort'],
								$fligthInfo['voyage'],
								$fligthInfo['flightDate'],
								$fligthInfo['bruType'],
								$fligthInfo['performer'],
								$fligthInfo['departureAirport'],
								$fligthInfo['arrivalAirport'],
								$fligthInfo['id']);
				}
				printf("</table>");
	
				if(count($avaliableFlights) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Fl);
		}
	
		//==========================================
		//access to slices
		//==========================================
		if(in_array(PRIVILEGE_SHARE_SLICES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForSlices);
				
			$Sl = new Slice();
			$avaliableSliceIds = $Usr->GetAvaliableSlices($this->username);
			$avaliableSlices = $Sl->GetSliceList($avaliableSliceIds);
				
			if(count($avaliableSlices) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableSlices) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->sliceCode,
						$this->lang->sliceName,
						$this->lang->sliceCreationTime,
						$this->lang->sliceLastModifyTime,
						$this->lang->sliceAuthor,
						$this->lang->sliceIsEtalon,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableSlices as $sliceInfo)
				{
					$isEtalon = "";
					if($sliceInfo['etalonTableName'] != "")
					{
						$isEtalon = "+";
					}
						
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
						
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='sliceToAllowAccess' data-sliceid='%s' type='checkbox'/>
							</td></tr>",
								$sliceInfo['code'],
								$sliceInfo['name'],
								$sliceInfo['creationTime'],
								$sliceInfo['lastModifyTime'],
								$sliceInfo['author'],
								$isEtalon,
								$sliceInfo['id']);
				}
				printf("</table>");
	
				if(count($avaliableSlices) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Sl);
		}
	
		//==========================================
		//access to engines
		//==========================================
		if(in_array(PRIVILEGE_SHARE_ENGINES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForEngines);
	
			$avaliableEngines = $Usr->GetAvaliableEngines($this->username);
	
			if(count($avaliableEngines) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableEngines) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->engineSerial,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableEngines as $engineSerial)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
	
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='engineDescrepToAllowAccess' data-enginedescrepid='%s' type='checkbox'/>
							</td></tr>",
								$engineSerial,
								$engineSerial);
				}
				printf("</table>");
	
				if(count($avaliableEngines) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Sl);
		}
	
		//==========================================
		//access to brutypes
		//==========================================
		if(in_array(PRIVILEGE_SHARE_BRUTYPES, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForBruTypes);
	
			$Bru = new Bru();
			$avaliableIds = $Usr->GetAvaliableBruTypes($this->username);
			$avaliableBruTypes = $Bru->GetBruList($avaliableIds);
	
			if(count($avaliableBruTypes) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableBruTypes) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->bruTypesName,
						$this->lang->bruTypesStepLenth,
						$this->lang->bruTypesFrameLength,
						$this->lang->bruTypesWordLength,
						$this->lang->bruTypesAuthor,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableBruTypes as $bruTypeInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
	
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='bruTypeToAllowAccess' data-brutypeid='%s' type='checkbox'/>
							</td></tr>",
								$bruTypeInfo['bruType'],
								$bruTypeInfo['stepLength'],
								$bruTypeInfo['frameLength'],
								$bruTypeInfo['wordLength'],
								$bruTypeInfo['author'],
								$bruTypeInfo['id']);
				}
				printf("</table>");
	
				if(count($avaliableBruTypes) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
			unset($Bru);
		}
	
		//==========================================
		//access to users
		//==========================================
		if(in_array(PRIVILEGE_SHARE_USERS, $this->privilege))
		{
			printf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForUsers);
	
			//$Usr = new User();
			$avaliableIds = $Usr->GetAvaliableUsers($this->username);
			$avaliableUsers = $Usr->GetUsersList($avaliableIds);
	
			if(count($avaliableUsers) > 0)
			{
				//if more than 30 rows make table scrollable
				if(count($avaliableUsers) > 30)
				{
					printf("<div style='overflow-y:scroll; height:300px'>");
				}
	
				printf("<table width='%s' class='ExeptionsTable'>", "99%");
	
				printf("<tr class='ExeptionsTableHeader'>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell'>%s</td>
					<td class='ExeptionsCell' width='50px'>%s</td></tr>",
						$this->lang->userLogin,
						$this->lang->userCompany,
						$this->lang->userAuthor,
						$this->lang->access);
	
				$greyHightLight = false;
				foreach ($avaliableUsers as $userInfo)
				{
					if($greyHightLight)
					{
						printf("<tr>");
					}
					else
					{
						printf("<tr style='background-color:lightgrey'>");
					}
					$greyHightLight = !$greyHightLight;
	
					printf("<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>%s</td>
							<td class='ExeptionsCell' align='center'>
								<input id='usersToAllowAccess' data-userid='%s' type='checkbox'/>
							</td></tr>",
								$userInfo['login'],
								$userInfo['company'],
								$userInfo['author'],
								$userInfo['id']);
				}
				printf("</table>");
	
				if(count($avaliableUsers) > 30)
				{
					printf("</div>");
				}
			}
			else
			{
				printf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
				$this->lang->noDataToOpenAccess);
			}
			printf("</div>");
		}
	
		printf("<input id='author' value='%s' style='visibility:hidden;'/>", $this->username);
	
		unset($Usr);
	}
	
	public function DeleteUser()
	{
		$Usr = new User();
		$deletingUsername = $Usr->GetUserNameById($this->info->userId);
		$deletingUserId = $this->ulogin->Uid($deletingUsername);
		// Delete account in ulogin
		$this->ulogin->DeleteUser($deletingUserId);
		
		unset($_SESSION[$deletingUserId]);
		unset($_SESSION[$deletingUsername]);	
		
		$Fl = new Flight();
		$flightsArr = $Fl->GetFlightsByAuthor($deletingUsername);
		$Fl->DeleteFlightsByAuthor($deletingUsername);
		unset($Fl);
		
		foreach($flightsArr as $flightInfo)
		{
			$Usr->UnsetFlightAvaliable($flightInfo['id']);
		}
		
		$Bru = new Bru();
		$BruArr = $Bru->GetBrutypesByAuthor($deletingUsername);
		$Bru->DeleteBrutypesByAuthor($deletingUsername);
		unset($Bru);
		
		foreach($BruArr as $bruInfo)
		{
			$Usr->UnsetBrutypesAvaliable($bruInfo['id']);
		}
		
		$Sl = new Slice();
		$SliceArr = $Sl->GetSlicesByAuthor($deletingUsername);
		$Sl->DeleteSlicesByAuthor($deletingUsername);
		unset($Bru);
		
		foreach($SliceArr as $sliceInfo)
		{
			$Usr->UnsetSliceAvaliable($sliceInfo['id']);
		}
		
		$Eng = new Engine();
		$engArr = $Eng->GetEnginesByAuthor($deletingUsername);
		$Eng->DeleteEnginesByAuthor($deletingUsername);
		unset($Bru);
		
		foreach($engArr as $engId)
		{
			$Usr->UnsetEngineAvaliable($engId);
		}
		
		$Usr->UpdateUsersBecauseAuthorDeleting($deletingUsername);
		
		$Usr->DeleteUserPersonal($deletingUsername);
		unset($Usr);
	}
	
	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
			<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>

			<script type='text/javascript' src='scripts/userManager.js'></script>");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}

}

//=========================================================
// ╔══╗─────╔════╗─────────╔═╗╔═╗
// ║╔╗║─────║╔╗╔╗║─────────║║╚╝║║
// ║╚╝╚╦═╦╗╔╬╝║║╚╬╗─╔╦══╦══╣╔╗╔╗╠══╦═╗╔══╦══╦══╦═╗
// ║╔═╗║╔╣║║║─║║─║║─║║╔╗║║═╣║║║║║╔╗║╔╗╣╔╗║╔╗║║═╣╔╝
// ║╚═╝║║║╚╝║─║║─║╚═╝║╚╝║║═╣║║║║║╔╗║║║║╔╗║╚╝║║═╣║
// ╚═══╩╝╚══╝─╚╝─╚═╗╔╣╔═╩══╩╝╚╝╚╩╝╚╩╝╚╩╝╚╩═╗╠══╩╝
// ──────────────╔═╝║║║──────────────────╔═╝║
// ──────────────╚══╝╚╝──────────────────╚══╝
//=========================================================
class BruTypeManagerView implements iViewer
{
	private $filePath = LANG_FILE_PATH;
	private $curPage = 'bruTypePage';
	private $ulogin;
	private $uloginMsg;
	public $lang;
	private $info;
	public $action;
	private $username;
	public $privilege;

	function __construct($post)
	{
		$this->GetLanguage();

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();

		if(isset($post['bruTypeAction']) && $post['bruTypeAction'] != null)
		{
			$this->action = $post['bruTypeAction'];
			$this->ulogin->uloginMsg = "";
			if(isset($post['bruTypeId']) && $post['bruTypeId'] != null)
			{
				$this->info->bruTypeId = $post['bruTypeId'];
			}
		}
		else
		{
			exit("Action is not set");
		}

	}

	function GetLanguage()
	{
		$content = file_get_contents($this->filePath);
		$langObj = json_decode($content, true);

		if(is_array($langObj))
		{
			if(array_key_exists($this->curPage, $langObj))
			{
				$this->lang = (object)$langObj[$this->curPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($this->curPage, $langObj))
					{
						$this->lang = (object)$langObj[$this->curPage];
					}
					else
					{
						echo("No language object in file for current page. Table.php");
						error_log("No language object in file for current page. Table.php");
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. Table.php");
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents(LANG_FILE_PATH_DEFAULT);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($this->curPage, $langObj))
				{
					$this->lang = (object)$langObj->$this->curPage;
				}
				else
				{
					error_log("No language object in file for current page. Table.php");
					exit();
				}
			}
			else
			{
				error_log("No language object in file for current page. Table.php");
				exit();
			}
		}
	}

	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}

	public function ShowLoginForm()
	{
		printf("<div align='center'><p class='Label'>%s</p>
			<form action='index.php' method='POST'>
			<table>
				<tr><td>%s</td><td>
					<input type='text' name='user'>
				</td></tr>
				<tr><td>%s</td><td>
					<input type='password' name='pwd'>
				</td></tr>
				<tr><td>%s</td><td align='center'>
					<input type='checkbox' name='autologin' value='1'>
				</td></tr>
				<tr style='visibility:hidden;'><td>
					Nonce:
				</td><td>
					<input type='text' id='nonce' name='nonce' value='%s'>
				</td></tr>
			</table>

			<input class='Button' type='submit' value='%s'>
		</form></div>", $this->lang->loginForm,
		$this->lang->userName,
		$this->lang->pass,
		$this->lang->rememberMe,
		ulNonce::Create('login'),

		$this->lang->login);

		//ulLog::ShowDebugConsole();
	}

	public function PutCharset()
	{
		printf("<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
	}

	public function PutTitle()
	{
		printf("<title>%s</title>",
		$this->lang->bruTypeCreation);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link href='stylesheets/jquery-ui-1.10.3.custom.min.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/jTableThemes/lightcolor/gray/jtable.css' rel='stylesheet' type='text/css' />
				<link href='stylesheets/style.css' rel='stylesheet' type='text/css' />");
	}

	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}

	public function PutHeader()
	{
		printf("<body>");
	}
	
	public function PutInfo()
	{
		$action = $this->action;
		$bruTypeId = $this->info->bruTypeId;
		printf("<input id='action' type='hidden' value='%s'>", $action);
		printf("<input id='bruTypeId' type='hidden' value='%s' />", $bruTypeId );
	}

	public function PutMainMenu() { }

	public function ShowBruTypeInfoForm()
	{
		printf("<div align='center'><p class='Label'>%s</p></br>
			<form>
			<table>
			<tr><td>%s</td><td>
				<input id='bruTypeName' type='text' name='bruTypeName' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='stepLength' type='text' name='stepLength' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='stepDivider' type='text' name='stepDivider' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='frameLength' type='text' name='frameLength' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='wordLength' type='text' name='wordLength' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='aditionalInfo' type='text' name='aditionalInfo' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='headerLength' type='text' name='headerLength' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='frameSyncroCode' type='text' name='frameSyncroCode' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='previewParams' type='text' name='previewParams' size='50'>
			</td></tr>
			<tr><td>%s</td><td>
				<input id='collada' type='text' name='collada' size='50'>
			</td></tr>
		</table>
			<p><label>%s</label></p>
			<textarea id='headerScr' name='headerScr' rows='10' cols='80'></textarea></br>
			<input align='center' id='createUserBut' class='Button' type='submit' value='%s'>
			</form>
			</div><br><br>",
			$this->lang->bruTypeCreationForm,
			$this->lang->bruTypeName,
			$this->lang->stepLength,
			$this->lang->stepDivider,
			$this->lang->frameLength,
			$this->lang->wordLength,
			$this->lang->aditionalInfo,
			$this->lang->headerLength,
			$this->lang->frameSyncroCode,
			$this->lang->previewParams,
			$this->lang->collada,
			$this->lang->headerScr,
			$this->lang->bruTypeCreate);
		
		
	}
	
	public function ShowBruTypeCreationForm()
	{
		$this->ShowBruTypeInfoForm();
		$this->PutTableContainer();
	}

	public function ShowBruTypeEditingForm()
	{
		$this->ShowBruTypeInfoForm();
		$this->PutTableContainer();
	}

	public function DeleteBruType()
	{

	}
	
	public function PutTableContainer()
	{
		printf('<div id="apParamsTableContainer"></div>');
	}

	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}

	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.min.js'></script>
			<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>
			<script type='text/javascript' src='scripts/include/jtable/jquery.jtable.min.js'></script>
			<script type='text/javascript' src='scripts/bruTypeManager.js'></script>");
	}

	public function PutFooter()
	{
		printf("</body></html>");
	}

}

?>