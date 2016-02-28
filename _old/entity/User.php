<?php

require_once("includes.php"); 

class User
{
	public $allPrivilegeArr = array(
			PRIVILEGE_VIEW_FLIGHTS,
			PRIVILEGE_SHARE_FLIGHTS,
			PRIVILEGE_ADD_FLIGHTS,
			PRIVILEGE_EDIT_FLIGHTS,
			PRIVILEGE_DEL_FLIGHTS,
			PRIVILEGE_FOLLOW_FLIGHTS,
			PRIVILEGE_TUNE_FLIGHTS,
				
			PRIVILEGE_VIEW_SLICES,
			PRIVILEGE_SHARE_SLICES,
			PRIVILEGE_ADD_SLICES,
			PRIVILEGE_EDIT_SLICES,
			PRIVILEGE_DEL_SLICES,
				
			PRIVILEGE_VIEW_ENGINES,
			PRIVILEGE_SHARE_ENGINES,
			PRIVILEGE_EDIT_ENGINES,
			PRIVILEGE_DEL_ENGINES,
				
			PRIVILEGE_VIEW_BRUTYPES,
			PRIVILEGE_SHARE_BRUTYPES,
			PRIVILEGE_ADD_BRUTYPES,
			PRIVILEGE_EDIT_BRUTYPES,
			PRIVILEGE_DEL_BRUTYPES,
				
			PRIVILEGE_OPTIONS_USERS,
			PRIVILEGE_VIEW_USERS,
			PRIVILEGE_SHARE_USERS,
			PRIVILEGE_ADD_USERS,
			PRIVILEGE_DEL_USERS,
			PRIVILEGE_EDIT_USERS
	
			/*PRIVILEGE_VIEW_DOCS,
			PRIVILEGE_SHARE_DOCS,
			PRIVILEGE_ADD_DOCS,
			PRIVILEGE_EDIT_DOCS,
			PRIVILEGE_DEL_DOCS,*/);
	
	public $flightPrivilegeArr = array(
			PRIVILEGE_VIEW_FLIGHTS,
			PRIVILEGE_SHARE_FLIGHTS,
			PRIVILEGE_ADD_FLIGHTS,
			PRIVILEGE_EDIT_FLIGHTS,
			PRIVILEGE_DEL_FLIGHTS,
			PRIVILEGE_FOLLOW_FLIGHTS,
			PRIVILEGE_TUNE_FLIGHTS);
	
	public $slicePrivilegeArr = array(
			PRIVILEGE_VIEW_SLICES,
			PRIVILEGE_SHARE_SLICES,
			PRIVILEGE_ADD_SLICES,
			PRIVILEGE_EDIT_SLICES,
			PRIVILEGE_DEL_SLICES);
	
	public $enginePrivilegeArr = array(
			PRIVILEGE_VIEW_ENGINES,
			PRIVILEGE_SHARE_ENGINES,
			PRIVILEGE_EDIT_ENGINES,
			PRIVILEGE_DEL_ENGINES);
	
	public $bruTypesPrivilegeArr = array(
			PRIVILEGE_VIEW_BRUTYPES,
			PRIVILEGE_SHARE_BRUTYPES,
			PRIVILEGE_ADD_BRUTYPES,
			PRIVILEGE_EDIT_BRUTYPES,
			PRIVILEGE_DEL_BRUTYPES);
	
	/*public $docsPrivilegeArr = array(
			PRIVILEGE_VIEW_DOCS,
			PRIVILEGE_ADD_DOCS,
			PRIVILEGE_EDIT_DOCS,
			PRIVILEGE_DEL_DOCS);*/
	
	public $userPrivilegeArr = array(
			PRIVILEGE_OPTIONS_USERS,
			PRIVILEGE_VIEW_USERS,
			PRIVILEGE_SHARE_USERS,
			PRIVILEGE_ADD_USERS,
			PRIVILEGE_DEL_USERS,
			PRIVILEGE_EDIT_USERS);
	
