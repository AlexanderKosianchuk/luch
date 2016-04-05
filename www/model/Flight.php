<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

class Flight
{	
	public function CreateFlightTable()
	{
		$query = "SHOW TABLES LIKE 'flights';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{	
			$query = "CREATE TABLE `flights` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`bort` VARCHAR(255),
				`voyage` VARCHAR(255),
				`startCopyTime` BIGINT(20),
				`uploadingCopyTime` BIGINT(20),
				`performer` VARCHAR(255),
				`bruType` VARCHAR(255),
				`departureAirport` VARCHAR(255),
				`arrivalAirport` VARCHAR(255),
				`flightAditionalInfo` TEXT,
				`fileName` VARCHAR(255),
				`apTableName` VARCHAR(20),
				`bpTableName` VARCHAR(20),
				`exTableName` VARCHAR(20),
				PRIMARY KEY (`id`)) " .
				"DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$stmt = $link->prepare($query);
			if (!$stmt->execute()) {
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		$c->Disconnect();
		unset($c);
	}
	
	public function GetFlightInfo($extFlightId)
	{
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$flightId = $extFlightId;
	
		$query = "SELECT * FROM `flights` WHERE id = '".$flightId."' LIMIT 1;";

		$result = $link->query($query);
		$flightInfo = array();
		
		if($row = $result->fetch_array())
		{
			foreach ($row as $key => $value)
			{
				if($key == 'flightAditionalInfo')
				{
					$flightInfo[$key] = $value;
					if(($value != '') && ($value != ' '))
					{
						if(strrpos($value, ";") !== false)
						{
							$rest = substr($value, -1);
							if($rest == ";")
							{
								$value = substr($value, 0, count($value) - 2);
							}
							
							$aditionalInfoArr = explode(";", $value);
	
							for($i = 0; $i < count($aditionalInfoArr); $i++)
							{
								$aditionalInfoVal = explode(":", $aditionalInfoArr[$i]);
								$flightInfo[$aditionalInfoVal[0]] = $aditionalInfoVal[1];
							}
						}
					}
				}
				else 
				{
					$flightInfo[$key] = $value;
				}
			}
		}
	
		$result->free();
		$c->Disconnect();
	
		unset($c);
	
		return $flightInfo;
	}
	
	public function GetFlights($extAvaliableFlightIds, $extOrderName = 'id', $extOrderType = 'ASC')
	{
		$avaliableFlightIds = $extAvaliableFlightIds;
		$orderName = $extOrderName;
		$orderType = $extOrderType;
		
		$listFlights = array();
		if(count($avaliableFlightIds) > 0)
		{
			$inString = "";
			foreach($avaliableFlightIds as $id)
			{
				$inString .= "'" . $id ."',";
			}
			
			$inString = substr($inString, 0, -1);
			
			$c = new DataBaseConnector();
			$link = $c->Connect();
			
			$query = "SELECT * FROM `flights` WHERE `id` IN (".$inString.") ORDER BY `".$orderName."` ".$orderType.";";
			$mySqliSelectFlightsResult = $link->query($query);//, MYSQLI_USE_RESULT);

			while($row = $mySqliSelectFlightsResult->fetch_array())
			{
				$flight = $this->GetFlightInfo($row['id']);	
				array_push($listFlights, $flight);
			}
			$mySqliSelectFlightsResult->free();
			$c->Disconnect();
			
			unset($c);
		}
		
		return $listFlights;		
	}
	
	public function GetFlightsByAuthor($extAuthor)
	{
		$author = $extAuthor;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
			
		$query = "SELECT `id` FROM `flights` WHERE `author` = '".$author."';";
		$mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

		$list = array();
		while($row = $mySqliResult->fetch_array())
		{
			$item = $this->GetFlightInfo($row['id']);
			array_push($list, $item);
		}
		$mySqliResult->free();
		$c->Disconnect();
			
		unset($c);
	
		return $list;
	}
	
	public function GetFlightsByFolder($extFolder, $extAvaliableFlightIds)
	{
		$folder = $extFolder;
		$avaliableFlightIds = $extAvaliableFlightIds;
	
		$listFlights = array();
		if(count($avaliableFlightIds) > 0)
		{
			$inString = "";
			foreach($avaliableFlightIds as $id)
			{
				$inString .= "'" . $id ."',";
			}
			
			$inString = substr($inString, 0, -1);

			$query = "SELECT `id` FROM `flights` WHERE `id` IN (".$inString.") " .
					"AND `folder` = " .$folder. " " .
					"ORDER BY `id`;";
						
			$c = new DataBaseConnector();
			$link = $c->Connect();

			$mySqliSelectFlightsResult = $link->query($query);//, MYSQLI_USE_RESULT);

			while($row = $mySqliSelectFlightsResult->fetch_array())
			{
				$flight = $this->GetFlightInfo($row['id']);	
				array_push($listFlights, $flight);
			}
			$mySqliSelectFlightsResult->free();
			$c->Disconnect();
			
			unset($c);
		}
		
		return $listFlights;	
	}
		
