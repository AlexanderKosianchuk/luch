<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

//================================================================
//┏┓╋╋┏┓╋╋╋╋╋╋╋┏━━━┓╋╋┏┓
//┃┗┓┏┛┃╋╋╋╋╋╋╋┃┏━┓┃╋┏┛┗┓
//┗┓┃┃┏╋━━┳┓┏┓┏┫┃╋┃┣━┻┓┏╋┳━━┳━┓┏━━┓
//╋┃┗┛┣┫┃━┫┗┛┗┛┃┃╋┃┃┏┓┃┃┣┫┏┓┃┏┓┫━━┫
//╋┗┓┏┫┃┃━╋┓┏┓┏┫┗━┛┃┗┛┃┗┫┃┗┛┃┃┃┣━━┃
//╋╋┗┛┗┻━━┛┗┛┗┛┗━━━┫┏━┻━┻┻━━┻┛┗┻━━┛
//╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┃┃
//╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋╋┗┛
//================================================================
class ViewOptionsModel
{
	public $curPage = 'viewOptionsPage';
	
	private $ulogin;
	private $username;
	
	public $privilege;
	public $lang;
	public $viewOptionsActions;

	public $action;
	public $data;

	function __construct($post, $session)
	{
		$L = new Language();
		$this->lang = $L->GetLanguage("ru", $this->curPage);
		$this->viewOptionsActions = (array)$L->GetServiceStrs($this->curPage);
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
			echo("Incorect input. Data: " . json_encode($post['data']) . 
				" . Action: " . json_encode($post['action']) . 
				" . Page: " . $this->curPage. ".");
			
			error_log("Incorect input. Data: " . json_encode($post['data']) . 
				" . Action: " . json_encode($post['action']) . 
				" . Page: " . $this->curPage. ".");
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && 
			isset($_SESSION['username']) && 
			isset($_SESSION['loggedIn']) && 
			($_SESSION['loggedIn'] === true);
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
		$topMenuContainer = "<div id='topMenuOptionsView' class='TopMenu'></div>";	
		return $topMenuContainer;
	}
	
	public function PutLeftMenu()
	{
		$leftMenu = "<div id='leftMenuOptionsView' class='LeftMenu'>";
		$leftMenu .= "<input class='SearchBox' value=' Поиск' size='24' disabled></input>";
		$leftMenu .= "<div id='templatesLeftMenuRow' class='LeftMenuRowOptions LeftMenuRowSelected'>" .
				"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/templates.png'></img>" .
				" " . $this->lang->flightViewOptionsTemplates .
				"</div>";
		
		$leftMenu .= "<div id='eventsLeftMenuRow' class='LeftMenuRowOptions'>" .
				"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/events.png'></img>" .
				" " . $this->lang->flightViewOptionsEvents .
				"</div>";
		
		$leftMenu .= "<div id='paramsListLeftMenuRow' class='LeftMenuRowOptions'>" .
				"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/paramsList.png'></img>" .
				" " . $this->lang->flightViewOptionsParamsList .
				"</div>";
		
		$leftMenu .= "</div>";
		
		return $leftMenu;
	}
	
	public function PutWorkspace()
	{
		//MainContainer
		$workspace = "<div id='flightOptionsWorkspace' class='WorkSpace'></div>";
		
		return $workspace;
	}
	
	public function GetBruType($extFlightId)
	{
		$flightId = $extFlightId;
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
		
		return $bruType;
	}
	
	public function GetBruTypeId($extFlightId)
	{
		$flightId = $extFlightId;
	
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruTypeInfo = $Bru->GetBruInfo($bruType);
		$bruTypeId = $bruTypeInfo['id'];
		unset($Fl);
	
		return $bruTypeId;
	}
	
	public function GetFlightTiming($flightId)
	{
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$stepLength = $bruInfo['stepLength'];
		
		$prefixArr = $Bru->GetBruApCycloPrefixes($bruType);
		unset($Bru);
		
		$Frame = new Frame();
		$framesCount = $Frame->GetFramesCount($flightInfo['apTableName'], $prefixArr[0]); //giving just some prefix
		unset($Frame);
		
		$stepsCount = $framesCount * $stepLength;
		$flightTiming['duration'] = $stepsCount;
		$flightTiming['startCopyTime'] = $flightInfo['startCopyTime'];
		$flightTiming['stepLength'] = $stepLength;
		
		return $flightTiming;
	}
			
