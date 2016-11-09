<?php

require_once("includes.php"); 

class Engine
{
	public function CreateEngineDiscrepTable()
	{
		$query = "SHOW TABLES LIKE 'engineDiscrep';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `engineDiscrep` (`id` BIGINT NOT NULL AUTO_INCREMENT,
			`engineSerial` VARCHAR(255),
			`flightId` MEDIUMINT,
			`flightDate` BIGINT(20),
			`sliceCode` VARCHAR(255),
			`etalonId` VARCHAR(255),
			`discrepCode` VARCHAR(255),
			`discrepValue` DOUBLE,
			`author` VARCHAR(200) DEFAULT ' ',
			PRIMARY KEY (`id`));";
				
			$stmt = $link->prepare($query);
			if (!$stmt->execute()) {
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		unset($c);
	}
	
	public function InsertEngineDiscrep($extEngineSerial,
			$extFlightId,
			$extFlightDate,
			$extSliceCode,
			$extEtalonId,
			$extDiscrepName,
			$extDiscrepVal)
	{
		$engineSerial = $extEngineSerial;
		$flightId = $extFlightId;
		$flightDate = $extFlightDate;
		$sliceCode = $extSliceCode;
		$etalonId = $extEtalonId;
		$discrepName = $extDiscrepName;
		$discrepVal = $extDiscrepVal;
	
		$query = "INSERT INTO `engineDiscrep` (`engineSerial`,
			`flightId`,
			`flightDate`,
			`sliceCode`,
			`etalonId`,
			`discrepCode`,
			`discrepValue`) VALUES ('".$engineSerial."',
				'".$flightId."',
				'".$flightDate."',
				'".$sliceCode."',
				'".$etalonId."',
				'".$discrepName."',
				'".$discrepVal."');";
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
	
