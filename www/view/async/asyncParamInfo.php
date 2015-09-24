<?php
	
require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if((isset($_GET['action']) && ($_GET['action'] == CHART_PARAM_INFO_RECEIVE_LEGENT)) || 
		(isset($_POST['action']) && ($_POST['action'] == CHART_PARAM_INFO_RECEIVE_LEGENT)))
	{
		if(isset($_POST['flightId']) || isset($_GET['flightId']))
		{
			if(isset($_POST['flightId']))
			{
				$flightId = $_POST['flightId'];
			}
			else if(isset($_GET['flightId']))
			{
				$flightId = $_GET['flightId'];
			}

			
			if(isset($_GET['paramCode']) || isset($_POST['paramCode']))
			{
				if(isset($_GET['paramCode']))
				{
					$paramCodeArray = explode("-",$_GET['paramCode']);	
				}
				else if(isset($_POST['paramCode']))
				{
					$paramCodeArray = explode("-",$_POST['paramCode']);
				}
				$infoArray = array();	
		
				$Fl = new Flight();
				$flightInfo = $Fl->GetFlightInfo($flightId);
				unset($Fl);
				
				$bruType = $flightInfo['bruType'];
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($bruType);
				$gradiApTableName = $bruInfo['gradiApTableName'];
				$gradiBpTableName = $bruInfo['gradiBpTableName'];
				
				for($i = 0; $i < count($paramCodeArray); $i++)
				{
					$paramCode = $paramCodeArray[$i];
					$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);
					
					if($paramInfo["paramType"] == PARAM_TYPE_AP)
					{
						$infoArray[] = $paramInfo['name'].", ".
							$paramInfo['dim'];
					}
					else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
					{
						$infoArray[] = $paramInfo['name'];			
					}
				}
				unset($Bru);
				
				echo(json_encode($infoArray));
			}
			else
			{
				error_log("Param code not set. ParamCode: ".$_GET['paramCode']." asyncParamInfo.php ");
				echo("Param code not set. Page asyncParamInfo.php");			
			}
		
		}
		else
		{
			error_log("Flight id not set. FlightId: ".$_GET['flightId']." asyncParamInfo.php ");
			echo("Flight id not set. Page asyncParamInfo.php");	
		}
	}
	else if(isset($_POST['action']) && ($_POST['action'] == CHART_PARAM_INFO_SET_PARAM_COLOR))
	{
		if(isset($_POST['bruType']))
		{
			$bruType = $_POST['bruType'];
		
			if(isset($_POST['paramCode']))
			{
				$paramCode = $_POST['paramCode'];
				
				$color = '';			
				if(isset($_POST['color']))
				{
					$color = $_POST['color'];
				}
				
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($bruType);
				$gradiApTableName = $bruInfo['gradiApTableName'];
				$gradiBpTableName = $bruInfo['gradiBpTableName'];
	
				$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);
		
				if($paramInfo["paramType"] == PARAM_TYPE_AP)
				{
					$Bru->UpdateParamColor($gradiApTableName, $paramCode, $color);
				}
				else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
				{
					$Bru->UpdateParamColor($gradiBpTableName, $paramCode, $color);
				}
	
				unset($Bru);
			}
			else
			{
				error_log("Param code not set. ParamCode value: ".$_POST['paramCode'].". Page asyncParamInfo.php");
				echo("Param code not set. Page asyncParamInfo.php");
			}	
		}
		else
		{
			error_log("BruType not set. BruType value: ".$_POST['bruType'].". Page asyncParamInfo.php");
			echo("BruType not set. Page asyncParamInfo.php");
		}
	}
	else if(isset($_GET['action']) && ($_GET['action'] == CHART_PARAM_INFO_GET_PARAM_COLOR) || 
		isset($_POST['action']) && ($_POST['action'] == CHART_PARAM_INFO_GET_PARAM_COLOR))
	{
		if(isset($_POST['flightId']) || isset($_GET['flightId']))
		{
			if(isset($_POST['flightId']))
			{
				$flightId = $_POST['flightId'];
			}
			else if(isset($_GET['flightId']))
			{
				$flightId = $_GET['flightId'];
			}
	
			if(isset($_GET['paramCode']) || isset($_POST['paramCode']))
			{
				$paramCode = '';
				
				if(isset($_POST['paramCode']))
				{
					$paramCode = $_POST['paramCode'];
				}
				else if(isset($_GET['paramCode']))
				{
					$paramCode = $_GET['paramCode'];
				}
					
				$color = 'ffffff';
	
				$Fl = new Flight();
				$flightInfo = $Fl->GetFlightInfo($flightId);
				unset($Fl);
					
				$bruType = $flightInfo['bruType'];
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($bruType);
				$gradiApTableName = $bruInfo['gradiApTableName'];
				$gradiBpTableName = $bruInfo['gradiBpTableName'];
	
				$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);
	
				if($paramInfo["paramType"] == PARAM_TYPE_AP)
				{
					$color = $Bru->GetParamColor($gradiApTableName, $paramCode);
				}
				else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
				{
					$color = $Bru->GetParamColor($gradiBpTableName, $paramCode);
				}
	
				unset($Bru);
				
				echo json_encode($color);
			}
			else
			{
				error_log("Param code not set. ParamCode value: ".$_POST['paramCode'].". Page  asyncParamInfo.php ");
				echo("Param code not set. Page asyncParamInfo.php");
			}
		}
		else
		{
			error_log("BruType not set. BruType value: ".$_POST['bruType'].". Page  asyncParamInfo.php ");
			echo("BruType not set. Page asyncParamInfo.php");
		}
	}
	else
	{
		error_log("Action not set or incorect. Action value: ".$_POST['action'].". Page  asyncParamInfo.php ");
		echo("Action not set or incorect. Page asyncParamInfo.php");
	}
}
else
{
	echo("Authorization error. Page asyncParamInfo.php");
}


?>