	public function CheckPrivilege($extInputPrivilege)
	{
		$inputPrivilege = $extInputPrivilege;
	
		$privilegeString = "";
		if(count($inputPrivilege) == count($this->allPrivilegeArr))
		{
			$privilegeString = "AllGrant";
		}
		else
		{
			if(count(array_intersect($inputPrivilege, $this->flightPrivilegeArr)) == count($this->flightPrivilegeArr))
			{
				$privilegeString .= "FlightGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->flightPrivilegeArr);
			}
				
			if(count(array_intersect($inputPrivilege, $this->slicePrivilegeArr)) == count($this->slicePrivilegeArr))
			{
				$privilegeString .= "; SliceGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->slicePrivilegeArr);
			}
				
			if(count(array_intersect($inputPrivilege, $this->enginePrivilegeArr)) == count($this->enginePrivilegeArr))
			{
				$privilegeString .= "; EngineGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->enginePrivilegeArr);
			}
				
			if(count(array_intersect($inputPrivilege, $this->bruTypesPrivilegeArr)) == count($this->bruTypesPrivilegeArr))
			{
				$privilegeString .= "; BruTypesGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->bruTypesPrivilegeArr);
			}
				
			/*if(count(array_intersect($inputPrivilege, $this->docsPrivilegeArr)) == count($this->docsPrivilegeArr))
			{
				$privilegeString .= "; DocsGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->docsPrivilegeArr);
			}*/
				
			if(count(array_intersect($inputPrivilege, $this->userPrivilegeArr)) == count($this->userPrivilegeArr))
			{
				$privilegeString .= "; UsersGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->userPrivilegeArr);
			}
				
			if(!empty($inputPrivilege))
			{
				if(strlen($privilegeString) > 0)
				{
					$privilegeString .= "; ";
				}
	
				$privilegeString .= implode(", ", $inputPrivilege);
			}
		}
	
		return $privilegeString;
	}
	
	public function CreateUsersTables()
	{			
		$query = "SHOW TABLES LIKE 'user_personal';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_personal` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`login` VARCHAR(200),
				`privilege` TINYTEXT,
				`options` TEXT,
				`subscribers` TINYTEXT,
				`author` VARCHAR(200) DEFAULT ' ',
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute()) 
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_brutypes';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_brutypes` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`bruTypeId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_docs';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_docs` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`docId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_engine';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_engine` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`engineId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_flights';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_flights` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`flightId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_slices';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_slices` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`sliceId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_users';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_users` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userMasterId` INT(11),
				`userSlaveId` INT(11),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
				

