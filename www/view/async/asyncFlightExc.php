<?php

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if(isset($_GET['flightId']) || isset($_POST['flightId']))
	{
		$flightId = 0;
		if(isset($_GET['flightId']))
		{
			$flightId = $_GET['flightId'];
		}
		else if(isset($_POST['flightId']))
		{
			$flightId = $_POST['flightId'];
		}
	
		//chart flight exceptions serving
		if(isset($_GET['refParam']))
		{
			$refParam = $_GET['refParam'];
	
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($flightId);
			unset($Fl);
	
			$excTableName = $flightInfo['exTableName'];
	
			if($excTableName != '')
			{
				$bruType = $flightInfo['bruType'];
				$startCopyTime = $flightInfo['startCopyTime'];
				$apTableName = $flightInfo['apTableName'];
	
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($bruType);
				$stepLength = $bruInfo['stepLength'];
				$gradiApTableName = $bruInfo['gradiApTableName'];
				$gradiBpTableName = $bruInfo['gradiBpTableName'];
				$excListTableName = $bruInfo['excListTableName'];
				$paramType = $Bru->GetParamType($refParam,
				$gradiApTableName,$gradiBpTableName);
				$excList = array();
				if($paramType == PARAM_TYPE_AP)
				{
					$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, 
							$gradiBpTableName, $refParam, PARAM_TYPE_AP);
					
					$apTableName = $apTableName . "_" . $paramInfo["prefix"];
					
					$FEx = new FlightException();
					$excList = (array)$FEx->GetExcApByCode($excTableName, 
							$refParam, $apTableName, $excListTableName);
					unset($FEx);
				}
				else if($paramType == PARAM_TYPE_BP)
				{
					$FEx = new FlightException();
					$excList = (array)$FEx->GetExcBpByCode($excTableName, $refParam,
							$stepLength, $startCopyTime, $excListTableName);
					unset($FEx);
				}
				unset($Bru);
				echo json_encode($excList);
			}
			else
			{
				echo json_encode('null');
			}
	
		}
	
		//tuner flight exceptions serving
		if(isset($_POST['excId']))
		{
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($flightId);
			unset($Fl);
			
			$excTableName = $flightInfo['exTableName'];
			
			if($_POST['excId'] != NULL)
			{
				$excId = $_POST['excId'];
		
				if(isset($_POST['falseAlarmState']))
				{
					$falseAlarmState = $_POST['falseAlarmState'];
	
					$FEx = new FlightException();
					$excList = (array)$FEx->UpdateFalseAlarmState($excTableName,
							$excId, $falseAlarmState);
					unset($FEx);
						
				}
		
				if(isset($_POST['userComment']))
				{
					$userComment = $_POST['userComment'];
					
					$FEx = new FlightException();
					$excList = (array)$FEx->UpdateUserComment($excTableName,
							$excId, $userComment);
					unset($FEx);
		
				}
		
			}
			else
			{
				echo("Flight exception id error ". $_POST['excId'] .". Page asyncFlightExc.php");
			}
		}
	}
	else
	{
		echo("Flight id not set. Page asyncFlightExc.php");
	}
}
else
{
	echo("Authorization error. Page asyncFlightExc.php");
}

?>