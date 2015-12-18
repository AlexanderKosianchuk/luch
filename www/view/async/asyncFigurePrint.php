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
		if(isset($_GET['flightId']) && $_GET['flightId'] != null)
		{
			$flightId = $_GET['flightId'];
		}
		else if(isset($_POST['flightId']) && $_POST['flightId'] != null)
		{
			$flightId = $_POST['flightId'];
		}
		
		if(isset($_POST['data']) && $_POST['data'] != null)
		{
			$aoData = $_POST['data'];
			$sEcho = $aoData[sEcho]['value'];
			$iDisplayStart = $aoData[iDisplayStart]['value'];
			$iDisplayLength = $aoData[iDisplayLength]['value'];
		}
		else
		{
			echo("Echo not received");
			exit();
		}
		
		
		$Fl = new Flight();
		$flightInfo = $Fl->GetFlightInfo($flightId);
		$bruType = $flightInfo['bruType']; 
		$apTableName = $flightInfo['apTableName'];
		$bpTableName = $flightInfo['bpTableName'];
		$startCopyTime = $flightInfo['startCopyTime'];
		unset($Fl);
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$prefixArr = $Bru->GetBruApGradiPrefixes($bruType);
		$stepLength = $bruInfo['stepLength'];
		$stepDivider = $bruInfo['stepDivider'];
		$startCopyTime = $flightInfo['startCopyTime'];
		$excListTableName = $bruInfo['excListTableName'];
		$apGradiTableName = $bruInfo['gradiApTableName'];
		$bpGradiTableName = $bruInfo['gradiBpTableName'];
		
		$Frame = new Frame();
		$framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix

		unset($Frame);
		
		if($iDisplayLength == -1)
		{
			$iDisplayLength = $framesCount * $stepDivider;
		}
	
		$startFrame = floor($iDisplayStart / $stepDivider);
		$endFrame = ceil($startFrame + ($iDisplayLength / $stepDivider));
		
		if(isset($_GET['paramsCode']) || isset($_POST['paramsCode']))
		{
			if(isset($_GET['paramsCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_GET['paramsCode']);
			}
			else if(isset($_POST['paramsCode']))
			{
				$paramCodeArr = 
					(array)explode("-",$_POST['paramsCode']);			
			}
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$stepLength = $bruInfo['stepLength'];
			$stepDivider = $bruInfo['stepDivider'];
			$startCopyTime = $flightInfo['startCopyTime'];
			$excListTableName = $bruInfo['excListTableName'];
			$apGradiTableName = $bruInfo['gradiApTableName'];
			$bpGradiTableName = $bruInfo['gradiBpTableName'];
			
			$Ch = new Channel();
			$normParam = $Ch->NormalizeTime($stepDivider, $stepLength,
				$framesCount, $startCopyTime, $startFrame, $endFrame);
			$globalRawParamArr = array();
			array_push($globalRawParamArr, $normParam);
			
			for($i = 0; $i < count($paramCodeArr); $i++)
			{
				$paramType = $Bru->GetParamType($paramCodeArr[$i],
					$apGradiTableName, $bpGradiTableName);
				
				if($paramType == PARAM_TYPE_AP)
				{
					$paramInfo = $Bru->GetParamInfoByCode($apGradiTableName, $bpGradiTableName, 
							$paramCodeArr[$i], PARAM_TYPE_AP);

					$normParam = $Ch->GetNormalizedApParam($apTableName, 
						$stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
						$startFrame, $endFrame);
					
					array_push($globalRawParamArr, $normParam);
				}
				else if($paramType == PARAM_TYPE_BP)
				{
					$channelsAndMasks = $Bru->GetChannelsAndMasksByCode(
						$bpGradiTableName, $paramCodeArr[$i]);
					$normParam = $Ch->NormalizeBpParam($bpTableName,
						$stepDivider, $channelsAndMasks[0], $channelsAndMasks[1],
						$startFrame, $endFrame);
					array_push($globalRawParamArr, $normParam);
				}
			}
			
			unset($Ch);
			unset($Bru);
			
			$totalRecords = $framesCount * $stepDivider;
			
			$aaData["sEcho"] = $sEcho;
			$aaData["iTotalRecords"] = $totalRecords;
			$aaData["iTotalDisplayRecords"] = $totalRecords;
			$aaData["aaData"] = array();
	
			if($iDisplayLength <= $totalRecords)
			{
				if(count($globalRawParamArr[1]) < $iDisplayLength)
				{
					$iDisplayLength = count($globalRawParamArr[1]);
				}
				
				for($i = 0; $i < $iDisplayLength; $i++)
				{
					$figPrRow = array();
					//array_push($figPrRow, $globalRawParamArr[0][$i][0]);//time
					for($j = 0; $j < count($globalRawParamArr); $j++)
					{
						array_push($figPrRow, $globalRawParamArr[$j][$i]);
					}
					array_push($aaData["aaData"], $figPrRow);
				}
			}
			else
			{
				for($i = 0; $i < $totalRecords; $i++)
				{
				$figPrRow = array();
					//array_push($figPrRow, $globalRawParamArr[0][$i][0]);//time
					for($j = 0; $j < count($globalRawParamArr); $j++)
					{
						array_push($figPrRow, $globalRawParamArr[$j][$i]);
					}
					array_push($aaData["aaData"], $figPrRow);
				}
			}
					
			echo json_encode($aaData);
		}
		else 
		{
			error_log("Params not selected. Page asyncFigurePrint.php");
			echo("Params not selected. Page asyncFigurePrint.php");
		}
	}
	else
	{
		error_log("Flight id not set. Page asyncFigurePrint.php");
		echo("Flight id not set. Page asyncFigurePrint.php");
	}
}
else
{
	error_log("Authorization error. Page asyncFigurePrint.php");
	echo("Authorization error. Page asyncFigurePrint.php");
}

?>