	public function ShowTempltList($extFlightId)
	{		
		$flightId = $extFlightId;
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		$apTableName = $flightInfo['apTableName'];
		$bpTableName = $flightInfo['bpTableName'];
		$exTableName = $flightInfo['exTableName'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$paramSetTemplateListTableName = $bruInfo['paramSetTemplateListTableName'];
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
		$stepLength = $bruInfo['stepLength'];		
		//$this->info = array_merge($this->info, $flightInfo, $bruInfo);
		$prefixArr = $Bru->GetBruApCycloPrefixes($bruType);
		unset($Bru);
		
		$Frame = new Frame();
		$framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
		unset($Frame);
		
		$PSTempl = new PSTempl();
		//if no template table - create it
		$PSTTableName = $paramSetTemplateListTableName;
		if($PSTTableName == "")
		{
			$dummy = substr($cycloApTableName, 0, -3);
			$paramSetTemplateListTableName = $dummy . "_pst";
			$PSTTableName = $paramSetTemplateListTableName;
			$PSTempl->CreatePSTTable($PSTTableName);
			$PSTempl->AddPSTTable($bruType, $PSTTableName);
		}
	
		//if isset excListTable create list to add template
		$excEventsParamsList = array();
		if($exTableName != "")
		{
			$FEx = new FlightException();
			$excEventsList = $FEx->GetFlightEventsParamsList($exTableName);
			unset($FEx);
		}
	
		$flightTplsStr = "<select id='tplList' size='10' class='TplListSelect' multiple>";
	
		//here builds template options list
		$flightTplsStr .= $this->BuildTplOptionList($paramSetTemplateListTableName, $bruType);
	
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
			
			$Bru = new Bru();
			$paramNamesStr = $Bru->GetParamNames($bruType, $paramsToAdd);
	
			$flightTplsStr .= "<option id='tplOption' " .
					"name='".EVENTS_TPL_NAME."'  " .
					"data-comment='".$paramNamesStr."'  " .
					"data-params='".$params."'  " .
					"data-defaulttpl='true'  " .
					"selected> " .
					$foundedEventsTplName . " - ".$params."</option>";
	
			$this->CreateTemplate($flightId, $paramsToAdd, EVENTS_TPL_NAME);
		}
	
		unset($PSTempl);
	
		$flightTplsStr .= "</select><br><br>
			<textarea id='tplComment' class='TplListTextareaComment'
				rows='10' readonly/></textarea>";
		
		return $flightTplsStr;
	}
	
	private function BuildTplOptionList($extParamSetTemplateListTableName, $extBruType)
	{
		$username = $this->username;
		$bruType = $extBruType;
		$paramSetTemplateListTableName = $extParamSetTemplateListTableName;
		$PSTempl = new PSTempl();
		$PSTList = $PSTempl->GetPSTList($paramSetTemplateListTableName, $username);
		$defaultPSTName = $PSTempl->GetDefaultPST($paramSetTemplateListTableName, $username);
		unset($PSTempl);
		
		$optionsStr = "";
	
		$Bru = new Bru();
		for($i = 0; $i < count($PSTList); $i++)
		{
			$PSTRow = $PSTList[$i];
			$paramsArr = $PSTRow[1];
			$params = implode(", ", $paramsArr);
			
			$paramNamesStr = $Bru->GetParamNames($bruType, $paramsArr);
	
			if($PSTRow[0] == $defaultPSTName)
			{
				$optionsStr .= "<option id='tplOption' " .
						"name='".$PSTRow[0]."'  " .
						"title='".$params."' " .
						"data-comment='".$paramNamesStr."'  " .
						"data-params='".$params."'  " .
						"data-defaulttpl='true'  " .
						"selected> " .
						"(".$this->lang->defaultTpl.") " . $PSTRow[0] . " - ".$params."</option>";
			}
			else if($PSTRow[0] == PARAMS_TPL_NAME)
			{
				$optionsStr .= "<option id='tplOption' " .
						"name='".$PSTRow[0]."'  " .
						"title='".$params."' " .
						"data-comment='".$paramNamesStr."'  " .
						"data-params='".$params."'  " .
						"data-defaulttpl='true'  " .
						"selected> " .
						$this->lang->lastTpl." - ".$params."</option>";
			}
			else
			{
				if($PSTRow[0] != EVENTS_TPL_NAME)
				{
					$optionsStr .= "<option id='tplOption' " .
						"name='".$PSTRow[0]."'  " .
						"title='".$params."' " .
						"data-comment='".$paramNamesStr."'  " .
						"data-params='".$params."'  " .
						"data-defaulttpl='true'  " .
						"selected> " .
						$PSTRow[0] . " - ".$params."</option>";
				}
			}
		}
		unset($Bru);
		
		return $optionsStr;
	}
		