	public function PrepareFlightsList($extAvaliableFlightIds)
	{
		$avaliableFlightIds = $extAvaliableFlightIds;
		
		$listFlights = (array)$this->GetFlights($avaliableFlightIds);
		$i = 0;
		$flightsListInfo = array();
	
		while($i < count($listFlights))
		{
			$flight = (array)$listFlights[$i];
			$flightInfo = $flight;
				
			$flightInfo['exceptionsSearchPerformed'] = false;
			if($flight['exTableName'] != "")
			{
				$flightInfo['exceptionsSearchPerformed'] = true;
			}
			
			$flightInfo['cellNum'] = $flight['id'];
			$flightInfo['uploadDate'] = date('H:i:s Y-m-d', $flight['uploadingCopyTime']);
			$flightInfo['flightDate'] = date('H:i:s Y-m-d', $flight['startCopyTime']);

			$i++;
			array_push($flightsListInfo, $flightInfo);
		}
		return $flightsListInfo;
	}
			
	public function InsertNewFlight($extBort, $extVoyage, 
			$extStartCopyTime,
			$extBruType, $extPerformer,
			$extDepartureAirport, $extArrivalAirport,
			$extFile, $extAditionalInfo)
	{
		$bort = $extBort;
		$voyage = $extVoyage;
		$startCopyTime = $extStartCopyTime;
		$uploadingCopyTime = time();
		$bruType = $extBruType;
		$performer = $extPerformer;
		$departureAirport = $extDepartureAirport;
		$arrivalAirport = $extArrivalAirport;
		$uploadedFile = $extFile;
		$aditionalInfo = "";
		if(($extAditionalInfo !== null) && 
			($extAditionalInfo !== false))  {
			$aditionalInfo = strval($extAditionalInfo);
		}
		
		$tableName = uniqid();
		$tableNameAp = "_".$tableName."_ap";
		$tableNameBp = "_".$tableName."_bp";
		$exTableName = '';
		$paramsTables = array("tableNameAp" => $tableNameAp, "tableNameBp" => $tableNameBp);

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "INSERT INTO `flights` (`bort`, 
				`voyage`,
				`startCopyTime`, 
				`uploadingCopyTime`, 
				`performer`, 
				`brutype`, 
				`departureAirport`,
				`arrivalAirport`,
				`flightAditionalInfo`,
				`fileName`, 
				`apTableName`, 
				`bpTableName`, 
				`exTableName`) 
				VALUES ('".$bort."', 
						'".$voyage."', 
						".$startCopyTime.", 
						".$uploadingCopyTime.",
						'".$performer."', 
						'".$bruType."', 
						'".$departureAirport."', 
						'".$arrivalAirport."', 
						'".$aditionalInfo."', 
						'".$uploadedFile."', 
						'".$tableNameAp."', 
						'".$tableNameBp."', 
						'".$exTableName."');";
		
		$stmt = $link->prepare($query);
		/*$stmt->bind_param("ssiissssssssss", (string)$bort, 
				(string)$voyage,
				intval ($startCopyTime), 
				intval ($uploadingCopyTime), 
				(string)$performer, 
				(string)$bruType, 
				(string)$departureAirport,
				(string)$arrivalAirport,
				(string)$engines,
				(string)$aditionalInfo,
				(string)$uploadedFile, 
				(string)$tableNameAp, 
				(string)$tableNameBp, 
				(string)$exTableName);*/

		$stmt->execute();
		$stmt->close();

		$query = "SELECT LAST_INSERT_ID();";
		$result = $link->query($query);
		$row = $result->fetch_array();
		$flightId = $row["LAST_INSERT_ID()"];
		
		$c->Disconnect();
		unset($c);
		