		if (!$stmt->execute()) {
			echo('Error during query execution ' . $query);
		}
		$stmt->close();
		$c->Disconnect();
		unset($c);
	}
	
	public function GetEngineDiscrep($extEngineSerial, $extSlice, $extFlightId, $extDiscrepCode)
	{
		$engineSerial = $extEngineSerial;
		$slice = $extSlice;
		$flightId = $extFlightId;
		$discrepCode = $extDiscrepCode;
	
		$discrepRow = array();
			
		$query = "SELECT * FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND
				`flightId` = '".$flightId."' AND
				`sliceCode` LIKE '".$slice."%' AND
				`discrepCode` = '".$discrepCode."'";
			
		$c = new DataBaseConnector();
		$link = $c->Connect();
			
		$result = $link->query($query);
		if($row = $result->fetch_array())
		{
			$discrepRow = array("id" => $row["id"],
					"engineSerial" => $row["engineSerial"],
					"flightId" => $row["flightId"],
					"flightDate" => $row["flightDate"],
					"sliceCode" => $row["sliceCode"],
					"etalonId" => $row["etalonId"],
					"discrepCode" => $row["discrepCode"],
					"discrepValue" => $row["discrepValue"]);
		}
		else
		{
			$discrepRow = null;
		}
	
		return $discrepRow;
	}
	
	public function SelectEnginesSerialsList()
	{
		$engineSerialArr = array();
		$query = "SELECT DISTINCT(`engineSerial`) FROM `engineDiscrep` WHERE 1;";
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
	
		while($row = $result->fetch_array())
		{
			array_push($engineSerialArr, $row['engineSerial']);
		}
		$c->Disconnect();
		unset($c);
	
		return $engineSerialArr;
	}
	
	public function SelectEnginesSerialsByEtalonsList($extAvaliableEnginesIds)
	{
		$avaliableEnginesIds = $extAvaliableEnginesIds;
		
		$engineSerialsByEtalonsArr = array();
		if(count($avaliableEnginesIds) > 0)
		{
			$inString = "";
			foreach($avaliableEnginesIds as $id)
			{
				$inString .= "'" . $id ."',";
			}
				
			$inString = substr($inString, 0, -1);
			
			$query = "SELECT DISTINCT(`etalonId`) FROM `engineDiscrep` WHERE `id` IN (".$inString.");";
		
			$c = new DataBaseConnector();
			$link = $c->Connect();
			$result = $link->query($query);
		
			while($row = $result->fetch_array())
			{
				$engineSerialsArr = array();
				$query = "SELECT DISTINCT(`engineSerial`) FROM `engineDiscrep` WHERE `etalonId` = '".$row['etalonId']."';";
				$result2 = $link->query($query);
				while($row2 = $result2->fetch_array())
				{
					$engineSerialsArr[] = $row2['engineSerial'];
				}
					
				$engineSerialsByEtalonsArr[$row['etalonId']] = $engineSerialsArr;
			}
		
			$c->Disconnect();
			unset($c);
		}
	
		return $engineSerialsByEtalonsArr;
	}
	
	public function GetEngineInfo($extEngineSerial)
	{
		$engineSerial = $extEngineSerial;
		$engineInfo = array();
		$query = "SELECT MAX(`flightDate`) FROM `engineDiscrep` WHERE `engineSerial` = ".$engineSerial.";";
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		$row = $result->fetch_array();
	
		$flightDate = $row["MAX(`flightDate`)"];
		$sliceCode = "";
		$sliceCodeArr = array();
		$discrepCode = "";
	
		$query = "SELECT DISTINCT(`sliceCode`) FROM `engineDiscrep` WHERE `engineSerial` = ".$engineSerial.";";
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			//if engine change its position for example from 1 to 2
			//it can duplicate sliceCode list values
			//bacause engine num removes
			$sliceCodeCurrent = explode("Engine", $row['sliceCode']);
			if(!in_array($sliceCodeCurrent[0], $sliceCodeArr))
			{
				$sliceCode .=  $sliceCodeCurrent[0] . ", ";
				$sliceCodeArr[] = $sliceCodeCurrent[0];
			}
		}
	
		$sliceCode = substr($sliceCode, 0, -2);
	
		$query = "SELECT DISTINCT(`discrepCode`) FROM `engineDiscrep` WHERE `engineSerial` = ".$engineSerial.";";
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$discrepCode .= $row['discrepCode'] . ", ";
		}
	
		$discrepCode = substr($discrepCode, 0, -2);
	
		$c->Disconnect();
		unset($c);
	
		$engineInfo["flightDate"] = $flightDate;
		$engineInfo["sliceCode"] = $sliceCode;
		$engineInfo["discrepCode"] = $discrepCode;
	
		return $engineInfo;
	}
	
	public function GetEngineInfoBySerialAndEtalon($extEtalonId, $extEngineSerial)
	{
		$engineSerial = $extEngineSerial;
		$etalonId = $extEtalonId;
	
		$engineInfo = array();
		$query = "SELECT MAX(`flightDate`) FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND `etalonId` = '".$etalonId."';";
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		$row = $result->fetch_array();
	
		$flightDate = $row["MAX(`flightDate`)"];
		$sliceCode = "";
		$sliceCodeArr = array();
		$discrepCode = "";
	
		$query = "SELECT DISTINCT(`sliceCode`) FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND `etalonId` = '".$etalonId."';";
	
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			//if engine change its position for example from 1 to 2
			//it can duplicate sliceCode list values
			//bacause engine num removes
			$sliceCodeCurrent = explode("Engine", $row['sliceCode']);
			if(!in_array($sliceCodeCurrent[0], $sliceCodeArr))
			{
				$sliceCode .=  $sliceCodeCurrent[0] . ", ";
				$sliceCodeArr[] = $sliceCodeCurrent[0];
			}
		}
	
		$sliceCode = substr($sliceCode, 0, -2);
	
		$query = "SELECT DISTINCT(`discrepCode`) FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND `etalonId` = '".$etalonId."';";
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$discrepCode .= $row['discrepCode'] . ", ";
		}
	
		$discrepCode = substr($discrepCode, 0, -2);
	
		$c->Disconnect();
		unset($c);
	
		$engineInfo["etalonId"] = $etalonId;
		$engineInfo["flightDate"] = $flightDate;
		$engineInfo["sliceCode"] = $sliceCode;
		$engineInfo["discrepCode"] = $discrepCode;
	
		return $engineInfo;
	}
	
	public function GetEngineDiscrepsBySlices($extEngineSerial, $extSliceCode, $extEtalonId = null)
	{
		$engineSerial = $extEngineSerial;
		$sliceCode = $extSliceCode;
		$etalonId = $extEtalonId;
	
		$engineDiscreps = array();
		if($etalonId == null)
		{
			$query = "SELECT DISTINCT(`discrepCode`) FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND
				`sliceCode` LIKE '".$sliceCode."%';";
		}
		else
		{
			$query = "SELECT DISTINCT(`discrepCode`) FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND
				`etalonId` = '".$etalonId."' AND
				`sliceCode` LIKE '".$sliceCode."%';";
		}
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$engineDiscreps[] = $row['discrepCode'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $engineDiscreps;
	}
	
	public function DeleteEngineDiscrep($extEtalonId, $extEngineSerial)
	{
		$etalonId  = $extEtalonId;
		$engineSerial = $extEngineSerial;
	
		$query = "DELETE FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND
				`engineSerial` = ".$engineSerial.";";
	
		//error_log($query);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
	
		if (!$stmt->execute()) {
			echo('Error during query execution ' . $query);
		}
		$stmt->close();
		$c->Disconnect();
		unset($c);
	}
	
	public function GetEngineDiscrepValuesByAbscissaOrdinate($extEngineSerial, $extSlice, $extAbscissa, $extOrdinate, $extEtalonId = null)
	{
		$etalonId = $extEtalonId;
		$engineSerial = $extEngineSerial;
		$slice = $extSlice;
		$abscissa = $extAbscissa;
		$ordinate = $extOrdinate;
	
		$discrepVals = array();
	
		if($abscissa == DIAGNOSTIC_ABSCISSA_FLIGHTS)
		{
			$flightCounter = 1;
				
			if($etalonId != null)
			{
				$query = "SELECT `discrepValue` FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND
					`engineSerial` = '".$engineSerial."' AND
					`sliceCode` LIKE '".$slice."%' AND
					`discrepCode` = '".$ordinate."'
					ORDER BY `flightDate`;";
			}
			else
			{
				$query = "SELECT `discrepValue` FROM `engineDiscrep` WHERE `engineSerial` = '".$engineSerial."' AND
					`sliceCode` LIKE '".$slice."%' AND
					`discrepCode` = '".$ordinate."'
					ORDER BY `flightDate`;";
	
			}
				
			$c = new DataBaseConnector();
			$link = $c->Connect();
				
			$result = $link->query($query);
			while($row = $result->fetch_array())
			{
				$discrepVals[] = array($flightCounter, (float)$row["discrepValue"]);
				$flightCounter++;
			}
		}
		else
		{
			//not enable. Will be developed in future
			/*
			if(in_array($ordinate, unserialize(NN_RESULT_KEYS)))
			{
	
			}
			else
			{
	
			}*/
		}
	
		return $discrepVals;
	}
	
	public function GetEngineDiscrepValuesByLimitsAndDate($extEngineSerial, $extDiscrepCode, 
			$extLowerVal, $extHigherVal, $extLimitType, $extLimitsLogicOperation,
			$extFromDate, $extToDate, $extEtalonId = null)
	{
		$etalonId = $extEtalonId;
		$engineSerial = $extEngineSerial;
		$discrepCode = $extDiscrepCode;
		$lowerVal = $extLowerVal;
		$higherVal = $extHigherVal;
		$limitType = $extLimitType;
		$limitsLogicOperation = $extLimitsLogicOperation;
		$fromDate = $extFromDate;
		$toDate = $extToDate;
	
		$discrepVals = array();

		if($etalonId != null)
		{
			if((!empty($fromDate)) && (!empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND " .
					"`engineSerial` = '".$engineSerial."' AND " .
					"`flightDate` > '".$fromDate."' AND " .
					"`flightDate` < '".$toDate."' AND " .
					"`discrepCode` = '".$discrepCode."' AND " .
					"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
					"`discrepValue` < '".$higherVal."') " .
					"ORDER BY `flightDate`;";
			}
			else if((!empty($fromDate)) && (empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND " .
					"`engineSerial` = '".$engineSerial."' AND " .
					"`flightDate` > '".$fromDate."' AND " .
					"`discrepCode` = '".$discrepCode."' AND " .
					"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
					"`discrepValue` < '".$higherVal."') " .
					"ORDER BY `flightDate`;";
			}
			else if((empty($fromDate)) && (!empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND " .
					"`engineSerial` = '".$engineSerial."' AND " .
					"`flightDate` < '".$toDate."' AND " .
					"`discrepCode` = '".$discrepCode."' AND " .
					"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
					"`discrepValue` < '".$higherVal."') " .
					"ORDER BY `flightDate`;";
			}
			else
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE `etalonId` = '".$etalonId."' AND " .
					"`engineSerial` = '".$engineSerial."' AND " .
					"`discrepCode` = '".$discrepCode."' AND " .
					"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
					"`discrepValue` < '".$higherVal."') " .
					"ORDER BY `flightDate`;";
			}
		}
		else
		{
			if((!empty($fromDate)) && (!empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE " .
						"`engineSerial` = '".$engineSerial."' AND " .
						"`flightDate` > '".$fromDate."' AND " .
						"`flightDate` < '".$toDate."' AND " .
						"`discrepCode` = '".$discrepCode."' AND " .
						"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
						"`discrepValue` < '".$higherVal."') " .
						"ORDER BY `flightDate`;";
			}
			else if((!empty($fromDate)) && (empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE " .
						"`engineSerial` = '".$engineSerial."' AND " .
						"`flightDate` > '".$fromDate."' AND " .
						"`discrepCode` = '".$discrepCode."' AND " .
						"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
						"`discrepValue` < '".$higherVal."') " .
						"ORDER BY `flightDate`;";
			}
			else if((empty($fromDate)) && (!empty($toDate)))
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE " .
						"`engineSerial` = '".$engineSerial."' AND " .
						"`flightDate` < '".$toDate."' AND " .
						"`discrepCode` = '".$discrepCode."' AND " .
						"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
						"`discrepValue` < '".$higherVal."') " .
						"ORDER BY `flightDate`;";
			}
			else
			{
				$query = "SELECT * FROM `engineDiscrep` WHERE " .
						"`engineSerial` = '".$engineSerial."' AND " .
						"`discrepCode` = '".$discrepCode."' AND " .
						"(`discrepValue` > '".$lowerVal."' ".$limitsLogicOperation." " .
						"`discrepValue` < '".$higherVal."') " .
						"ORDER BY `flightDate`;";
			}
		}

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$discrepDate = date("d/m/y H:i:s", $row["flightDate"]);
			$discrepVals[] = array(
					"flightDate" => $discrepDate,
					"etalonId" => $row["etalonId"],
					"engineSerial" => $row["engineSerial"],
					"sliceCode" => $row["sliceCode"],
					"discrepCode" => $row["discrepCode"],
					"discrepValue" => (float)$row["discrepValue"],
					"limitType" => $limitType,
					"limits" => round($higherVal, 5) . " : " . round($lowerVal, 5)					
			);
		}

	
		return $discrepVals;
	}
	
	public function GetEnginesByAuthor($extAuthor)
	{
		$author = $extAuthor;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
			
		$query = "SELECT `id` FROM `engineDiscrep` WHERE `author` = '".$author."';";
		$mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

		$list = array();
		while($row = $mySqliResult->fetch_array())
		{
			$item = $row['id'];
			array_push($list, $item);
		}
		$mySqliResult->free();
		$c->Disconnect();
			
		unset($c);
	
		return $list;
	}
	
	public function DeleteEnginesByAuthor($extAuthor)
	{
		$author = $extAuthor;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "DELETE FROM `engineDiscrep` WHERE `author` = '".$author."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
		
}

?>