	public function ShowParamList($extFlightId)
	{
		$flightId = $extFlightId;
		
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$flightApHeaders = $Bru->GetBruApHeaders($bruType);
		$flightBpHeaders= $Bru->GetBruBpHeaders($bruType);
		unset($Bru);

		$paramList = sprintf ("<div class='ListContainer'>");
		
		$paramList .= sprintf ("<div class='ApList'>");
		
		for ($i = 0; $i < count($flightApHeaders); $i++)
		{
			$paramList .= sprintf ("
				<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
					data-colorpicker='false' readonly/>
				<label style='display:inline;'><input type='checkbox' class='ParamsCheckboxGroup' value='%s'/>
				%s, %s </label>
				</br>",
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['code'],
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['code'],
					$flightApHeaders[$i]['name'],
					$flightApHeaders[$i]['code']);
		}
		
		$paramList .= sprintf ("</div><div class='BpList'>");
	
		for ($i = 0; $i < count($flightBpHeaders); $i++)
		{
			$paramList .= sprintf ("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
				data-colorpicker='false' readonly/>
			<label style='display:inline;'>
			<input type='checkbox' id='bpCheckboxGroup' class='ParamsCheckboxGroup' value='%s'/>
			%s, %s</label></br>",
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['color'],
							$flightBpHeaders[$i]['code'],
							$flightBpHeaders[$i]['color'],
							$flightBpHeaders[$i]['code'],
							$flightBpHeaders[$i]['name'],
							$flightBpHeaders[$i]['code']);
		}
		
		$paramList .= sprintf("</div></div></br>");
		
		return $paramList;
	}
	
	public function GetParamCount($extFlightId)
	{
		$flightId = $extFlightId;
	
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$flightApHeaders = $Bru->GetBruApHeaders($bruType);
		$flightBpHeaders= $Bru->GetBruBpHeaders($bruType);
		unset($Bru);
		
		return array(
			'apCount' => $flightApHeaders,
			'bpCount' => $flightBpHeaders
		);
	}
	
	public function ShowParamListWithPaging($extFlightId, $extPageNum, $extPageSize)
	{
		$flightId = $extFlightId;
		$pageNum = $extPageNum;
		$pageSize = $extPageSize;
		
		$startIndex = $pageNum * $pageSize;
		$endIndex = $startIndex + $pageSize;
	
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$flightApHeaders = $Bru->GetBruApHeadersWithPaging($bruType, $startIndex, $endIndex);
		$flightBpHeaders = $Bru->GetBruBpHeadersWithPaging($bruType, $startIndex, $endIndex);
		unset($Bru);
	
		$paramList = sprintf ("<div class='ListContainer'>");
	
		if(count($flightApHeaders) < 1) {
			$paramList .= sprintf ("<div class='ApList' style='visibility:hidden'>");
		} else {
			$paramList .= sprintf ("<div class='ApList'>");
		}
		
	
		for ($i = 0; $i < count($flightApHeaders); $i++)
		{
			$paramList .= sprintf ("
				<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
					data-colorpicker='false' readonly/>
				<label style='display:inline;'><input type='checkbox' class='ParamsCheckboxGroupPaged' value='%s'/>
				%s, %s </label>
				</br>",
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['code'],
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['code'],
				$flightApHeaders[$i]['name'],
				$flightApHeaders[$i]['code']);
		}
	
		$paramList .= sprintf ("</div><div class='BpList'>");
	
		for ($i = 0; $i < count($flightBpHeaders); $i++)
		{
		$paramList .= sprintf ("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
				data-colorpicker='false' readonly/>
			<label style='display:inline;'>
			<input type='checkbox' id='bpCheckboxGroup' class='ParamsCheckboxGroupPaged' value='%s'/>
			%s, %s</label></br>",
					$flightBpHeaders[$i]['color'],
						$flightBpHeaders[$i]['color'],
						$flightBpHeaders[$i]['code'],
						$flightBpHeaders[$i]['color'],
						$flightBpHeaders[$i]['code'],
						$flightBpHeaders[$i]['name'],
						$flightBpHeaders[$i]['code']);
		}
	
		$paramList .= sprintf("</div></div></br>");
	
		return $paramList;
	}
	
