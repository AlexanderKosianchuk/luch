<?php

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if((isset($_POST[DIAGNOSTIC_ACTION])) && ($_POST[DIAGNOSTIC_ACTION] != NULL))
	{
		$action = $_POST[DIAGNOSTIC_ACTION];
		
		switch($action)
		{
			case GET_ETALON_ENGINES:
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engines = GetEngines($etalonId);
	
					echo(json_encode($engines));
					exit();
				}
				else
				{
					echo("Error during ". GET_ETALON_ENGINES . ". " . DIAGNOSTIC_ETALON_ID . " not set. Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_ETALON_ENGINES . ". " . DIAGNOSTIC_ETALON_ID . " not set. Page asyncDiagnosticServer.php");
					exit();
				}
				break;
			case GET_ENGINE_SLICES:
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL) && 
					isset($_POST[DIAGNOSTIC_ENGINE_SERIAL]) && ($_POST[DIAGNOSTIC_ENGINE_SERIAL] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engineSerial = $_POST[DIAGNOSTIC_ENGINE_SERIAL];
					//error_log($etalonId . " " . $engineSerial);
					$engineSlices = GetEngineSlices($etalonId, $engineSerial);
					echo(json_encode($engineSlices));
					exit();
				}
				else 
				{
					echo("Error during ". GET_ENGINE_SLICES . ". " . DIAGNOSTIC_ETALON_ID . " or ". DIAGNOSTIC_ENGINE_SERIAL ." not set (" .
							$etalonId . ", " . $engineSerial . "). Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_ENGINE_SLICES . ". " . DIAGNOSTIC_ETALON_ID . " or ". DIAGNOSTIC_ENGINE_SERIAL ." not set (" .
							$etalonId . ", " . $engineSerial . "). Page asyncDiagnosticServer.php");
					exit();
				}
			break;
			
			case GET_ENGINE_DISCREP:
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL) &&
					isset($_POST[DIAGNOSTIC_ENGINE_SERIAL]) && ($_POST[DIAGNOSTIC_ENGINE_SERIAL] != NULL) &&
					isset($_POST[DIAGNOSTIC_SLICE]) && ($_POST[DIAGNOSTIC_SLICE] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engineSerial = $_POST[DIAGNOSTIC_ENGINE_SERIAL];
					$engineSlice = $_POST[DIAGNOSTIC_SLICE];
					
					$engineDiscreps = GetEngineDiscreps($etalonId, $engineSerial, $engineSlice);
					
					echo(json_encode($engineDiscreps));
					exit();
				}
				else
				{
					echo("Error during ". GET_ENGINE_SLICES . ". " . DIAGNOSTIC_ENGINE_SERIAL . " or " . DIAGNOSTIC_SLICE . " not set. Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_ENGINE_SLICES . ". " . DIAGNOSTIC_ENGINE_SERIAL . " or " . DIAGNOSTIC_SLICE . " not set. Page asyncDiagnosticServer.php");
					exit();
				}
			break;
	
			case GET_DISCREP_VALS:
				
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL) &&
					isset($_POST[DIAGNOSTIC_ENGINE_SERIAL]) && ($_POST[DIAGNOSTIC_ENGINE_SERIAL] != NULL) &&
					isset($_POST[DIAGNOSTIC_SLICE]) && ($_POST[DIAGNOSTIC_SLICE] != NULL) &&
					isset($_POST[DIAGNOSTIC_ABSCISSA]) && ($_POST[DIAGNOSTIC_ABSCISSA] != NULL) &&
					isset($_POST[DIAGNOSTIC_ORDINATE]) && ($_POST[DIAGNOSTIC_ORDINATE] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engineSerial = $_POST[DIAGNOSTIC_ENGINE_SERIAL];
					$engineSlice = $_POST[DIAGNOSTIC_SLICE];
					$discrepAbscissa = $_POST[DIAGNOSTIC_ABSCISSA];
					$discrepOrdinate = $_POST[DIAGNOSTIC_ORDINATE];
				
					$engineDiscrepVals = GetDiscrepVals($etalonId, $engineSerial, $engineSlice, $discrepAbscissa, $discrepOrdinate);
				
					echo(json_encode($engineDiscrepVals));
					exit();
				}
				else
				{
					echo("Error during ". GET_DISCREP_VALS . ". One of expected params not set. Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_DISCREP_VALS . ". One of expected params not set. Page asyncDiagnosticServer.php");
					exit();
				}			
			break;	
			
			case GET_DISCREP_LIMITS:
					
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL) &&
				isset($_POST[DIAGNOSTIC_ENGINE_SERIAL]) && ($_POST[DIAGNOSTIC_ENGINE_SERIAL] != NULL) &&
				isset($_POST[DIAGNOSTIC_SLICE]) && ($_POST[DIAGNOSTIC_SLICE] != NULL) &&
				isset($_POST[DIAGNOSTIC_DISCREP]) && ($_POST[DIAGNOSTIC_DISCREP] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engineSerial = $_POST[DIAGNOSTIC_ENGINE_SERIAL];
					$engineSlice = $_POST[DIAGNOSTIC_SLICE];
					$discrep = $_POST[DIAGNOSTIC_DISCREP];
					
					$engineDiscrepLimits = GetDiscrepLimits($etalonId, $engineSerial, $engineSlice, $discrep);
						
					echo(json_encode($engineDiscrepLimits));
					exit();
				}
				else
				{
					echo("Error during ". GET_DISCREP_VALS . ". One of expected params not set. Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_DISCREP_VALS . ". One of expected params not set. Page asyncDiagnosticServer.php");
					exit();
				}
			break;
			
			case GET_DISCREP_REPORT:
				if(isset($_POST[DIAGNOSTIC_ETALON_ID]) && ($_POST[DIAGNOSTIC_ETALON_ID] != NULL) &&
				isset($_POST[DIAGNOSTIC_ENGINE_SERIAL]) && ($_POST[DIAGNOSTIC_ENGINE_SERIAL] != NULL) &&
				isset($_POST[DIAGNOSTIC_FROM_DATE]) && isset($_POST[DIAGNOSTIC_TO_DATE]) &&
				isset($_POST[DIAGNOSTIC_DISCREP_TYPE]) && ($_POST[DIAGNOSTIC_DISCREP_TYPE] != NULL))
				{
					$etalonId = $_POST[DIAGNOSTIC_ETALON_ID];
					$engineSerialArr = (array)$_POST[DIAGNOSTIC_ENGINE_SERIAL];
					$fromDate = $_POST[DIAGNOSTIC_FROM_DATE];
					$toDate = $_POST[DIAGNOSTIC_TO_DATE];
					
					if($fromDate != "")
					{
						$fromDate = strtotime($_POST[DIAGNOSTIC_FROM_DATE] . " " . "00:00:00");
					}
					
					if($toDate != "")
					{
						$toDate = strtotime($_POST[DIAGNOSTIC_TO_DATE] . " " . "00:00:00");
					}
					
					$discrepLimitTypeArr = (array)$_POST[DIAGNOSTIC_DISCREP_TYPE];
									
					$report = GetReport($etalonId, $engineSerialArr, $fromDate, $toDate, $discrepLimitTypeArr);
						
					echo(json_encode($report));
					exit();
				}
				else
				{
					echo("Error during ". GET_DISCREP_REPORT . ". One of expected params not set. Page asyncDiagnosticServer.php");
					error_log("Error during ". GET_DISCREP_REPORT . ". One of expected params not set. Page asyncDiagnosticServer.php");
					exit();
				}
				break;
			
			default:
				echo("Unexpected action. Page asyncDiagnosticServer.php");
				error_log("Unexpected action. Page asyncDiagnosticServer.php");
				exit();
			break;
		}
		
	}
	else 
	{
		echo("Action not set. Page asyncDiagnosticServer.php");
		error_log("Action not set. Page asyncDiagnosticServer.php");
		exit();
	}

}
else
{
	echo("Authorization error. Page asyncDiagnosticServer.php");
}

