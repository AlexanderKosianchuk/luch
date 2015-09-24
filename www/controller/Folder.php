<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

class Folder
{
	public function CreateFolderTable()
	{			
		$query = "SHOW TABLES LIKE 'folders';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `folders` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(200),
				`path` INT(11) DEFAULT 0,
				`userId` INT(11),
				PRIMARY KEY (`id`)) " .
				"DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$stmt = $link->prepare($query);
			if (!$stmt->execute()) 
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'folders';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `flightsInFolders` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`flightId` INT(11),
				`folderId` INT(11) DEFAULT 0,
				`userId` INT(11),
				PRIMARY KEY (`id`)) " .
				"DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
				
		$c->Disconnect();
		unset($c);
	}
	
	public function CreateFolder($extName, $extPath, $extUserId)
	{
		$name = $extName;
		$path = $extPath;
		$userId = $extUserId;
		
		$id = $this->GetMaxFolderId();
		
		$res = array();
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		if($id == 0)
		{			
			$id = FOLDER_START_ID;
			$query = "INSERT INTO `folders` (`id`,`name`, `path`, `userId`) " .
					"VALUES ('".$id."', '".$name."', ".$path.", '".$userId."');";
			$stmt = $link->prepare($query);
			$res['data'] = $stmt->execute();
			$stmt->close();
		}
		else 
		{
			$query = "INSERT INTO `folders` (`name`, `path`, `userId`) " .
					"VALUES ('".$name."', ".$path.", '".$userId."');";

			$stmt = $link->prepare($query);
			$res['data'] = $stmt->execute();
			$stmt->close();
			
			$query = "SELECT LAST_INSERT_ID()";
			$result = $link->query($query);
			
			if($row = $result->fetch_array())
			{
				$id = $row['LAST_INSERT_ID()'];
			}
		}
		
		$c->Disconnect();
		unset($c);
		
		$res['folderId'] = $id;
		return $res;
	}
	
	public function ChangeFolderPath($extSenderId, $extDestinationId, $extUserId)
	{
		$folderId = $extSenderId;
		$newPath = $extDestinationId;
		$userId = $extUserId;
	
		$result = array();
		$query = "UPDATE `folders` SET `path` = '" . $newPath . "' ".
				"WHERE `id` = '" . $folderId . "' AND `userId` = " . $userId . ";";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function RenameFolder($extFolderId, $extFolderName, $extUserId)
	{
		$folderId = $extFolderId;
		$folderName = $extFolderName;
		$userId = $extUserId;
	
		$result = array();
		$query = "UPDATE `folders` SET `name` = '" . $folderName . "' ".
				"WHERE `id` = '" . $folderId . "' AND `userId` = " . $userId . ";";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function PutFlightInFolder($extFlightId, $extFolderId, $extUserId)
	{
		$flightId = $extFlightId;
		$folderId = $extFolderId;
		$userId = $extUserId;
	
		$result = array();
		$query = "INSERT INTO `flightsInFolders` (`flightId`, `folderId`, `userId`) " .
				"VALUES (".$flightId.", ".$folderId.", ".$userId.");";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function ChangeFlightFolder($extFlightId, $extFolderId, $extUserId)
	{
		$flightId = $extFlightId;
		$folderId = $extFolderId;
		$userId = $extUserId;
	
		$result = array();
		$query = "UPDATE `flightsInFolders` SET `folderId` = '" . $folderId . "' ".
				"WHERE `flightId` = '" . $flightId . "' AND `userId` = " . $userId . ";";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function DeleteFlightFromFolders($extFlightId)
	{
		$flightId = $extFlightId;
	
		$result = array();
		$query = "DELETE FROM `flightsInFolders` " .
				"WHERE `flightId` = '" . $flightId . "';";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function DeleteFlightsInFolder($extFolderId)
	{
		$folderId = $extFolderId;
	
		$result = array();
		$query = "DELETE FROM `flightsInFolders` " .
				"WHERE `folderId` = '" . $folderId . "';";
		$result['query'] = $query;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$result['status'] = $stmt->execute();
		$stmt->close();
		$c->Disconnect();
		unset($c);
	
		return $result;
	}
	
	public function GetFolderInfo($extId)
	{
		$id = $extId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$folderInfo = array();

		if($id != 0)
		{
			$query = "SELECT * FROM `folders` WHERE `id`=".$id." LIMIT 1;";
			$result = $link->query($query);
		
			if($row = $result->fetch_array())
			{
				foreach($row as $key => $val)
				{
					$folderInfo[$key] = $val;
				}
			}
		}
		else 
		{
			$folderInfo['id'] = '0';
			$folderInfo['name'] = 'root';
			$folderInfo['path'] = '';
		}
		
	
		$c->Disconnect();
		unset($c);
	
		return $folderInfo;
	}
	
	public function GetFlightFolder($extFlightId, $extUserId)
	{
		$flightId = $extFlightId;
		$userId = $extUserId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `folderId` FROM `flightsInFolders` WHERE `flightId`=".$flightId." " .
				"AND `userId` = ".$userId." LIMIT 1;";
		
		//error_log($query);
		$result = $link->query($query);		
	
		if($row = $result->fetch_array())
		{
			$folderId = $row['folderId'];
			$folderInfo = $this->GetFolderInfo($folderId);
		}
	
		$c->Disconnect();
		unset($c);
	
		return $folderInfo;
	}
	
	public function GetFlightsByFolder($extFolderId, $extUserId)
	{
		$folderId = $extFolderId;
		$userId = $extUserId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `flightId` FROM `flightsInFolders` WHERE `folderId`='".$folderId."' " .
				"AND `userId` = ".$userId.";";		

		//error_log($query);
		$result = $link->query($query);
	
		$flightArr = array();
		while($row = $result->fetch_array())
		{
			$flightArr[] = $row['flightId'];
		}
		
		/*if($folderId == 0)
		{
			$folderInfo['name'] = 'root';
		}
		else 
		{
			$folderInfo = $this->GetFolderInfo($folderId);
		}*/
	
		$c->Disconnect();
		unset($c);
	
		return $flightArr;
	}
	
	public function GetSubfoldersByFolder($extFolderId, $extUserId)
	{
		$id = $extFolderId;
		$userId = $extUserId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT * FROM `folders` WHERE ((`path` = ".$id.") " .
				"AND (`userId` = '".$userId."'));";
		
		$result = $link->query($query);
		$subfolders = array();
		while($row = $result->fetch_array())
		{
			foreach($row as $key => $val)
			{
				$folderInfo[$key] = $val;				
			}
			$subfolders[] = $folderInfo;
		}
		$c->Disconnect();
		unset($c);
		
		return $subfolders;
	}
	
	public function GetAvaliableFolders($extUserId)
	{
		$userId = $extUserId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `id` FROM `folders` WHERE `userId` = '".$userId."';";
	
		$result = $link->query($query);
		$avaliable = array();
		while($row = $result->fetch_array())
		{
			$avaliable[] = $row['id'];
		}
		$c->Disconnect();
		unset($c);
	
		return $avaliable;
	}
	
	public function GetAvaliableContent($extFolderId, $extUserId)
	{
		$userId = $extUserId;
		$folderId = $extFolderId;
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$link2 = $c->Connect();
	
		$query = "SELECT * FROM `folders` WHERE `userId` = '".$userId."';";
	
		$result = $link->query($query);
		$avaliable = array();
		while($row = $result->fetch_array())
		{
			if($folderId == $row['id'])
			{
				$avaliable[] = array(
						'id' => $row['id'],		
						'text' => $row['name'],
						'type' => 'folder',
						'parent' => $row['path'],
						"state" => array(
								"opened" => true,
								"selected" => true
						)
				);
			} else {
				$avaliable[] = array(
						'id' => $row['id'],
						'text' => $row['name'],
						'type' => 'folder',
						'parent' => $row['path']
				);
			}
		}
		
		$query = "SELECT * FROM `flightsinfolders` WHERE `userId` = '".$userId."';";
		
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$query = "SELECT `id`, `bort`, `voyage`, `startCopyTime`, `bruType`, `departureAirport`, `arrivalAirport` ".
				"FROM `flights` WHERE `id` = '".$row['flightId']."';";
			
			$result2 = $link2->query($query);
			$name = '';
			
			if($row2 = $result2->fetch_array())
			{
				$name = $row2['bort'] . ", " .  $row2['voyage']  . ", " . date('d/m/y H:i', $row2['startCopyTime'])  . 
					", " . $row2['bruType']  . ", " . $row2['departureAirport']  . "-" . $row2['departureAirport'] ;
			}
			
			$avaliable[] = array(
					'id' => $row2['id'],		
					'text' => $name,
					'type' => 'flight',
					'parent' => $row['folderId']
			);
		}
		$c->Disconnect();
		unset($c);
	
		return $avaliable;
	}
	
	public function DeleteFolder($extFolderId, $extUserId)
	{
		if(is_int($extFolderId) && is_int($extUserId))
		{
			$userId = $extUserId;
			$folderId = $extFolderId;
		
			$query = "DELETE FROM `folders` WHERE (`id` = '".$folderId."') " .
				"AND (`userId` = '".$userId."');";

			$c = new DataBaseConnector();
			$link = $c->Connect();
			$stmt = $link->prepare($query);
			$result['status'] = $stmt->execute();
			$result['query'] = $query;
			$stmt->close();
			$c->Disconnect();
			unset($c);
		
			return $result;
		}
		else
		{
			error_log("Incorrect input data. " .
				"DeleteFolder id - " . json_encode($extFolderId) . ". " .
				"UserId id - " . json_encode($extUserId) . ". " .
				"FolderController");
			return false;
		}
	}
	
	public function GetMaxFolderId()
	{
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT MAX(`id`) FROM `folders` WHERE 1;";
		
		$result = $link->query($query);
		$maxId = 0;
		if($row = $result->fetch_array())
		{
			$maxId = $row['MAX(`id`)'];
		}
		
		return $maxId;
	}

}



?>