	public function ShowSearchedParams($extFlightId, $extRequest)
	{
		$flightId = $extFlightId;
		$request = $extRequest;
		
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$flightApHeaders = $Bru->GetBruApHeadersByRequest($bruType, $request);
		$flightBpHeaders = $Bru->GetBruBpHeadersByRequest($bruType, $request);
		unset($Bru);
	
		$paramList = sprintf ("<div class='ListContainer'>");
	
		if(count($flightApHeaders) < 1) {
			$paramList .= sprintf ("<div class='ApList' style='visibility:hidden'>");
		} else {
			$paramList .= sprintf ("<div class='ApList'>");
		}
	
		for ($i = 0; $i < count($flightApHeaders); $i++)
		{
		$paramList .= sprintf ("
				<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
					data-colorpicker='false' readonly/>
				<label style='display:inline;'><input type='checkbox' class='ParamsCheckboxSearched' value='%s'/>
				%s, %s </label>
				</br>",
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['code'],
				$flightApHeaders[$i]['color'],
				$flightApHeaders[$i]['code'],
				$flightApHeaders[$i]['name'],
				$flightApHeaders[$i]['code']);
		}
	
			$paramList .= sprintf ("</div><div class='BpList'>");
	
		for ($i = 0; $i < count($flightBpHeaders); $i++)
			{
			$paramList .= sprintf ("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
				data-colorpicker='false' readonly/>
			<label style='display:inline;'>
			<input type='checkbox' id='bpCheckboxGroup' class='ParamsCheckboxSearched' value='%s'/>
			%s, %s</label></br>",
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['code'],
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['code'],
					$flightBpHeaders[$i]['name'],
					$flightBpHeaders[$i]['code']);
			}
	
			$paramList .= sprintf("</div></div></br>");
	
		return $paramList;
	}
	