function GetEngines($extEtalonId)
{
	$etalonId = $extEtalonId;
	
	$engines = "";
	
	$Eng = new Engine();
	
	if($etalonId != DIAGNOSTIC_IGNORE_ETALON)
	{
		$enginesByEtalon = $Eng->SelectEnginesSerialsByEtalonsList();
		$engines = $enginesByEtalon[$etalonId];
	}
	else
	{
		$engines = $Eng->SelectEnginesSerialsList();
	}

	unset($Eng);

	return $engines;
}

function GetEngineSlices($extEtalonId, $extEngineSerial)
{
	$etalonId = $extEtalonId;
	$engineSerial = $extEngineSerial;
	
	$Eng = new Engine();
	if($etalonId != DIAGNOSTIC_IGNORE_ETALON)
	{
		$engineSerialInfo = $Eng->GetEngineInfoBySerialAndEtalon($etalonId, $engineSerial);
	}
	else
	{
		$engineSerialInfo = $Eng->GetEngineInfo($engineSerial);
	}
	$engineSlices = explode(", ", $engineSerialInfo["sliceCode"]);
	unset($Eng);

	return $engineSlices;	
}

function GetEngineDiscreps($extEtalonId, $extEngineSerial, $extSlice)
{
	$etalonId = $extEtalonId;
	$engineSerial = $extEngineSerial;
	$slice = $extSlice;

	$engineDiscreps = "";
	$Eng = new Engine();
	if($etalonId != DIAGNOSTIC_IGNORE_ETALON)
	{
		$engineDiscreps = $Eng->GetEngineDiscrepsBySlices($engineSerial, $slice, $etalonId);
	}
	else 
	{
		$engineDiscreps = $Eng->GetEngineDiscrepsBySlices($engineSerial, $slice);
		
	}
	unset($Eng);

	return $engineDiscreps;
}

