<?php

require_once(@"includes.php");

//================================================================
//╔══╗────╔╗
//╚╣╠╝────║║
//─║║╔═╗╔═╝╠══╦╗╔╗
//─║║║╔╗╣╔╗║║═╬╬╬╝
//╔╣╠╣║║║╚╝║║═╬╬╬╗
//╚══╩╝╚╩══╩══╩╝╚╝
//================================================================
class IndexModel
{
	private $curPage = 'indexPage';
	
	private $ulogin;
	private $uloginMsg;
	private $username;
	
	public $privilege;
	public $lang;
	private $userLang;
	public $flightActions;

	function __construct()
	{	
		$L = new Language();
		$this->userLang = $L->GetLanguageName();
		$this->lang = $L->GetLanguage($this->curPage);
		unset($L);
		
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
			<html lang='%s'>
			<head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>", 
				$this->userLang);
	}

	public function PutTitle()
	{
		printf("<title>%s</title>", $this->lang->title);
	}

	public function PutStyleSheets()
	{
		printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
				<link rel='stylesheet' type='text/css' href='stylesheets/jquery-ui-1.10.3.custom.min.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/jquery.fileupload.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/jsTreeThemes/default/style.min.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/dataTables/jquery.dataTables.min.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/jquery.colorpicker.css'/>
				<link rel='stylesheet' type='text/css' href='stylesheets/pages/bruTypeTemplates.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/pages/viewOptionsParams.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/pages/viewOptionsEvents.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/pages/chart.css' />
				<link rel='stylesheet' type='text/css' href='stylesheets/style.css' />");
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
		printf("</head><body>");
	}	
	
	public function EventHandler()
	{
		printf("<div id='eventHandler'></div>");
	}
	
	public function PutMessageBox()
	{
		printf("<div id='dialog' title='%s'>
				<p></p>
				</div>", $this->lang->message);
	}
	
public function PutHelpDialog()
	{
		printf("<div id='helpDialog' title='%s'>
				<p>
				</p>
				===== F5 - Возврат в Главное меню LUCH =====
				</p>
				1. На графике :  V -  установить вертикальное сечение
				</p>
				** Двойной LeftMouse - на окне времени сечения - отмена
				<p>
				2. На графике :  N - отобразить имена параметров 
				</p>
				** повторное нажатие N - отмена 
				<p>
				3. На графике :  L - установить справа от визира имена параметров
				</p>
				** повторное нажатие L - отмена 
				<p>
				4. На графике :  D - равномерное распределение аналоговых параметров на экране
				</p>
				<p>
				5. На графике :  SHFT + D - равномерное распределение линий и разовых команд на экране
				</p>
				<p>
				6. На графике :  LeftMouse - при круглом указателе - выбор линии для редактирования (утолщается)
				</p>
				** LeftMouse - на круглом указателе - отмена выбора линии
				</p>
				** Mouse вверх/вниз - на графике : перемещение выбранной линии
				</p>
				** Ctrl + Mouse вверх/вниз - на графике : увеличение/уменьшение масштаба выбранной линии
				</p>
				<p>
				7. На графике :  Ролик Mouse на себя / от себя - удаление / приближение графика
				</p>
				<p>
				8. На графике :  Двойной LeftMouse - приближение графика по линии визира
				</p>
				<p>
				9. На графике :  + / - на правой панели - увеличение/уменьшение количества горизонтальных линий сетки
				</p>
				<p>
				10. В режиме <Бланк> или <График на печать> : Ctrl + P - вывод на печать (или запись в файл)
				</p>
				<p>
				</p>
				</div>", $this->lang->helpTitle);
	}
	
	public function PutExportLink()
	{
		printf("<div id='exportLink'></div>");
	}
	
	public function PutScripts()
	{
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery-2.1.1.min.js'></script>");
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>");
		printf("<script type='text/javascript' src='scripts/include/jquery/jquery.mousewheel.min.js'></script>");
		
		//The jQuery UI widget factory, can be omitted if jQuery UI is already included
		printf("<script type='text/javascript' src='scripts/include/fileUploader/vendor/jquery.ui.widget.js'></script>");
		//The Iframe Transport is required for browsers without support for XHR file uploads
		printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.iframe-transport.js'></script>");
		//jstree
		printf("<script type='text/javascript' src='scripts/include/jstree/jstree.min.js'></script>");
		//tables
		//printf("<script type='text/javascript' src='scripts/include/jtable/jquery.jtable.min.js'></script>");
		printf("<script type='text/javascript' src='scripts/include/dataTables/jquery.dataTables.min.js'></script>");
		printf("<script type='text/javascript' src='scripts/include/dataTables/dataTables.fixedHeader.min.js'></script>");
				
		//colorpicker
		printf("<script type='text/javascript' src='scripts/include/colorpicker/jquery.colorpicker.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-parser.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-crayola.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-memory.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-pantone.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-ral-classic.js'></script>
			<script type='text/javascript' src='scripts/include/colorpicker/jquery.ui.colorpicker-cmyk-percentage-parser.js'></script>");
		
		//flot
		printf("<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>-->
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
			<script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>-->
		
			<!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
			<!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->");
		//The basic File Upload plugin
		printf("<script type='text/javascript' src='scripts/include/fileUploader/jquery.fileupload.js'></script>");
		//manual scripts
		printf("<script type='text/javascript' src='scripts/index.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/Lang.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/WindowFactory.proto.js'></script>");
		
		//main
		printf("<script type='text/javascript' src='scripts/proto/main/FlightList.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/main/FlightUploader.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/main/FlightProccessingStatus.proto.js'></script>");
		
		//bruType
		printf("<script type='text/javascript' src='scripts/proto/bruType/BruType.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeGeneralInfo.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeTemplates.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeEvents.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/bruType/BruTypeCyclo.js'></script>");
		
		//user
		printf("<script type='text/javascript' src='scripts/proto/user/User.proto.js'></script>");
		
		//viewOptions
		printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptions.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsTpls.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsEvents.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/viewOptions/ViewOptionsParams.js'></script>");
		
		
		//chart scripts
		printf("<script type='text/javascript' src='scripts/proto/chart/Chart.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/chart/AxesWorker.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/chart/Exception.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/chart/Legend.proto.js'></script>");
		printf("<script type='text/javascript' src='scripts/proto/chart/Param.proto.js'></script>");	
	}
	
	public function PutFooter()
	{
		printf("</body></html>");
	}
	
}


	



















//
// 	public function PutMainMenu()
// 	{
// 		printf("<div class='MainMenu'>");
		
// 		$Usr = new User();
		
// 		if(in_array($Usr->flightPrivilegeArr[0], $this->privilege) ||
// 			in_array($Usr->flightPrivilegeArr[1], $this->privilege) ||
// 			in_array($Usr->flightPrivilegeArr[2], $this->privilege) ||
// 			in_array($Usr->flightPrivilegeArr[3], $this->privilege) ||
// 			in_array($Usr->flightPrivilegeArr[4], $this->privilege) ||
// 			in_array($Usr->flightPrivilegeArr[5], $this->privilege))
// 		{
// 			printf("<img id='flight' src='stylesheets/basicImg/flight.png'></img>");
// 		}
		
// 		if(in_array($Usr->slicePrivilegeArr[0], $this->privilege) ||
// 			in_array($Usr->slicePrivilegeArr[1], $this->privilege) ||
// 			in_array($Usr->slicePrivilegeArr[2], $this->privilege) ||
// 			in_array($Usr->slicePrivilegeArr[3], $this->privilege))
// 		{
// 			printf("<img id='slice' src='stylesheets/basicImg/slice.png'></img>");
// 		}

// 		if(in_array($Usr->enginePrivilegeArr[0], $this->privilege) ||
// 				in_array($Usr->enginePrivilegeArr[1], $this->privilege) ||
// 				in_array($Usr->enginePrivilegeArr[2], $this->privilege))
// 		{
// 			printf("<img id='engine' src='stylesheets/basicImg/engine.png'></img>");
// 		}
		
// 		if(in_array($Usr->bruTypesPrivilegeArr[0], $this->privilege) ||
// 				in_array($Usr->bruTypesPrivilegeArr[1], $this->privilege) ||
// 				in_array($Usr->bruTypesPrivilegeArr[2], $this->privilege) ||
// 				in_array($Usr->bruTypesPrivilegeArr[3], $this->privilege))
// 		{
// 			printf("<img id='bruType' src='stylesheets/basicImg/bru.png'></img>");
// 		}
		
// 		/*if(in_array($Usr->docsPrivilegeArr[0], $this->privilege) ||
// 				in_array($Usr->docsPrivilegeArr[1], $this->privilege) ||
// 				in_array($Usr->docsPrivilegeArr[2], $this->privilege) ||
// 				in_array($Usr->docsPrivilegeArr[3], $this->privilege))
// 		{
// 			printf("<img id='docs' src='stylesheets/basicImg/doc.png'></img>");
// 		}*/
		
// 		if(in_array($Usr->userPrivilegeArr[0], $this->privilege) ||
// 				in_array($Usr->userPrivilegeArr[1], $this->privilege) ||
// 				in_array($Usr->userPrivilegeArr[2], $this->privilege) ||
// 				in_array($Usr->userPrivilegeArr[3], $this->privilege) ||
// 				in_array($Usr->userPrivilegeArr[4], $this->privilege))
// 		{
// 			printf("<img id='user' src='stylesheets/basicImg/user.png'></img>");
// 		}
		
// 		unset($Usr);
		
// 		printf("</div>");
		
// 		$this->FlightSubMenu();
// 		$this->SliceSubMenu();
// 		$this->EngineSubMenu();
// 		$this->BruTypesSubMenu();
// 		$this->DocsSubMenu();
// 		$this->UserSubMenu();
// 	}

// 	private function FlightSubMenu()
// 	{
// 		printf("<div class='FlightSubMenu'>");
		
// 		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $this->privilege))
// 		{
// 			printf("<img id='viewFlight' src='stylesheets/basicImg/flightView.png' title='%s'></img>",
// 				$this->lang->flightView);
// 		}
					
// 		if(in_array(PRIVILEGE_FOLLOW_FLIGHTS, $this->privilege))
// 		{
// 			printf("<img id='followFlight' src='stylesheets/basicImg/flightFollow.png' title='%s'></img>",
// 				$this->lang->flightFollow);
// 		}
		
// 		if(in_array(PRIVILEGE_ADD_FLIGHTS, $this->privilege))
// 		{
// 			printf("<img id='addFlight' src='stylesheets/basicImg/flightUpload.png' title='%s'></img>",
// 				$this->lang->flightUpload);
// 			printf("<img id='impFlight' src='stylesheets/basicImg/flightImp.png' title='%s'></img>",
// 				$this->lang->flightImport);
// 		}
					
// 		if(in_array(PRIVILEGE_DEL_FLIGHTS, $this->privilege))
// 		{
// 			printf("<img id='delFlight' src='stylesheets/basicImg/flightDel.png' title='%s'></img>",
// 				$this->lang->flightDelete);
// 			printf("<img id='expFlight' src='stylesheets/basicImg/flightExp.png' title='%s'></img>",
// 				$this->lang->flightExport);
// 		}
		
// 		printf("</div>");
// 	}

// 	private function SliceSubMenu()
// 	{
// 		printf("<div class='SliceSubMenu'>");
				
// 		if(in_array(PRIVILEGE_VIEW_SLICES, $this->privilege))
// 		{
// 			printf("<img id='calcSlice' src='stylesheets/basicImg/sliceCalc.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_EDIT_SLICES, $this->privilege))
// 		{
// 			printf("<img id='etalonSlice' src='stylesheets/basicImg/sliceEtalon.png'></img>");
// 			printf("<img id='chooseSlice' src='stylesheets/basicImg/sliceChoose.png'></img>");
// 			printf("<img id='appendSlice' src='stylesheets/basicImg/sliceAppend.png' hidden='true'></img>");
// 		}
		
// 		//this button has more relation to engines but also operates slices
// 		if(in_array(PRIVILEGE_EDIT_ENGINES, $this->privilege))
// 		{
// 			printf("<img id='compareSlice' src='stylesheets/basicImg/sliceCompare.png' hidden='true'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_ADD_SLICES, $this->privilege))
// 		{
// 			printf("<img id='createSlice' src='stylesheets/basicImg/sliceCreate.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_DEL_SLICES, $this->privilege))
// 		{
// 			printf("<img id='delSlice' src='stylesheets/basicImg/sliceDel.png'></img>");
// 		}
				
// 		printf("</div>");
// 	}
	
// 	private function EngineSubMenu()
// 	{
// 		printf("<div class='EngineSubMenu'>");
				
// 		if(in_array(PRIVILEGE_VIEW_ENGINES, $this->privilege))
// 		{
// 			printf("<img id='engineDiagnostic' src='stylesheets/basicImg/engineDiagnostic.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_DEL_ENGINES, $this->privilege))
// 		{
// 			printf("<img id='engineDel' src='stylesheets/basicImg/engineDel.png'></img>");
// 		}

// 		printf("</div> ");
// 	}
	
// 	private function BruTypesSubMenu()
// 	{
// 		printf("<div class='BruTypesSubMenu'>");
				
						
// 		if(in_array(PRIVILEGE_VIEW_BRUTYPES, $this->privilege))
// 		{
// 			printf("<img id='bruTypeView' src='stylesheets/basicImg/bruTypeView.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_EDIT_BRUTYPES, $this->privilege))
// 		{
// 			printf("<img id='bruTypeEdit' src='stylesheets/basicImg/bruTypeEdit.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_ADD_BRUTYPES, $this->privilege))
// 		{
// 			printf("<img id='bruTypeAdd' src='stylesheets/basicImg/bruTypeAdd.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_DEL_BRUTYPES, $this->privilege))
// 		{
// 			printf("<img id='bruTypeDel' src='stylesheets/basicImg/bruTypeDel.png'></img>");
// 		}

// 		printf("</div>");
// 	}
	
// 	private function DocsSubMenu()
// 	{
// 		/*printf("<div class='DocsSubMenu'>");
		
// 		if(in_array(PRIVILEGE_VIEW_DOCS, $this->privilege))
// 		{
// 			printf("<img id='docView' src='stylesheets/basicImg/docView.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_EDIT_DOCS, $this->privilege))
// 		{
// 			printf("<img id='docEdit' src='stylesheets/basicImg/docEdit.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_ADD_DOCS, $this->privilege))
// 		{
// 			printf("<img id='docAdd' src='stylesheets/basicImg/docAdd.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_DEL_DOCS, $this->privilege))
// 		{
// 			printf("<img id='docDel' src='stylesheets/basicImg/docDel.png'></img>");
// 		}
		
// 		printf("</div>");*/
// 	}
	
// 	private function UserSubMenu()
// 	{
// 		printf("<div class='UserSubMenu'>");
		
// 		if(in_array(PRIVILEGE_OPTIONS_USERS, $this->privilege))
// 		{
// 			//printf("<img id='userOptions' src='stylesheets/basicImg/userOptions.png'></img>");
// 			printf("<img id='userExit' src='stylesheets/basicImg/userExit.png' title='%s'></img>",
// 				$this->lang->userExit);
// 		}
		
// 		if(in_array(PRIVILEGE_VIEW_USERS, $this->privilege))
// 		{
// 			printf("<img id='userView' src='stylesheets/basicImg/userView.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_EDIT_USERS, $this->privilege))
// 		{
// 			printf("<img id='userEdit' src='stylesheets/basicImg/userEdit.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_ADD_USERS, $this->privilege))
// 		{
// 			printf("<img id='userAdd' src='stylesheets/basicImg/userAdd.png'></img>");
// 		}
		
// 		if(in_array(PRIVILEGE_DEL_USERS, $this->privilege))
// 		{
// 			printf("<img id='userDel' src='stylesheets/basicImg/userDel.png'></img>");
// 		}
		
// 		printf("</div>");
// 	}

// 	public function FileUploadBlock()
// 	{
// 		$Usr = new User();
// 		$avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->username);
// 		unset($Usr);
		
// 		$Bru = new Bru();
// 		$bruList = $Bru->GetBruList($avalibleBruTypes);
// 		unset($Bru);
		
// 		$optionString = "";
		
// 		foreach($bruList as $bruInfo)
// 		{
// 			$optionString .="<option>".$bruInfo['bruType']."</option>";
// 		}
		
// 		printf("<div id='fileUpload' class='OptionBlock' title='%s'>
// 				<form action='fileUploader.php' method='post' enctype='multipart/form-data'>
// 				<select id='bruType' name='bruType' class='FlightUploadingInputs' style='margin-left:11px;'>%s</select>
// 				<input name='uploadingFile[]' type='file' style='width:250px; margin:10px' multiple='multiple'/></br>
// 				<input name='submitUploadingFile' value='%s' class='Button' type='submit'/>
// 				</form></div>", $this->lang->flightUpload, $optionString, $this->lang->send);
// 	}
	
// 	public function FileImportBlock()
// 	{	
// 		printf("<div id='fileImport' class='OptionBlock' title='%s'><br>
// 			<span class='btn btn-success fileinput-button'>
// 			<i class='glyphicon'>%s</i>
// 			<input id='fileImportBut' type='file' name='files[]' multiple>
// 			</span>
// 			<br>
// 			<br>
// 			<div id='progress' class='progress'>
// 					<div class='progress-bar progress-bar-success'></div>
//    			</div>
// 			<div id='files' class='files'></div>
// 			<br></div>", $this->lang->fileImport, $this->lang->chooseFile);
// 	}

// 	public function SliceCreationBlock()
// 	{
// 		$Sl = new Slice();
// 		$slTypesList = $Sl->GetSliceTypesList();
// 		unset($Sl);
// 		$optionString = '';
// 		for($i = 0; $i < count($slTypesList); $i++)
// 		{
// 			$optionString .= "<option>".$slTypesList[$i]['code']."</option>";
// 		}

// 		printf("<div id='sliceCreation' class='OptionBlock' title='%s'>
// 				<form action='sliceUploader.php' method='post' enctype='multipart/form-data'>
// 				<label><input name='name' type='text' style='margin:10px;'/>
// 				%s
// 				</label></br>
// 				<label>
// 				<select name='code' size='1' style='width:145px; margin:11px'>
// 				%s
// 				</select>%s
// 				</label></br>
// 				<input name='action' value='%s' style='display:none'/>
// 				<input name='submitUploadingFile' value='%s' class='Button'
// 				type='submit'/>
// 				</form></div>", $this->lang->sliceCreation,
// 		$this->lang->sliceName,
// 		$optionString,
// 		$this->lang->sliceCode,
// 		SLICE_CREALE,
// 		$this->lang->create);
// 	}

// 	public function ShowSearchBox()
// 	{
// 		printf("<div class='SearchBox'>
// 				<a class='Label'>%s</a>
// 				<form action='searchEngine.php' method='post'
// 				enctype='multipart/form-data' name='SearchBox'>
// 				&nbsp;&nbsp;
// 				<input id='query' name='query' style='width:400px' disabled/>
// 				&nbsp;&nbsp;
// 				<button id='find' class='Button' disabled>%s</button>
// 				</form></div>",
// 		$this->lang->search,
// 		$this->lang->find);
// 	}

// 	public function ShowFlightList()
// 	{
// 		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $this->privilege))
// 		{
// 			$Usr = new User();
// 			$avalibleFlights = $Usr->GetAvaliableFlights($this->username);
// 			unset($Usr);
			
// 			$Fl = new Flight();
// 			$Fl->CreateFlightTable();
// 			$listFlights = (array)$Fl->PrepareFlightsList($avalibleFlights);
// 			unset($Fl);
	
// 			printf("<div class='FlightList NotSelectable' style='margin-top:-8px;'>
// 					<a class='Label'>%s</a>
// 					<form action='tuner.php' method='post'
// 						enctype='multipart/form-data'
// 					id='flightList'>
// 					<table border='0'>", $this->lang->flightList);
	
// 			$i = 0;
	
// 			while($i < count($listFlights))
// 			{
// 				$flight = (array)$listFlights[$i];
// 				//error_log(json_encode($flight));
// 				if($flight['exceptionsSearchPerformed'] == true)
// 				{
// 					$flight['exceptionsSearchPerformed'] = $this->lang->performed;
// 				}
// 				else
// 				{
// 					$flight['exceptionsSearchPerformed'] = $this->lang->notPerformed;
// 				}
	
// 				printf("<tr id='flightRow' data-flightid='%s'>
// 						<td class='FlightListCell'>%s</td>
// 						<td class='FlightListCell'>
// 						<input id='flightIdRadioBut' name='radioBut' type='radio' value='%s'
// 						style='margin-left:20px; margin-right:20px;'></td>
// 						<td class='FlightListCell' style='width:%s'> %s - %s. %s - %s</br>
// 						%s - %s </br>
// 						%s - %s </br>
// 						%s - %s </br>
// 						%s - %s; %s - %s </br>
// 						%s - %s</br>
// 						%s - %s</td>
// 						</tr>",
// 				$flight['bort'], $flight['cellNum'],
// 				$flight['cellNum'], "100%",
// 				$this->lang->bort, $flight['bort'], $this->lang->voyage, $flight['voyage'],
// 				$this->lang->uploadTime, $flight['uploadDate'],
// 				$this->lang->flightTime, $flight['flightDate'],
// 				$this->lang->bruType, $flight['bruType'],
// 				$this->lang->departureAirport, $flight['departureAirport'], $this->lang->arrivalAirport, $flight['arrivalAirport'],
// 				$this->lang->performer, $flight['performer'],
// 				$this->lang->status, $flight['exceptionsSearchPerformed']);
// 				$i++;
// 			}
	
// 			printf("</table></form></div>");
// 		}
// 	}

// 	public function ShowSliceList()
// 	{
// 		if(in_array(PRIVILEGE_VIEW_SLICES, $this->privilege))
// 		{
// 			$Usr = new User();
// 			$avalibleSlices = $Usr->GetAvaliableSlices($this->username);
// 			unset($Usr);
			
// 			$Sl = new Slice();
// 			$Sl->CreateSliceTable();
// 			$slList = (array)$Sl->GetSliceList($avalibleSlices);
	
// 			printf("<div class='SlicesList'>
// 					<a class='Label'>%s</a>
// 					<form action='sliceUploader.php' method='post'
// 					enctype='multipart/form-data' id='sliceList'>
// 					<table border='0'>", $this->lang->sliceList);
	
// 			for($i = 0; $i < count($slList); $i++)
// 			{
// 				$slice = (array)$slList[$i];
// 				$sliceTypeInfo = $Sl->GetSliceTypeInfo($slice['code']);
// 				$slice['bruType'] = $sliceTypeInfo['bruType'];
				
// 				if($slice['etalonTableName'] != "")
// 				{
// 					//if etalon - show engines that formed in etalon
// 					$status = $this->lang->sliceSetAsEtalon . "; ";
// 					$etalonPairs = $Sl->GetEtalonEngineSlicesPairs($slice['etalonTableName'], $slice['id']);
					
// 					for($j = 0; $j < count($etalonPairs); $j++)
// 					{
// 						$status .= "</br>" . $etalonPairs[$j]['engineSerial'] . " - " . $etalonPairs[$j]['sliceCode'] . ";";
// 					}
// 				}
// 				else 
// 				{
// 					//if just slice - show engines that appended to slice
// 					$status = $this->lang->sliceNotSetAsEtalon;
// 					$slicePairs = $Sl->GetSliceEngineSlicesFlightCountPairs($slice['sliceTableName'], $slice['id']);
					
// 					for($j = 0; $j < count($slicePairs); $j++)
// 					{
// 						$status .= "</br>" . $slicePairs[$j]['engineSerial'] . " - " . $slicePairs[$j]['sliceCode'] . " - " . $slicePairs[$j]['flightCount'] . ";";
// 					}
// 				}
	
// 				printf("<tr>
// 						<td class='FlightListCell'>%s</td>
// 						<td class='FlightListCell'>
// 						<input name='sliceId' type='radio' value='%s'
// 						style='margin-left:20px; margin-right:20px;'></td>
// 						<td class='FlightListCell'> %s - %s. %s - %s</br>
// 						%s - %s </br>
// 						%s - %s </br>
// 						%s - %s </br>
// 						%s - %s</td>
// 						</tr>",
// 				$slice['id'],
// 				$slice['id'],
// 				$this->lang->sliceName, $slice['name'], $this->lang->sliceCode, $slice['code'],
// 				$this->lang->bruType, $slice['bruType'],
// 				$this->lang->sliceStatusAsEtalon, $status,
// 				$this->lang->sliceCreationTime, $slice['creationTime'],
// 				$this->lang->sliceLastModifyTime, $slice['lastModifyTime']);
// 			}
	
// 			printf("</table>
// 					<input id='flightId' name='flightId' value='' style='display:none'/>
// 					<input id='sliceUploaderAction' name='action' value='' style='display:none'/>
// 					</form></div>");
// 			unset($Sl);
// 		}
// 	}
	
// 	public function ShowEngineList()
// 	{
// 		if(in_array(PRIVILEGE_VIEW_ENGINES, $this->privilege))
// 		{
// 			$Usr = new User();
// 			$avalibleEngines = $Usr->GetAvaliableEngines($this->username);
// 			unset($Usr);
			
// 			$Eng = new Engine();
// 			$Eng->CreateEngineDiscrepTable();
// 			$engineSerialsByEtalonsArr = (array)$Eng->SelectEnginesSerialsByEtalonsList($avalibleEngines);
		
// 			printf("<div class='EnginesList'>
// 					<a class='Label'>%s</a>
// 					<form action='diagnostic.php' method='post'
// 						enctype='multipart/form-data' id='enginesList'>				
// 					<table border='0'>", $this->lang->engineList);
			
// 			$Sl = new Slice();
// 			$num = 1;
// 			$curEgineSerial = 0;
// 			foreach ($engineSerialsByEtalonsArr as $etalonId => $engineSerials)
// 			{
// 				$curSliceInfo = $Sl->GetSliceInfo($etalonId);
				
// 				for($i = 0; $i < count($engineSerials); $i++)
// 				{
// 					$curEgineSerial = $engineSerials[$i];
					
// 					$engineInfo = $Eng->GetEngineInfoBySerialAndEtalon($etalonId, $curEgineSerial);
// 					printf("<tr>
// 					<td class='FlightListCell'>%s</td>
// 					<td class='FlightListCell'>
// 					<input name='etalonId' type='radio' value='%s' data-engineserial='%s'
// 						style='margin-left:20px; margin-right:20px;'></td>
// 					<td class='FlightListCell'> %s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					%s - %s</td>
// 					</tr>",
// 						$num,
// 						$etalonId, $curEgineSerial,
// 						$this->lang->engineSerial, $curEgineSerial,
// 						$this->lang->etalonName, $curSliceInfo["name"],
// 						$this->lang->engineFlightLastTime, date("Y-m-d H:i:s", $engineInfo["flightDate"]),
// 						$this->lang->engineSliceCodes, $engineInfo["sliceCode"],
// 						$this->lang->engineDescreps, $engineInfo["discrepCode"]);
					
// 					$num++;
// 				}
// 			}
		
// 			printf("</table>
// 				<input id='engineAction' name='engineAction' value='%s' style='display:none'>
// 				<input id='engineSerial' name='engineSerial' value='%s' style='display:none'>
// 				</form></div>", $curEgineSerial, ENGINE_DIAGNOSTIC);
// 			unset($Sl);
// 			unset($Eng);
// 		}
// 	}
	
// 	public function ShowBruTypesList()
// 	{
// 		if(in_array(PRIVILEGE_VIEW_BRUTYPES, $this->privilege))
// 		{
// 			$Usr = new User();
// 			$avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->username);
			
// 			$Bru = new Bru();
// 			$Bru->CreateBruTypeTable();
			
// 			$bruTypeList = (array)$Bru->GetBruList($avalibleBruTypes);
	
// 			printf("<div class='BruTypesList'>
// 				<a class='Label'>%s</a>
// 				<form action='bruTypeManager.php' method='post'
// 					enctype='multipart/form-data' id='bruTypeList'>
// 				<table border='0'>", $this->lang->bruTypesList);
				
// 			$num = 1;
// 			foreach ($bruTypeList as $bruType)
// 			{
// 				$aditionalInfo = $bruType['aditionalInfo'];
				
// 				if(strlen($aditionalInfo) > 0)
// 				{
// 					$aditionalInfoArr = explode(";", $aditionalInfo);
					
// 					$aditionalInfoLangAdopted = '';
// 					foreach ($aditionalInfoArr as $aditionalInfoVal)
// 					{
// 						if(property_exists($this->lang, $aditionalInfoVal))
// 						{
// 							$aditionalInfoLangAdopted .= $this->lang->$aditionalInfoVal . ", ";
// 						}
// 						else
// 						{
// 							$aditionalInfoLangAdopted .=  $aditionalInfoVal . ", ";
// 						}
// 					}
					
// 					$aditionalInfoLangAdopted = substr($aditionalInfoLangAdopted, 0, -2);
// 				}
// 				else
// 				{
// 					$aditionalInfoLangAdopted = $this->lang->bruTypesNoAditionalInfo;
// 				}
				
// 				printf("<tr>
// 				<td class='FlightListCell'>%s</td>
// 					<td class='FlightListCell'>
// 					<input name='bruTypeId' type='radio' value='%s' data-bruTypeId='%s'
// 						style='margin-left:20px; margin-right:20px;'></td>
// 					<td class='FlightListCell'> %s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					</tr>",
// 						$num, $bruType['id'], $bruType['id'],
// 						$this->lang->bruTypesName, $bruType['bruType'],
// 						$this->lang->bruTypesStepLenth, $bruType['stepLength'],
// 						$this->lang->bruTypesFrameLength, $bruType['frameLength'],
// 						$this->lang->bruTypesWordLength, $bruType['wordLength'],
// 						$this->lang->bruTypesAditionalInfo, $aditionalInfoLangAdopted);
	
// 				$num++;
// 			}
	
// 			printf("</table>
// 				<input id='bruTypeAction' name='bruTypeAction' value='%s' style='display:none'>
// 				</form></div>", BRUTYPE_VIEW);
				
// 			unset($Usr);
// 		}
// 	}
	
// 	public function ShowUsersList()
// 	{
// 		if(in_array(PRIVILEGE_VIEW_USERS, $this->privilege))
// 		{
// 			$Usr = new User();
// 			$Usr->CreateUsersTables();
			
// 			$avalibleUsers = $Usr->GetAvaliableUsers($this->username);
			
// 			$userList = (array)$Usr->GetUsersList($avalibleUsers);
		
// 			printf("<div class='UsersList'>
// 					<a class='Label'>%s</a>
// 					<form action='userManager.php' method='post'
// 						enctype='multipart/form-data' id='usersList'>
// 					<table border='0'>", $this->lang->usersList);
			
// 			$num = 1;
// 			foreach ($userList as $user)
// 			{
// 				$privilegeChecked = $Usr->CheckPrivilege(explode(",", $user['privilege']));
				
// 				//just to prevent to long string
// 				if(strlen($privilegeChecked) > 100)
// 				{
// 					$pos = strrpos(substr($privilegeChecked, 0, 100), ",", 0);
// 					if ($pos !== false) 
// 					{
// 						$privilegeChecked = substr_replace($privilegeChecked, "<br>", $pos, 1);
// 					}
// 				}
				
// 				$subscribers = '';
// 				if($user['subscribers'] == '')
// 				{
// 					$subscribers = $this->lang->userNoSubscribers;
// 				}
// 				else
// 				{
// 					$subscribers = $user['subscribers'];
// 				}
				
// 				printf("<tr>
// 				<td class='FlightListCell'>%s</td>
// 					<td class='FlightListCell'>
// 					<input name='userId' type='radio' value='%s' data-userId='%s'
// 						style='margin-left:20px; margin-right:20px;'></td>
// 					<td class='FlightListCell'> %s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					%s - %s </br>
// 					</tr>",
// 				$num, $user['id'], $user['id'],
// 				$this->lang->usersLogin, $user['login'],
// 				$this->lang->usersCompany, $user['company'],
// 				$this->lang->usersPrivilege, $privilegeChecked,
// 				$this->lang->usersSubscribers, $subscribers);
	
// 				$num++;
// 			}
		
// 			printf("</table>
// 				<input id='userAction' name='userAction' value='%s' style='display:none'>
// 				</form></div>", USER_CREATE);
			
// 			unset($Usr);
// 		}
// 	}
	
?>