		return $flightId;		
	}
	
	public function CreateFlightParamTables($extFlightId, $extCycloAp, $extCycloBp)
	{
		$flightId = $extFlightId;
		$cycloAp = $extCycloAp;
		$cycloBp = $extCycloBp;
	
		$flightInfo = $this->GetFlightInfo($flightId);
		$tableNameAp = $flightInfo["apTableName"];
		$tableNameBp = $flightInfo["bpTableName"];
		$apTables = array();
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach($cycloAp as $prefix => $prefixCyclo)
		{
			array_push($apTables, $tableNameAp."_".$prefix);
			$query = "CREATE TABLE `".$tableNameAp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT";

			for($i = 0; $i < count($prefixCyclo); $i++)
			{
				$query .= ", `".$prefixCyclo[$i]["code"]."` FLOAT(7,2)";
			}
			
			$query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
					"DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$stmt = $link->prepare($query);
			$stmt->execute();
		}
		
		foreach($cycloBp as $prefix => $prefixCyclo)
		{
			$query = "CREATE TABLE `".$tableNameBp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT, `code` varchar(255)) " .
				"DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$stmt = $link->prepare($query);
			$stmt->execute();
		}
		
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	
		return $apTables;
	}
	
	public function UpdateFlightInfo($extFlightId, $extFlightInfo)
	{
		$flightId = $extFlightId;
		$flightInfo = $extFlightInfo;
		foreach($flightInfo as $key => $value)	
		{
			$c = new DataBaseConnector();
			$link = $c->Connect();
			
			$query = "UPDATE `flights` SET `".
							$key."`='".$value."'
							WHERE id='".$flightId."';";
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
			
			$c->Disconnect();
			unset($c);
		}
	}
	
	public function GetFlightExtendedInfo($extFlightInfo)
	{
		$flightInfo = $extFlightInfo;
		if (isset($flightInfo['bort']) && isset($flightInfo['voyage']) && isset($flightInfo['startCopyTime']) &&
			isset($flightInfo['performer']) && isset($flightInfo['bruType']))
		{
			$bort = $flightInfo['bort'];
			$startCopyTime = $flightInfo['startCopyTime'];
			$performer = $flightInfo['performer'];
			$bruType = $flightInfo['bruType'];
			$voyage = $flightInfo['voyage'];
			
			$query = "SELECT `id` FROM `flights` WHERE (bort = '".$bort."') AND 
				(`startCopyTime` = '".$startCopyTime."') AND
				(`performer` = '".$performer."') AND
				(`bruType` = '".$bruType."') AND
				(`voyage` = '".$voyage."') 
				LIMIT 1;";
	
			$c = new DataBaseConnector();
			$link = $c->Connect();
			$result = $link->query($query);
			$row = $result->fetch_array();
			$flightInfo = $this->GetFlightInfo($row['id']);
			$result->free();		
			$c->Disconnect();
			unset($c);
			
			return $flightInfo;	
		} 
		else if (isset($flightInfo['tableNameAp']) && 
			isset($flightInfo['tableNameBp']))	
		{
			$tableNameAp = $flightInfo['tableNameAp'];
			$tableNameBp = $flightInfo['tableNameBp'];
				
			$query = "SELECT * FROM `flights` WHERE (`apTableName` = '".$tableNameAp."') AND
							(`bpTableName` = '".$tableNameBp."')
							LIMIT 1;";
			
			$c = new DataBaseConnector();
			$link = $c->Connect();
			$result = $link->query($query);
			$row = $result->fetch_array();
			$flightInfo = $this->GetFlightInfo($row['id']);
			$result->free();		
			$c->Disconnect();
			unset($c);
				
			return $flightInfo;
		}
	}
	
	public function DeleteFlight($extFlightId, $extPrefixApArr, $extPrefixBpArr)
	{
		$flightId = $extFlightId;
		$prefixApArr = (array)$extPrefixApArr;
		$prefixBpArr = (array)$extPrefixBpArr;
		
		$flightInfo = $this->GetFlightInfo($flightId);
		$file = $flightInfo['fileName'];
		$apTableName = $flightInfo['apTableName'];
		$bpTableName = $flightInfo['bpTableName'];
		$exTableName = $flightInfo['exTableName'];
		$result = array();
		$result['status'] = array();
		$result['query'] = array();
		
		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "DELETE FROM `flights` WHERE id=".$flightId.";";
		$result['query'][] = $query;
		$stmt = $link->prepare($query);
		$result['status'][] = $stmt->execute();
		$stmt->close();
		
		foreach($prefixApArr as $item => $prefix)
		{
			$query = "DROP TABLE `". $apTableName ."_".$prefix."`;";
			$result['query'][] = $query;
			$stmt = $link->prepare($query);
			$result['status'][] = $stmt->execute();
			$stmt->close();
		}
		
		foreach($prefixBpArr as $item => $prefix)
		{
			$query = "DROP TABLE `". $bpTableName ."_".$prefix."`;";
			$result['query'][] = $query;
			$stmt = $link->prepare($query);
			$result['status'][] = $stmt->execute();
			$stmt->close();
		}
		
		if($exTableName != "")
		{
			$query = "DROP TABLE `". $exTableName ."`;";
			$result['query'][] = $query;
			$stmt = $link->prepare($query);
			$result['status'][] = $stmt->execute();
			$stmt->close();			
		}
		
		$c->Disconnect();
		
		unset($c);
		
		if(file_exists($file))
		{
			unlink($file);	
		}
		
		if(in_array(false, $result['status']))
		{
			$result['status'] = false;
		}
		else
		{
			$result['status'] = true;
		}
		
		return $result;
	}
	
	public function DropTable($extTableName)
	{
		$tableName = $extTableName;
		
		$c = new DataBaseConnector();
		
		$query = "DROP TABLE `". $tableName ."`;";
		
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$c->Disconnect();
		
		unset($c);	
	}
	
	public function GetMaxFlightId()
	{		
		$query = "SELECT MAX(`id`) FROM `flights` WHERE 1;";

		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		$maxId = 1;
		if($row = $result->fetch_array())
		{
			$maxId = $row['MAX(`id`)'];
		}

		$result->free();
		$c->Disconnect();
		unset($c);
			
		return $maxId;
		
	}
}

?>