function GetDiscrepVals($extEtalonId, $extEngineSerial, $extSlice, $extDiscrepAbscissa, $extDiscrepOrdinate)
{
	$etalonId = $extEtalonId;
	$engineSerial = $extEngineSerial;
	$slice = $extSlice;
	$discrepAbscissa = $extDiscrepAbscissa;
	$discrepOrdinate = $extDiscrepOrdinate;
	
	$Eng = new Engine();
	if($etalonId != DIAGNOSTIC_IGNORE_ETALON)
	{
		$engineDiscrepsVals = $Eng->GetEngineDiscrepValuesByAbscissaOrdinate($engineSerial, $slice, $discrepAbscissa, $discrepOrdinate, $etalonId);
	}
	else
	{
		$engineDiscrepsVals = $Eng->GetEngineDiscrepValuesByAbscissaOrdinate($engineSerial, $slice, $discrepAbscissa, $discrepOrdinate);
	}
	unset($Eng);
	
	return $engineDiscrepsVals;
}

function GetDiscrepLimits($extEtalonId, $extEngineSerial, $extSlice, $extDiscrep)
{
	$etalonId = $extEtalonId;
	$engineSerial = $extEngineSerial;
	$slice = $extSlice;
	$discrepY = $extDiscrep;
	$discrepLimits = array();
	
	//if not neural network
	if(!(in_array($discrepY, unserialize(NN_RESULT_KEYS))))
	{
		$discrepX = unserialize(DISCREP_X);
		
		$Sl = new Slice();
		error_log("get lim " . $etalonId);
		if($etalonId == DIAGNOSTIC_IGNORE_ETALON)
		{
			$sliceListArr = $Sl->GetSliceList();
			$discrepsArr = array();
			foreach($sliceListArr as $sliceInfo)
			{
				$etaloneTableName = $sliceInfo["etalonTableName"];
				$etalonRow = $Sl->GetEtalonRowUnknownPosition($etaloneTableName, 
						$sliceInfo["id"], 
						$engineSerial, 
						$slice, 
						$discrepX, 
						$discrepY);
				$discrepsArr = array_merge($discrepsArr, explode(",", $etalonRow["YAvgFlightValues"]));
			}

			if(count($discrepsArr) > 1)
			{
				//ariphmeticAvg
				$ariphmeticAvg = 0;
				for($i = 0; $i < count($discrepsArr); $i++)
				{
					$ariphmeticAvg += $discrepsArr[$i];
				}
				$ariphmeticAvg = $ariphmeticAvg / count($discrepsArr);
				
				//geometricAvg
				$geometricAvg = 0;
				for($i = 0; $i < count($discrepsArr); $i++)
				{
					$geometricAvg +=  pow(($discrepsArr[$i] - $ariphmeticAvg), 2);
				}
				$geometricAvg = $geometricAvg / (count($discrepsArr) - 1);
				
				if($geometricAvg > 0)
				{
					$limType1plus = $ariphmeticAvg + (3.295 * $geometricAvg);
					$limType1minus = $ariphmeticAvg - (3.295 * $geometricAvg);
					$limType2plus = $ariphmeticAvg + (4.319 * $geometricAvg);
					$limType2minus = $ariphmeticAvg - (4.319 * $geometricAvg);
					$limType3plus = $ariphmeticAvg + (5.003 * $geometricAvg);
					$limType3minus = $ariphmeticAvg - (5.003 * $geometricAvg);
				}
				else
				{
					$limType1plus = $ariphmeticAvg - (3.295 * $geometricAvg);
					$limType1minus = $ariphmeticAvg + (3.295 * $geometricAvg);
					$limType2plus = $ariphmeticAvg - (4.319 * $geometricAvg);
					$limType2minus = $ariphmeticAvg + (4.319 * $geometricAvg);
					$limType3plus = $ariphmeticAvg - (5.003 * $geometricAvg);
					$limType3minus = $ariphmeticAvg + (5.003 * $geometricAvg);
				}
				
				$limitsArr = array(array($limType1plus, $limType1minus), 
						array($limType2plus, $limType2minus), 
						array($limType3plus, $limType3minus));
				
				$discrepLimits = $limitsArr;
			}
		}
		else
		{
			$sliceInfo = $Sl->GetSliceInfo($etalonId);
			$etaloneTableName = $sliceInfo["etalonTableName"];
			//cant be array
			$etalonRow = $Sl->GetEtalonRowUnknownPosition($etaloneTableName, $etalonId, $engineSerial, $slice, $discrepX, $discrepY);
			$discrepsArr = $etalonRow["YAvgFlightValues"];
			$discrepsArr = explode(",", $discrepsArr);

			if(count($discrepsArr) > 1)
			{
				//ariphmeticAvg
				$ariphmeticAvg = $etalonRow["YAvgGeneral"];
				
				//geometricAvg
				$geometricAvg = 0;
				for($i = 0; $i < count($discrepsArr); $i++)
				{
					$geometricAvg += pow(($discrepsArr[$i] - $ariphmeticAvg), 2);
				}
				$geometricAvg = sqrt($geometricAvg / (count($discrepsArr) - 1));
				
				if($geometricAvg > 0)
				{
					$limType1plus = $ariphmeticAvg + (3.295 * $geometricAvg);
					$limType1minus = $ariphmeticAvg - (3.295 * $geometricAvg);
					$limType2plus = $ariphmeticAvg + (4.319 * $geometricAvg);
					$limType2minus = $ariphmeticAvg - (4.319 * $geometricAvg);
					$limType3plus = $ariphmeticAvg + (5.003 * $geometricAvg);
					$limType3minus = $ariphmeticAvg - (5.003 * $geometricAvg);
				}
				else
				{
					$limType1plus = $ariphmeticAvg - (3.295 * $geometricAvg);
					$limType1minus = $ariphmeticAvg + (3.295 * $geometricAvg);
					$limType2plus = $ariphmeticAvg - (4.319 * $geometricAvg);
					$limType2minus = $ariphmeticAvg + (4.319 * $geometricAvg);
					$limType3plus = $ariphmeticAvg - (5.003 * $geometricAvg);
					$limType3minus = $ariphmeticAvg + (5.003 * $geometricAvg);
				}
				
				$limitsArr = array(array($limType1plus, $limType1minus), 
						array($limType2plus, $limType2minus), 
						array($limType3plus, $limType3minus));
				$discrepLimits = $limitsArr;
			}
		}
		unset($Sl);
	}
	// neural network
	else 
	{
		$Eng = new Engine();
		
		if($etalonId == DIAGNOSTIC_IGNORE_ETALON)
		{
			$discrepsArr = (array)$Eng->GetEngineDiscrepValuesByAbscissaOrdinate($engineSerial, $slice, DIAGNOSTIC_ABSCISSA_FLIGHTS, $discrepY);
			
			$ariphmeticAvg = 0;
			for($i = 0; $i < count($discrepsArr); $i++)
			{
				//[1] because we need vals
				$ariphmeticAvg += $discrepsArr[$i][1];
			}
			
			$ariphmeticAvg = $ariphmeticAvg / count($discrepsArr);
			
			if($ariphmeticAvg > 0)
			{
				$limType1plus = $ariphmeticAvg + (2.5 * $ariphmeticAvg * 0.15);
				$limType1minus = $ariphmeticAvg - (2.5 * $ariphmeticAvg * 0.15);
				$limType2plus = $ariphmeticAvg + (3.5 * $ariphmeticAvg * 0.15);
				$limType2minus = $ariphmeticAvg - (3.5 * $ariphmeticAvg * 0.15);
			} 
			else 
			{
				$limType1plus = $ariphmeticAvg - (2.5 * $ariphmeticAvg * 0.15);
				$limType1minus = $ariphmeticAvg + (2.5 * $ariphmeticAvg * 0.15);
				$limType2plus = $ariphmeticAvg - (3.5 * $ariphmeticAvg * 0.15);
				$limType2minus = $ariphmeticAvg + (3.5 * $ariphmeticAvg * 0.15);	
			}
			
			$limitsArr = array(array($limType1plus, $limType1minus), array($limType2plus, $limType2minus));
			$discrepLimits = $limitsArr;
		}
		else 
		{
			$discrepsArr = (array)$Eng->GetEngineDiscrepValuesByAbscissaOrdinate($engineSerial, $slice, DIAGNOSTIC_ABSCISSA_FLIGHTS, $discrepY, $etalonId);
			
			$ariphmeticAvg = 0;
			for($i = 0; $i < count($discrepsArr); $i++)
			{
				//[1] because we need vals
				$ariphmeticAvg += $discrepsArr[$i][1];
			}
				
			$ariphmeticAvg = $ariphmeticAvg / count($discrepsArr);
				
			if($ariphmeticAvg > 0)
			{
				$limType1plus = $ariphmeticAvg + (2.5 * $ariphmeticAvg * 0.15);
				$limType1minus = $ariphmeticAvg - (2.5 * $ariphmeticAvg * 0.15);
				$limType2plus = $ariphmeticAvg + (3.5 * $ariphmeticAvg * 0.15);
				$limType2minus = $ariphmeticAvg - (3.5 * $ariphmeticAvg * 0.15);
			} 
			else 
			{
				$limType1plus = $ariphmeticAvg - (2.5 * $ariphmeticAvg * 0.15);
				$limType1minus = $ariphmeticAvg + (2.5 * $ariphmeticAvg * 0.15);
				$limType2plus = $ariphmeticAvg - (3.5 * $ariphmeticAvg * 0.15);
				$limType2minus = $ariphmeticAvg + (3.5 * $ariphmeticAvg * 0.15);	
			}	
			
			$limitsArr = array(array($limType1plus, $limType1minus), array($limType2plus, $limType2minus));
			$discrepLimits = $limitsArr;
		}
		
		unset($Eng);
	}
	return $discrepLimits;
}