	public function ShowEventsList($extFlightId)
	{
		$flightId = $extFlightId;
	
		$username = $this->username;
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		$exTableName = $flightInfo['exTableName'];
		unset($Fl);
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$flightApHeaders = $Bru->GetBruApHeaders($bruType);
		$flightBpHeaders= $Bru->GetBruBpHeaders($bruType);
		$excListTableName = $bruInfo['excListTableName'];
		unset($Bru);
		
		$eventsList = "";
	
		if($exTableName != "")
		{
			$FEx = new FlightException();
			$excEventsList = $FEx->GetFlightEventsList($exTableName);

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
					$reliability = "checked";
				}
				else
				{
					$reliability = "";
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
				$eventsList .= sprintf ("<table align='center' class='ExeptionsTable NotSelectable'>
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
					$excInfo = $FEx->GetExcInfo($excListTableName,
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

					$eventsList .= sprintf ("<tr style='%s' class='ExceptionTableRow' 
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
								<input class='reliability' data-excid='%s' type='checkbox' %s></input>
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
				
				$eventsList .= sprintf ("</table>");
				unset($FEx);
			}
			else
			{
				$eventsList .= sprintf ("<table border='1' align='center' style='padding:2px'>
						<tr><td>&nbsp;%s&nbsp;</td></tr>
						</table>", $this->lang->noEvents);
			}
		}
		else
		{
			$eventsList .= sprintf ("<table border='1' align='center' style='padding:2px'>
						<tr><td>&nbsp;%s&nbsp;</td></tr>
						</table>", $this->lang->processingWasNotPerformed);
		}
	
		return $eventsList;
	}
	
	public function GetDefaultTplParams($extFlightId)
	{
		$flightId = $extFlightId;
		$username = $this->username;
	
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$paramSetTemplateListTableName = $bruInfo['paramSetTemplateListTableName'];
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
	
		$PSTempl = new PSTempl();
		$params = $PSTempl->GetDefaultTemplateParams($paramSetTemplateListTableName, $username);
		unset($PSTempl);
	
		$apParams = array();
		$bpParams = array();
		foreach($params as $paramCode)
		{
			$paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);
			if($paramInfo["paramType"] == PARAM_TYPE_AP)
			{
				$apParams[] = $paramInfo['code'];
			}
			else if($paramInfo["paramType"] == PARAM_TYPE_BP)
			{
				$bpParams[] = $paramInfo['code'];
			}
		}
	
		unset($Bru);
		return array(
			'ap' => $apParams,
			'bp' => $bpParams);
	}
	
	public function GetTplParamCodes($extFlightId, $extTplName)
	{
		$flightId = $extFlightId;
		$tplName = $extTplName;
		$username = $this->username;
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$paramSetTemplateListTableName = $bruInfo['paramSetTemplateListTableName'];
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
		
		$PSTempl = new PSTempl();
		$params = $PSTempl->GetPSTByName($paramSetTemplateListTableName, $tplName, $username);
		unset($PSTempl);
		
		$apParams = array();
		$bpParams = array();
		foreach($params as $paramCode)
		{
			$paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);			
			if($paramInfo["paramType"] == PARAM_TYPE_AP)
			{
				$apParams[] = $paramInfo['code'];
			}
			else if($paramInfo["paramType"] == PARAM_TYPE_BP)
			{
				$bpParams[] = $paramInfo['code'];
			}
		}
		
		unset($Bru);
		return array(
				'ap' => $apParams, 
				'bp' => $bpParams);
	}
	
	public function CreateTemplate($extFlightId, $extParamsToAdd, $extTplName)
	{
		$flightId = $extFlightId;
		$paramsToAdd = $extParamsToAdd;
		$tplName = $extTplName;
		$username = $this->username;
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		
		$apTableName = $flightInfo['apTableName'];
		$bpTableName = $flightInfo['bpTableName'];
		unset($Fl);
			
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
		$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
			
		$paramsWithType = array();
		$Ch = new Channel();
		
		for($i = 0; $i < count($paramsToAdd); $i++)
		{
			$paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramsToAdd[$i]);
			if($paramInfo['paramType'] == PARAM_TYPE_AP)
			{
				$apTableNameWithPrefix = $apTableName . "_" . $paramInfo['prefix'];
				$paramMinMax = $Ch->GetParamMinMax($apTableNameWithPrefix,
				$paramsToAdd[$i], $username);
				
				$paramsWithType[PARAM_TYPE_AP][] = array(
					'code' => $paramsToAdd[$i],
					'min' => $paramMinMax['min'],
					'max' => $paramMinMax['max']);
			}
			else if($paramInfo['paramType'] == PARAM_TYPE_BP)
			{
				$paramsWithType[PARAM_TYPE_BP][] = array(
				'code' => $paramsToAdd[$i]);
			}
		}
		unset($Bru);
					
		$PSTempl = new PSTempl();
		$PSTempl->DeleteTemplate($PSTTableName, $tplName, $username);			
		$PSTempl->CreateTplWithDistributedParams($PSTTableName, $tplName, $paramsWithType, $username);
		
		unset($Ch);
		unset($PSTempl);
	}
	
	public function UpdateParamColor($extFlightId, $extParamCode, $extParamColor)
	{
		$flightId = $extFlightId;
		$paramCode = $extParamCode;
		$color = $extParamColor;
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
		
		$paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);
		
		if($paramInfo["paramType"] == PARAM_TYPE_AP)
		{
			$Bru->UpdateParamColor($cycloApTableName, $paramCode, $color);
		}
		else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
		{
			$Bru->UpdateParamColor($cycloBpTableName, $paramCode, $color);
		}
		
		unset($Bru);
	}
	
	public function SetExcReliability($extFlightId, $extExcId, $extState)
	{
		$flightId = $extFlightId;
		$excId = $extExcId;
		$state = $extState;

		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		unset($Fl);
		$excTableName = $flightInfo['exTableName'];
		
		if(($state == false) || ($state == 'false'))
		{
			$state = 1;
		}
		else if(($state == true) || ($state == 'true'))
		{
			$state = 0;
		}
		else 
		{
			$state = 0;
		}

		$FE = new FlightException();
		$res = $FE->UpdateFalseAlarmState($excTableName, $excId, $state);
		unset($FE);
		return $res;
	}
	
	
	
}

?>