		$query = "SHOW TABLES LIKE 'ul_blocked_ips';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `ul_blocked_ips` (
				`ip` varchar(39) CHARACTER SET ascii NOT NULL,
				`block_expires` varchar(26) CHARACTER SET ascii NOT NULL,
				PRIMARY KEY (`ip`)
				);";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'ul_log';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `ul_log` (
				`timestamp` varchar(26) CHARACTER SET ascii NOT NULL,
				`action` varchar(20) CHARACTER SET ascii NOT NULL,
				`comment` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
				`user` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
				`ip` varchar(39) CHARACTER SET ascii NOT NULL
				);";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'ul_logins';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `ul_logins` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`username` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
				`password` varchar(2048) CHARACTER SET ascii NOT NULL,
				`date_created` varchar(26) CHARACTER SET ascii NOT NULL,
				`last_login` varchar(26) CHARACTER SET ascii NOT NULL,
				`block_expires` varchar(26) CHARACTER SET ascii NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `username` (`username`(255))
				) AUTO_INCREMENT=1 ;";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'ul_nonces';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `ul_nonces` (
				`code` varchar(100) CHARACTER SET ascii NOT NULL,
				`action` varchar(850) CHARACTER SET ascii NOT NULL,
				`nonce_expires` varchar(26) CHARACTER SET ascii NOT NULL,
				PRIMARY KEY (`code`),
				UNIQUE KEY `action` (`action`(255))
				);";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'ul_sessions';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `ul_sessions` (
				`id` varchar(128) CHARACTER SET ascii NOT NULL DEFAULT '',
				`data` blob NOT NULL,
				`session_expires` varchar(26) CHARACTER SET ascii NOT NULL,
				`lock_expires` varchar(26) CHARACTER SET ascii NOT NULL,
				PRIMARY KEY (`id`)
				);";
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

	public function GetUserIdByName($extRequester)
	{
		$requester = $extRequester;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `id` FROM `user_personal` WHERE `login`='".$requester."' LIMIT 1;");
	
		$row = $result->fetch_array();
		$userId = $row['id'];
	
		$c->Disconnect();
		unset($c);
	
		return $userId;
	}
	
	public function GetUserNameById($extRequester)
	{
		$requester = $extRequester;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `login` FROM `user_personal` WHERE `id`='".$requester."' LIMIT 1;");
	
		$row = $result->fetch_array();
		$userLogin = $row['login'];
	
		$c->Disconnect();
		unset($c);
	
		return $userLogin;
	}
	
	public function GetUsersList($extAvaliableUsersIds)
	{
		$avaliableUsersIds = $extAvaliableUsersIds;
		
		$userInfoArr = array();
		if(count($avaliableUsersIds) > 0)
		{
			$inString = "";
			foreach($avaliableUsersIds as $id)
			{
				$inString .= "'" . $id ."',";
			}
				
			$inString = substr($inString, 0, -1);
			
			$c = new DataBaseConnector();
			$link = $c->Connect();
		
			$result = $link->query("SELECT * FROM `user_personal` WHERE 
					`id` IN (".$inString.") ORDER BY `id`;");

			while($row = $result->fetch_array())
			{	
				$userInfo = array("id"=>$row['id'],
					"login"=>$row['login'],
					"company"=>$row['company'],
					"privilege"=>$row['privilege'],
					"options"=>$row['options'],
					"subscribers"=>$row['subscribers'],
					"author"=>$row['author']);
				
				$userInfoArr[] = $userInfo;
			}
		
			$c->Disconnect();
			unset($c);
		}
	
		return $userInfoArr;
	}
	
	public function GetUsersInfo($extUsername)
	{
		$username = $extUsername;
			
		$c = new DataBaseConnector();
		$link = $c->Connect();

		$result = $link->query("SELECT * FROM `user_personal` WHERE
				`login` = '".$username."';");

		if($row = $result->fetch_array())
		{
			$userInfo = array("id"=>$row['id'],
					"login"=>$row['login'],
					"company"=>$row['company'],
					"privilege"=>$row['privilege'],
					"options"=>$row['options'],
					"subscribers"=>$row['subscribers'],
					"author"=>$row['author']);
		}

		$c->Disconnect();
		unset($c);
	
		return $userInfo;
	}
	
	public function GetUserPrivilege($extUsername)
	{
		$username = $extUsername;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `privilege` FROM `user_personal` WHERE `login`='".$username."' LIMIT 1;");
	
		$userInfo = array();
		$row = $result->fetch_array();
		$privilege = $row['privilege'];
		$privilege = explode(',', $privilege);
	
		$c->Disconnect();
		unset($c);
	
		return $privilege;
	}
	
	public function CreateUserPersonal($extLogin, $extPrivilege, $extAuthor, $extCompany)
	{
		$login = $extLogin;
		$privilege = $extPrivilege;
		$author = $extAuthor;
		$company = $extCompany;
	
		$query = "INSERT INTO `user_personal` (`login`,
				`privilege`,
				`author`,
				`company`)
				VALUES ('".$login."',
						'".$privilege."',
						'".$author."',
						'".$company."');";
	
		$execInfo['query'] = $query;
		$execInfo['status'] = 0;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$execInfo['link'] = $link;
		if($stmt = $link->prepare($query))
		{
			$execInfo['status'] = 1;
		}
		else
		{
			$execInfo['status'] = -1;
		}
	
		$stmt->execute();
		$stmt->close();
	
		return $execInfo;
	}
	
	public function DeleteUserPersonal($extLogin)
	{
		$login = $extLogin;

		$query = "DELETE FROM `user_personal` WHERE `login` = '".$login."';";
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$msg = $stmt;
		
		$stmt->close();
		
		$this->DeleteUserAsSubscriber($login);
		$userId = $this->GetUserIdByName($login);
		$this->DeleteUserAvaliableData($userId);
	
		return $msg;
	}
	
	public function CheckUserPersonalExist($extLogin)
	{
		$login = $extLogin;

		$query = "SELECT `login` FROM `user_personal` WHERE `login` = '".$login."';";
				
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$exist = false;
		$result = $link->query($query);
		if($row = $result->fetch_array())
		{
			$exist = true;
		}
	
		$c->Disconnect();
		unset($c);
	
		return $exist;
	}
	
	public function SelectSubscribers($extAuthor)
	{
		$author = $extAuthor;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$result = $link->query("SELECT `subscribers` FROM `user_personal` WHERE `login`='".$author."';");
		
		$subscribersArr = array();
		$subscribersStr = "";
		if($row = $result->fetch_array())
		{
			$subscribersStr = $row['subscribers'];
		}
		
		if($subscribersStr != '')
		{
			if(strpos(",", $subscribersStr) != 0)
			{
				$subscribersArr = (array)explode(",", $subscribersStr);
				$subscribersArr = array_unique($subscribersArr);
			}
			else
			{
				$subscribersArr[] = $subscribersStr;
			}
		}

		$c->Disconnect();
		unset($c);
		
		return $subscribersArr;
	}

	public function AppendSubscriber($extAuthor, $extSubscriber)
	{
		$author = $extAuthor;
		$subscriber = $extSubscriber;
		
		$existSubscribers = $this->SelectSubscribers($author);
		
		if(!in_array($subscriber, $existSubscribers))
		{
			if(count($existSubscribers) == 0)
			{
				$newSubscribers = $subscriber;
			}
			else
			{
				$newSubscribers = $existSubscribers;
				$newSubscribers[] = $subscriber;
				$newSubscribers = implode(",", $newSubscribers);
			}
			
			$c = new DataBaseConnector();
			$link = $c->Connect();
		
			$query = "UPDATE `user_personal` SET `subscribers` = '".$newSubscribers."' 
					WHERE `login` = '".$author."';";
		
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
			
			$c->Disconnect();
			unset($c);
		}
		
		$newSubscribers = $this->SelectSubscribers($author);
		
		return $newSubscribers;
	}
	
	public function DeleteUserAsSubscriber($extLogin)
	{
		$login = $extLogin;

		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$result = $link->query("SELECT `id`, `subscribers` FROM `user_personal` 
				WHERE `subscribers` LIKE '%".$login."%';");
		
		$userWhereCurSubscriberPresent = array();
		
		while($row = $result->fetch_array())
		{
			$userWhereCurSubscriberPresent[] = array(
					'id' => $row['id'],
					'subscribers' => $row['subscribers'],
			);
		}
		
		foreach($userWhereCurSubscriberPresent as $val)
		{
			$newSubscribers = str_replace($login, "", $val["subscribers"]);
			$newSubscribers = str_replace(",,", ",", $newSubscribers);
			
			$query = "UPDATE `user_personal` SET `subscribers` = '".$newSubscribers."'
						WHERE `id` = '".$val["id"]."';";
			
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}

		$c->Disconnect();
		unset($c);
	}
	
	public function GetIdByUsername($extUsername)
	{
		$username = $extUsername;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$result = $link->query("SELECT `id` FROM `user_personal` WHERE `login`='".$username."' LIMIT 1;");
		
		$row = $result->fetch_array();
		$userId = $row['id'];

		$c->Disconnect();
		unset($c);
	
		return $userId;
	}
	
	public function GetAvaliableFlights($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeFlights = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$result = $link->query("SELECT `flightId` FROM `user_flights` WHERE `userId`='".$userId."';");
		
		while($row = $result->fetch_array())
		{
			$avaliabeFlights[] = $row['flightId'];
		}

		$c->Disconnect();
		unset($c);
	
		return $avaliabeFlights;
	}
	
	public function SetFlightAvaliable($extUsername, $extFlightId)
	{
		$username = $extUsername;
		$flightId = $extFlightId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_flights` (`userId`, `flightId`) 
				VALUES ('".$userId."', '".$flightId."');";
		
		//error_log($query);
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$c->Disconnect();
		unset($c);
		
		$this->SetFlightAvaliableForSubscribers($username, $flightId);
	}
	
	public function SetFlightAvaliableForSubscribers($extUsername, $extFlightId)
	{
		$username = $extUsername;
		$flightId = $extFlightId;
		
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		for($i = 0; $i < count($subscribersArr); $i++)
		{
			$subscriber = $subscribersArr[$i];
			$userId = $this->GetIdByUsername($subscriber);
			$query = "INSERT INTO `user_flights` (`userId`, `flightId`)
					VALUES ('".$userId."', '".$flightId."');";
			
			//error_log($query);
		
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetFlightAvaliableForUser($extUsername, $extFlightId)
	{
		$username = $extUsername;
		$flightId = $extFlightId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		
		$query = "DELETE FROM `user_flights` WHERE `userId` = '".$userId."'	AND
				`flightId` = '".$flightId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetFlightAvaliable($extFlightId)
	{
		$flightId = $extFlightId;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_flights` WHERE `flightId` = '".$flightId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetAvaliableSlices($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeSlices = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `sliceId` FROM `user_slices` WHERE `userId`='".$userId."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeSlices[] = $row['sliceId'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $avaliabeSlices;
	}
	
	public function SetSliceAvaliable($extUsername, $extSliceId)
	{
		$username = $extUsername;
		$sliceId = $extSliceId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_slices` (`userId`, `sliceId`)
				VALUES ('".$userId."', '".$sliceId."');";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		$this->SetSliceAvaliableForSubscribers($username, $sliceId);
	}
	
	public function SetSliceAvaliableForSubscribers($extUsername, $extSliceId)
	{
		$username = $extUsername;
		$sliceId = $extSliceId;
	
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach ($subscribersArr as $subscriber)
		{
			$userId = $this->GetIdByUsername($username);
			$query = "INSERT INTO `user_slices` (`userId`, `sliceId`)
					VALUES ('".$userId."', '".$sliceId."');";
	
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetSliceAvaliableForUser($extUsername, $extSliceId)
	{
		$username = $extUsername;
		$sliceId = $extSliceId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_slices` WHERE `userId` = '".$userId."'	AND
				`sliceId` = '".$sliceId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetSliceAvaliable($extSliceId)
	{
		$sliceId = $extSliceId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_slices` WHERE `sliceId` = '".$sliceId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetAvaliableEngines($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeEngines = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `engineDiscrepId` FROM `user_engine` WHERE `userId`='".$userId."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeEngines[] = $row['engineDiscrepId'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $avaliabeEngines;
	}
	
	public function SetEnginesAvaliable($extUsername, $extEngineId)
	{
		$username = $extUsername;
		$engineId = $extEngineId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_engine` (`userId`, `engineDiscrepId`)
				VALUES ('".$userId."', '".$engineId."');";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		$this->SetEnginesAvaliableForSubscribers($username, $engineId);
	}
	
	public function SetEnginesAvaliableForSubscribers($extUsername, $extEngineId)
	{
		$username = $extUsername;
		$engineId = $extEngineId;
	
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach ($subscribersArr as $subscriber)
		{
			$userId = $this->GetIdByUsername($username);
			$query = "INSERT INTO `user_engine` (`userId`, `engineDiscrepId`)
					VALUES ('".$userId."', '".$engineId."');";
	
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetEngineAvaliableForUser($extUsername, $extEngineId)
	{
		$username = $extUsername;
		$engineId = $extEngineId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_engine` WHERE `userId` = '".$userId."'	AND
				`engineDiscrepId` = '".$engineId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetEngineAvaliable($extEngineId)
	{
		$engineId = $extEngineId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_engine` WHERE `engineDiscrepId` = '".$engineId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetAvaliableBruTypes($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeBruTypes = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `bruTypeId` FROM `user_brutypes` WHERE `userId`='".$userId."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeBruTypes[] = $row['bruTypeId'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $avaliabeBruTypes;
	}
	
	public function SetBruTypeAvaliable($extUsername, $extBruTypeId)
	{
		$username = $extUsername;
		$bruTypeId = $extBruTypeId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_brutypes` (`userId`, `bruTypeId`)
				VALUES ('".$userId."', '".$bruTypeId."');";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		$this->SetBruTypeAvaliableForSubscribers($username, $bruTypeId);
	}
	
	public function SetBruTypeAvaliableForSubscribers($extUsername, $extBruTypeId)
	{
		$username = $extUsername;
		$bruTypeId = $extBruTypeId;
	
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach ($subscribersArr as $subscriber)
		{
			$userId = $this->GetIdByUsername($username);
			$query = "INSERT INTO `user_brutypes` (`userId`, `bruTypeId`)
					VALUES ('".$userId."', '".$bruTypeId."');";
	
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetBruTypesAvaliableForUser($extUsername, $extBruTypeId)
	{
		$username = $extUsername;
		$bruTypeId = $extBruTypeId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_brutypes` WHERE `userId` = '".$userId."'	AND
				`bruTypeId` = '".$bruTypeId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetBruTypesAvaliable($extBruTypeId)
	{
		$bruTypeId = $extBruTypeId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "DELETE FROM `user_brutypes` WHERE `bruTypeId` = '".$bruTypeId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetAvaliableDocs($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeDocs = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `docId` FROM `user_docs` WHERE `userId`='".$userId."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeDocs[] = $row['docId'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $avaliabeDocs;
	}
	
	public function SetDocsAvaliable($extUsername, $extDocId)
	{
		$username = $extUsername;
		$docId = $extDocId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_docs` (`userId`, `docId`)
				VALUES ('".$userId."', '".$docId."');";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		$this->SetDocsAvaliableForSubscribers($username, $docId);
	}
	
	public function SetDocsAvaliableForSubscribers($extUsername, $extDocId)
	{
		$username = $extUsername;
		$docId = $extDocId;
	
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach ($subscribersArr as $subscriber)
		{
			$userId = $this->GetIdByUsername($username);
			$query = "INSERT INTO `user_docs` (`userId`, `docId`)
					VALUES ('".$userId."', '".$docId."');";
	
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetDocsAvaliableForUser($extUsername, $extDocId)
	{
		$username = $extUsername;
		$docId = $extDocId;
	
		$userId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_docs` WHERE `userId` = '".$userId."'	AND
				`docId` = '".$docId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetDocsAvaliable($extDocId)
	{
		$docId = $extDocId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
	
		$query = "DELETE FROM `user_docs` WHERE `docId` = '".$docId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetAvaliableUsers($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeUsers = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `userSlaveId` FROM `user_users` WHERE `userMasterId`='".$userId."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeUsers[] = $row['userSlaveId'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $avaliabeUsers;
	}
	
	public function SetUsersAvaliable($extUsername, $extUserId)
	{
		$username = $extUsername;
		$userSlaveId = $extUserId;
	
		$userMasterId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "INSERT INTO `user_users` (`userMasterId`, `userSlaveId`)
				VALUES ('".$userMasterId."', '".$userSlaveId."');";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		$this->SetUsersAvaliableForSubscribers($username, $userSlaveId);
	}
	
	public function SetUsersAvaliableForSubscribers($extUsername, $extUserId)
	{
		$username = $extUsername;
		$userSlaveId = $extUserId;
	
		$subscribersArr = $this->SelectSubscribers($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		foreach ($subscribersArr as $subscriber)
		{
			$userMasterId = $this->GetIdByUsername($username);
			$query = "INSERT INTO `user_users` (`userMasterId`, `userSlaveId`)
					VALUES ('".$userMasterId."', '".$userSlaveId."');";
	
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetUsersAvaliableForUser($extUsername, $extUserId)
	{
		$username = $extUsername;
		$userSlaveId = $extUserId;
	
		$userMasterId = $this->GetIdByUsername($username);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "DELETE FROM `user_users` WHERE `userMasterId` = '".$userMasterId."'	AND
				`userSlaveId` = '".$userSlaveId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function UnsetUsersAvaliable($extUserId)
	{
		$userSlaveId = $extUserId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "DELETE FROM `user_users` WHERE `userSlaveId` = '".$userSlaveId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function DeleteUserAvaliableData($extUserId)
	{
		$userId = $extUserId;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "DELETE FROM `user_flights` WHERE `userId` = '".$userId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "DELETE FROM `user_slices` WHERE `userId` = '".$userId."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "DELETE FROM `user_engine` WHERE `userId` = '".$userId."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "DELETE FROM `user_brutypes` WHERE `userId` = '".$userId."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "DELETE FROM `user_docs` WHERE `userId` = '".$userId."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "DELETE FROM `user_users` WHERE `userMasterId` = '".$userId."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function GetUsersByAuthor($extAuthor)
	{
		$author = $extAuthor;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
			
		$query = "SELECT `id` FROM `user_options` WHERE `author` = '".$author."';";
		$mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);
	
		$list = array();
		while($row = $mySqliResult->fetch_array())
		{
			$item = $this->GetUserPrivilege($this->GetUserNameById($row['id']));
			array_push($list, $item);
		}
		$mySqliResult->free();
		$c->Disconnect();
			
		unset($c);
	
		return $list;
	}
	
	public function UpdateUsersBecauseAuthorDeleting($extAuthor)
	{
		$author = $extAuthor;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "UPDATE `user_personal` SET `author` = 'admin' WHERE `author` = '".$author."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
}



?>