function GetReport($extEtalonId, $extEngineSerialArr, $extFromDate, $extToDate, $extDiscrepLimitTypeArr){
	$etalonId = $extEtalonId; 
	$engineSerialArr = $extEngineSerialArr;
	$fromDate = $extFromDate;
	$toDate = $extToDate;
	$limitTypeArr = $extDiscrepLimitTypeArr;
	
	$Eng = new Engine();
	$report = array();
	if($etalonId == DIAGNOSTIC_IGNORE_ETALON)
	{
		$etalonId = null;
	}

	foreach ($engineSerialArr as $engineSerial)
	{
		if($etalonId == null)
		{
			$engineInfo = $Eng->GetEngineInfo($engineSerial);
		}
		else 
		{
			$engineInfo = $Eng->GetEngineInfoBySerialAndEtalon($etalonId, $engineSerial);
		}
		$slicesArr = explode(", ", $engineInfo["sliceCode"]);

		foreach ($slicesArr as $sliceCode)
		{
			$discrepCodesArr = $Eng->GetEngineDiscrepsBySlices($engineSerial, $sliceCode, $etalonId);
			foreach($discrepCodesArr as $dicrepCode)
			{
				if($etalonId == null)
				{
					$limitsArr = GetDiscrepLimits(DIAGNOSTIC_IGNORE_ETALON, $engineSerial, $sliceCode, $dicrepCode);
				}
				else 
				{
					$limitsArr = GetDiscrepLimits($etalonId, $engineSerial, $sliceCode, $dicrepCode);
				}
				
				$limitsPrepArr = array();
				//if we in boundaries should select less high and more low
				//but outside shoud select less low or more high
				//$limitsPrepArr[]["operator"] = "AND";
				foreach($limitTypeArr as $limNum)
				{
					if(isset($limitsArr[$limNum]))
					{
						if($limNum == 0)
						{

							$limitsPrepArr[] = array(
									"high" => $limitsArr[$limNum][0],
									"low" => $limitsArr[$limNum][1],
									"name" => $limNum,
									"operator" => "AND");
						}
						else
						{						
							$limitsPrepArr[] = array(
									"high" => $limitsArr[$limNum][0],
									"low" => $limitsArr[$limNum - 1][0],
									"name" => $limNum,
									"operator" => "AND");
							
							$limitsPrepArr[] = array(
									"high" => $limitsArr[$limNum - 1][1],
									"low" => $limitsArr[$limNum][1],
									"name" => $limNum,
									"operator" => "AND");
						}
					}
					else
					{
						//out of boundaries
						if(isset($limitsArr[$limNum - 1]))
						{
							$limitsPrepArr[] = array(
									"low" => $limitsArr[$limNum - 1][1], 
									"high" => $limitsArr[$limNum - 1][0],
									"name" => $limNum,
									"operator" => "OR");
						}
					}
				}
				
				for($i = 0; $i < count($limitsPrepArr); $i++)
				{
					$reportRow = $Eng->GetEngineDiscrepValuesByLimitsAndDate($engineSerial, $dicrepCode,
							$limitsPrepArr[$i]["low"], $limitsPrepArr[$i]["high"], $limitsPrepArr[$i]["name"], $limitsPrepArr[$i]["operator"],
							$fromDate, $toDate, $etalonId);
					
					if(!empty($reportRow))
					{
						$report = array_merge($report, $reportRow);
					}
				}
			}
		}
	}

	
	unset($Eng);
	//$report = array_unique($report);
	return $report;
}

?>
