<?

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if(isset($_GET['flightId']) || isset($_POST['flightId']))
	{
		if(isset($_GET['flightId']))
		{
			$flightId = $_GET['flightId'];
		} 
		else
		{
			$flightId = $_POST['flightId'];
		} 
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType']; 
		$apTableName = $flightInfo['apTableName'];
		$bpTableName = $flightInfo['bpTableName'];
		$startCopyTime = $flightInfo['startCopyTime'];
		unset($Fl);
		
		$Bru = new Bru();
		$prefixArr = $Bru->GetBruApGradiPrefixes($flightInfo['bruType']);
		unset($Bru);
		
		$Frame = new Frame();
		$framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
		unset($Frame);
		
		if(isset($_GET['startFrame']) && ($_GET['startFrame'] != NULL) &&
			isset($_GET['endFrame']) && ($_GET['endFrame'] != NULL))
		{
			$startFrame = $_GET['startFrame'];
			$endFrame = $_GET['endFrame'];
			
			if($endFrame > $framesCount)
			{
				$endFrame = $framesCount;			
			}
		}
		else if(isset($_POST['startFrame']) && ($_POST['startFrame'] != NULL) &&
				isset($_POST['endFrame']) && ($_POST['endFrame'] != NULL))
		{
			$startFrame = $_POST['startFrame'];
			$endFrame = $_POST['endFrame'];
		
			if($endFrame > $framesCount)
			{
				$endFrame = $framesCount;
			}
		}
		else
		{
			$startFrame = 0;
			$endFrame = $framesCount;
		}
		
		if(isset($_GET['totalSeriesCount']) && 
			($_GET['totalSeriesCount'] != NULL))
		{
			$seriesCountDivider = $_GET['totalSeriesCount'];
		}
		else if(isset($_POST['totalSeriesCount']) &&
				($_POST['totalSeriesCount'] != NULL))
		{
			$seriesCountDivider = $_POST['totalSeriesCount'];
		}
		else
		{
			$seriesCountDivider = 1;		
		}
		
		if(isset($_GET['paramApCode']) || isset($_POST['paramApCode']))
		{
			if(isset($_GET['paramApCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_GET['paramApCode']);
			}
			else if(isset($_POST['paramApCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_POST['paramApCode']);			
			}
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType); 
	
			$Ch = new Channel();
			$paramValuesArr = array();
			
			for($i = 0; $i < count($paramCodeArr); $i++)
			{
				$code = $paramCodeArr[$i];
				$paramInfo = $Bru->GetParamInfoByCode($bruInfo["gradiApTableName"], 
						$bruInfo["gradiBpTableName"], 
						$code, PARAM_TYPE_AP);
				$prefix = $paramInfo["prefix"];
	
				$syncParam = $Ch->GetFlightParamWithExactSection($apTableName,
						$seriesCountDivider, $startFrame, $endFrame,
						$code, $prefix, $paramInfo["freq"], $framesCount);
				
				array_push($paramValuesArr, $syncParam);
			}
			unset($Bru);
			unset($Ch);
					
			if(count($paramCodeArr) > 1)
			{
				echo json_encode($paramValuesArr);
			}
			else
			{
				echo json_encode($syncParam);
			}
			
			
		}
		else if(isset($_GET['paramBpCode']) || 
			isset($_POST['paramBpCode']))
		{
			if(isset($_GET['paramBpCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_GET['paramBpCode']);
			}
			else if(isset($_POST['paramBpCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_POST['paramBpCode']);			
			}	
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$stepLength = $bruInfo['stepLength']; 
			$bpGradiTableName = $bruInfo['gradiBpTableName'];
			$channelsAndMasks = $Bru->GetChannelsAndMasksByCode(
				$bpGradiTableName, $paramCodeArr);
			unset($Bru);
			
			$Ch = new Channel();
			$paramValuesArr = array();
			for($i = 0; $i < count($paramCodeArr); $i++)
			{
				$syncParam = $Ch->GetBinaryParam($bpTableName, 
					$startCopyTime, $stepLength, $startFrame, $endFrame, 
					$channelsAndMasks[0], $channelsAndMasks[1]);
				array_push($paramValuesArr, $syncParam);
			}
			unset($Ch);
	
			//already encoded to json in GetBinaryParam
			if(count($paramCodeArr) > 1)
			{
				echo $paramValuesArr;
			}
			else
			{
				echo $syncParam;
			}
		}
	}
	else
	{
		echo("Flight id not set. Page asyncParamTrans.php");
	}
}
else
{
	echo("Authorization error. Page asyncParamTrans.php");
}
?>