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
		
		if(isset($_POST['action']))
		{
			$action = $_POST['action'];
			
			if($action == COORDINATES_ACTION_GET_COORD)
			{
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
				else
				{
					$startFrame = 0;
					$endFrame = $framesCount;
				}
			
				$paramCodeArr = array(GPS_LONG_MIN, GPS_LONG_SEC, GPS_LAT_MIN, GPS_LAT_SEC);
				
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
							0, $startFrame, $endFrame,
							$code, $prefix, $paramInfo["freq"]);
					
					$paramValuesArr[$paramCodeArr[$i]] = $syncParam;
				}
				
				$valueIndexInRes = 1;
				$coordFramesCount = count($syncParam);
				$coordArr = array();
				for($j = 0; $j < $coordFramesCount; $j++)
				{
					$LONG_GRAD = intval($paramValuesArr[$paramCodeArr[0]][$j][$valueIndexInRes] / 100);
					
					$LONG_MIN = ($paramValuesArr[$paramCodeArr[0]][$j][$valueIndexInRes] / 100 - 
						$LONG_GRAD) * 100;
					$LONG_SEC = $paramValuesArr[$paramCodeArr[1]][$j][$valueIndexInRes] / 10000;
					$LONG = $LONG_GRAD + ($LONG_MIN + $LONG_SEC) / 60;
					
					//-----
					
					$LAT_GRAD = intval($paramValuesArr[$paramCodeArr[2]][$j][$valueIndexInRes] / 100);
					
					$LAT_MIN = ($paramValuesArr[$paramCodeArr[2]][$j][$valueIndexInRes] / 100 - 
						$LAT_GRAD) * 100;
					$LAT_SEC = $paramValuesArr[$paramCodeArr[3]][$j][$valueIndexInRes] / 10000;
					$LAT = $LAT_GRAD + ($LAT_MIN + $LAT_SEC) / 60;
					
					array_push($coordArr, array($LONG, $LAT));
				}
				
				unset($Bru);
				unset($Ch);
					
				echo json_encode($coordArr);
			}
			else if($action == COORDINATES_ACTION_GET_PARAMS)
			{
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
				
				if(isset($_POST['frame']) && ($_POST['frame'] != NULL))
				{
					$frame = $_POST['frame'];
				}
				else
				{
					$frame = 0;
				}
					
				$paramCodeArr = array(COORD_TG, COORD_KM, COORD_KR);
				
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
						
					$syncParam = $Ch->GetFlightParamValue($apTableName,
						$frame, $code, $prefix);
					
					$paramValuesArr[$code] = $syncParam;
				}
	
				unset($Bru);
				unset($Ch);
					
				echo json_encode($paramValuesArr);
				
			}
			else
			{
				echo("Unexpected action. Page asyncCoordinateTrans.php");
			}
		}
		else
		{
			echo("Action is not set. Page asyncCoordinateTrans.php");
		}
	}
	else
	{
		echo("Flight id is not set. Page asyncCoordinateTrans.php");
	}
}
else 
{
	echo("Authorization error. Page asyncCoordinateTrans.php");
}

?>