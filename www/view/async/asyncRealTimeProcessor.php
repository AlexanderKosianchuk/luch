<?
require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if(isset($_POST['action']) && ($_POST['action'] != NULL))
	{
		$action = $_POST['action'];
	
		if($action == FLIGHT_CREATE)
		{
			//update flight info
			if(isset($_POST['bort']) && ($_POST['bort'] != NULL))
			{
				$bort = $_POST['bort'];
				$flightInfo['bort'] = $bort;
			}
				
			if(isset($_POST['voyage']) && ($_POST['voyage'] != NULL))
			{
				$voyage = $_POST['voyage'];
				$flightInfo['voyage'] = $voyage;
			}
				
			if(($_POST['copyCreationTime'] != NULL) && ($_POST['copyCreationDate'] != NULL) &&
			isset($_POST['copyCreationTime']) && isset($_POST['copyCreationDate']))
			{
				$copyCreationTime = $_POST['copyCreationTime'];
				$copyCreationDate = $_POST['copyCreationDate'];
				if(strlen($copyCreationTime) > 5)
				{
					$startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime);
				}
				else
				{
					$startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime . ":00");
				}
						
				$flightInfo['startCopyTime'] = $startCopyTime;
			}
			
			if(isset($_POST['bruType']) && ($_POST['bruType'] != NULL))
			{
				$bruType = $_POST['bruType'];
				$flightInfo['bruType'] = $bruType;
			}
			
			if(isset($_POST['performer']) && ($_POST['performer'] != NULL))
			{
				$performer = $_POST['performer'];
				$flightInfo['performer'] = $performer;
			}
			if(isset($_POST['centring']) && ($_POST['centring'] != NULL))
			{
				$centring = $_POST['centring'];
				$flightInfo['centring'] = $centring;
			}
			if(isset($_POST['engines']) && ($_POST['engines'] != NULL))
			{
				$engines = $_POST['engines'];
				$flightInfo['engines'] = $engines;
			}
			
			$Fl = new Flight();
			
			$flightId = $Fl->InsertNewFlight(0, 0, 0, 0, 0, "");
			$Fl->UpdateFlightInfo($flightId, $flightInfo);
			$Bru = new Bru();
			$gradiApByPrefixes = $Bru->GetBruApGradiPrefixOrganized($bruType);
			unset($Bru);
			$Fl->CreateFlightParamTables($flightId, $gradiApByPrefixes);
			unset($Fl);
			
			echo($flightId);
			exit();	
		}
		else if($action == FLIGHT_APPEND_FRAME)
		{
			if(isset($_POST['flightId']) && ($_POST['flightId'] != NULL))
			{
				$flightId = $_POST['flightId'];
			}
			
			if(isset($_POST['frameNum']) && ($_POST['frameNum'] != NULL))
			{
				$frameNum = $_POST['frameNum'];
			}
	
			if(isset($_POST['frame']) && ($_POST['frame'] != NULL))
			{
				$frame = $_POST['frame'];
			}
	
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($flightId);
			$tableNameAp = $flightInfo['apTableName'];
			$tableNameBp = $flightInfo['bpTableName'];
			$bruType = $flightInfo["bruType"];
			$startCopyTime = $flightInfo["startCopyTime"];
			unset($Fl);
		
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$gradiApByPrefixes = $Bru->GetBruApGradiPrefixOrganized($bruType);
			$prefixFreqArr = $Bru->GetBruApGradiPrefixFreq($bruType);
			$wordLength = $bruInfo['wordLength'];
			$stepLength = $bruInfo['stepLength'];
			$gradiAp = $Bru->GetBruApGradi($bruType);
			$gradiBp = $Bru->GetBruBpGradi($bruType);
			unset($Bru);
		
			$Fr = new Frame();
	
			$c = new DataBaseConnector();
			$link = $c->Connect();
	
			$apPhisicsByPrefixes = array();
			$algHeap = array();
	
			foreach($gradiApByPrefixes as $prefix => $gradiAp)
			{
				$channelFreq = $prefixFreqArr[$prefix];
				$splitedFrame = str_split($frame, $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need	
				$phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, 
						$startCopyTime, 
						$stepLength, 
						$channelFreq, 
						$frameNum, 
						$gradiAp, 
						$algHeap);
				$Fr->InsertApFrame($phisicsFrame, $tableNameAp, $prefix, $link);
			}
			
			$bpPhisics = $Fr->ConvertFrameToBinaryParams($splitedFrame, 
					$frameNum, 
					$gradiBp);
			
			$Fr->InsertBpFrame($bpPhisics, $tableNameBp, $link);
				
			$c->Disconnect();
	
			exit();
		}
	// 		else if($action == FLIGHT_PROC)
	// 		{
	// 			$fp = fopen($tempFileRoot, "w");
	// 			fwrite($fp, json_encode("proccess"));
	// 			fclose($fp);
				
	// 			$Fl = new Flight();
	// 			$flightInfo = $Fl->GetFlightInfo($flightId);
	// 			$apTableName = $flightInfo['apTableName'];
	// 			$bpTableName = $flightInfo['bpTableName'];
	// 			$excEventsTableName = $flightInfo['exTableName'];
	// 			$flightId = $flightInfo['id'];
	// 			$tableGuid = substr($apTableName, 0, 14);
	// 			unset($Fl);
				
	// 			$Bru = new Bru();
	// 			$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
	// 			$excListTableName = $bruInfo['excListTableName'];
	// 			$apGradiTableName = $bruInfo['gradiApTableName'];
	// 			$bpGradiTableName = $bruInfo['gradiBpTableName'];
					
	// 			if ($excListTableName != "")
	// 			{
	// 				$Bru = new Bru();
	// 				$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
	// 				$excListTableName = $bruInfo['excListTableName'];
	// 				$apGradiTableName = $bruInfo['gradiApTableName'];
	// 				$bpGradiTableName = $bruInfo['gradiBpTableName'];
			
	// 				$FEx = new FlightException();
	// 				$flightExTableName = $FEx->
	// 					CreateFlightExceptionTable($flightId, $tableGuid);
	// 				//Get exc refParam list
	// 				$excRefParamsList = $FEx->
	// 					GetFlightExceptionRefParams($excListTableName);
	// 				unset($Bru);
					
	// 				$exList = $FEx->GetFlightExceptionTable(
	// 					$excListTableName);
		
	// 				//file can be accesed by ajax what can cause warning
	// 				error_reporting(E_ALL ^ E_WARNING);
					
	// 				//perform proc be cached table
	// 				for($i = 0; $i < count($exList); $i++)
	// 				{
	// 					$fp = fopen($tempFileRoot, "w");
	// 					fwrite($fp, json_encode($exList[$i]['code']));
	// 					fclose($fp);
						
	// 					$FEx->PerformProcessingByExceptions($i,
	// 						$exList, $flightExTableName,
	// 						$apTableName, $bpTableName);
	// 				}
					
	// 				error_reporting(E_ALL);
					
	// 				unlink($tempFileRoot);
	// 				exit();
	// 			}
	// 			else
	// 			{
	// 				exit();				
	// 			}
	// 		}
	
		else
		{
			//log
			echo("Action not set. Page asyncRealTimeProcessor.php");
			//echo("<script>location.href=location.protocol + '//' + location.host + '/fileUploader.php'</script>");
	  		exit();	
		}
			
	}
	else
	{
		echo("Flight id not set. Page asyncRealTimeProcessor.php");   
	}
}
else
{
	echo("Authorization error. Page asyncRealTimeProcessor.php");
}
?>
