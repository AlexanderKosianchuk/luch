<?php

require_once(@SITE_ROOT_DIR ."/includes.php"); 

//User privilege
//------------

/*
 viewFlight,shareFlight,addFlight,editFlight,delFlight,followFlight,tuneFlight,
viewSlice,shareSlice,addSlice,editSlice,delSlice,
viewEngine,shareEngine,editEngine,delEngine,
viewBruType,shareBruType,addBruType,editBruType,delBruType,
optionsUsers,viewUsers,shareUsers,addUser,delUser,editUser
viewDocs,shareDocs,addDocs,editDocs,delDocs,
*/

class User
{
	public static $AVALIABILITY_FLIGHTS = 'flight';
	public static $AVALIABILITY_FDR_TYPES = 'brutype';
	public static $AVALIABILITY_SLICES = 'slice';
	public static $AVALIABILITY_ENGINES = 'engine';
	public static $AVALIABILITY_DOCS = 'doc';
	public static $AVALIABILITY_USERS = 'user';
	
	public static $PRIVILEGE_VIEW_FLIGHTS = 'viewFlight';
	public static $PRIVILEGE_SHARE_FLIGHTS = 'shareFlight';
	public static $PRIVILEGE_ADD_FLIGHTS = 'addFlight';
	public static $PRIVILEGE_EDIT_FLIGHTS = 'editFlight';
	public static $PRIVILEGE_DEL_FLIGHTS = 'delFlight';
	public static $PRIVILEGE_FOLLOW_FLIGHTS = 'followFlight';
	public static $PRIVILEGE_TUNE_FLIGHTS = 'tuneFlight';
	
	public static $PRIVILEGE_VIEW_SLICES = 'viewSlice';
	public static $PRIVILEGE_SHARE_SLICES = 'shareSlice';
	public static $PRIVILEGE_ADD_SLICES = 'addSlice';
	public static $PRIVILEGE_EDIT_SLICES = 'editSlice';
	public static $PRIVILEGE_DEL_SLICES = 'delSlice';
	
	public static $PRIVILEGE_VIEW_ENGINES = 'viewEngine';
	public static $PRIVILEGE_SHARE_ENGINES = 'shareEngine';
	public static $PRIVILEGE_EDIT_ENGINES = 'editEngine';
	public static $PRIVILEGE_DEL_ENGINES = 'delEngine';
	
	public static $PRIVILEGE_VIEW_BRUTYPES = 'viewBruType';
	public static $PRIVILEGE_SHARE_BRUTYPES = 'shareBruType';
	public static $PRIVILEGE_ADD_BRUTYPES = 'addBruType';
	public static $PRIVILEGE_EDIT_BRUTYPES = 'editBruType';
	public static $PRIVILEGE_DEL_BRUTYPES = 'delBruType';
	
	public static $PRIVILEGE_VIEW_DOCS = 'viewDocs';
	public static $PRIVILEGE_SHARE_DOCS = 'shareDocs';
	public static $PRIVILEGE_ADD_DOCS = 'addDocs';
	public static $PRIVILEGE_EDIT_DOCS = 'editDocs';
	public static $PRIVILEGE_DEL_DOCS = 'delDocs';
	
	public static $PRIVILEGE_OPTIONS_USERS = 'optionsUsers';
	public static $PRIVILEGE_VIEW_USERS = 'viewUsers';
	public static $PRIVILEGE_SHARE_USERS = 'shareUsers';
	public static $PRIVILEGE_ADD_USERS = 'addUser';
	public static $PRIVILEGE_DEL_USERS = 'delUser';
	public static $PRIVILEGE_EDIT_USERS = 'editUser';
	
	public $allPrivilegeArray;
	public $flightPrivilegeArr;
	public $slicePrivilegeArr;
	public $enginePrivilegeArr;
	public $bruTypesPrivilegeArr;
	public $userPrivilegeArr;
	
	public function  __construct()
	{
		$this->SetAllPrivilegeArr();
		$this->SetFlightPrivilegeArr();
		$this->SetSlicePrivilegeArr();
		$this->SetEnginePrivilegeArr();
		$this->SetBruTypesPrivilegeArr();
		$this->SetDocsPrivilegeArr();
		$this->SetUserPrivilegeArr();
		
		//$this->CreateUsersTables();
	}
	
	private function SetAllPrivilegeArr()
	{
		$this->allPrivilegeArray = array(
			$this::$PRIVILEGE_VIEW_FLIGHTS,
			$this::$PRIVILEGE_SHARE_FLIGHTS,
			$this::$PRIVILEGE_ADD_FLIGHTS,
			$this::$PRIVILEGE_EDIT_FLIGHTS,
			$this::$PRIVILEGE_DEL_FLIGHTS,
			$this::$PRIVILEGE_FOLLOW_FLIGHTS,
			$this::$PRIVILEGE_TUNE_FLIGHTS,
				
			$this::$PRIVILEGE_VIEW_SLICES,
			$this::$PRIVILEGE_SHARE_SLICES,
			$this::$PRIVILEGE_ADD_SLICES,
			$this::$PRIVILEGE_EDIT_SLICES,
			$this::$PRIVILEGE_DEL_SLICES,
				
			$this::$PRIVILEGE_VIEW_ENGINES,
			$this::$PRIVILEGE_SHARE_ENGINES,
			$this::$PRIVILEGE_EDIT_ENGINES,
			$this::$PRIVILEGE_DEL_ENGINES,
				
			$this::$PRIVILEGE_VIEW_BRUTYPES,
			$this::$PRIVILEGE_SHARE_BRUTYPES,
			$this::$PRIVILEGE_ADD_BRUTYPES,
			$this::$PRIVILEGE_EDIT_BRUTYPES,
			$this::$PRIVILEGE_DEL_BRUTYPES,
				
			$this::$PRIVILEGE_VIEW_DOCS,
			$this::$PRIVILEGE_SHARE_DOCS,
			$this::$PRIVILEGE_ADD_DOCS,
			$this::$PRIVILEGE_EDIT_DOCS,
			$this::$PRIVILEGE_DEL_DOCS,
				
			$this::$PRIVILEGE_OPTIONS_USERS,
			$this::$PRIVILEGE_VIEW_USERS,
			$this::$PRIVILEGE_SHARE_USERS,
			$this::$PRIVILEGE_ADD_USERS,
			$this::$PRIVILEGE_DEL_USERS,
			$this::$PRIVILEGE_EDIT_USERS
	
			);
	}
	
	private function SetFlightPrivilegeArr()
	{
		$this->flightPrivilegeArr = array(
				$this::$PRIVILEGE_VIEW_FLIGHTS,
				$this::$PRIVILEGE_SHARE_FLIGHTS,
				$this::$PRIVILEGE_ADD_FLIGHTS,
				$this::$PRIVILEGE_EDIT_FLIGHTS,
				$this::$PRIVILEGE_DEL_FLIGHTS,
				$this::$PRIVILEGE_FOLLOW_FLIGHTS,
				$this::$PRIVILEGE_TUNE_FLIGHTS);
	}
	
	private function SetSlicePrivilegeArr()
	{
		$this->slicePrivilegeArr = array(
				$this::$PRIVILEGE_VIEW_SLICES,
				$this::$PRIVILEGE_SHARE_SLICES,
				$this::$PRIVILEGE_ADD_SLICES,
				$this::$PRIVILEGE_EDIT_SLICES,
				$this::$PRIVILEGE_DEL_SLICES);
	}
	
	private function SetEnginePrivilegeArr()
	{
		$this->enginePrivilegeArr = array(
				$this::$PRIVILEGE_VIEW_ENGINES,
				$this::$PRIVILEGE_SHARE_ENGINES,
				$this::$PRIVILEGE_EDIT_ENGINES,
				$this::$PRIVILEGE_DEL_ENGINES);
	}
	
	private function SetBruTypesPrivilegeArr()
	{
		$this->bruTypesPrivilegeArr = array(
				$this::$PRIVILEGE_VIEW_BRUTYPES,
				$this::$PRIVILEGE_SHARE_BRUTYPES,
				$this::$PRIVILEGE_ADD_BRUTYPES,
				$this::$PRIVILEGE_EDIT_BRUTYPES,
				$this::$PRIVILEGE_DEL_BRUTYPES);
	}
	
	private function SetDocsPrivilegeArr()
	{
		$this->docsPrivilegeArr = array(
			$this::$PRIVILEGE_VIEW_DOCS,
			$this::$PRIVILEGE_SHARE_DOCS,
			$this::$PRIVILEGE_ADD_DOCS,
			$this::$PRIVILEGE_EDIT_DOCS,
			$this::$PRIVILEGE_DEL_DOCS);
	}
	
	private function SetUserPrivilegeArr()
	{
		$this->userPrivilegeArr = array(
			$this::$PRIVILEGE_OPTIONS_USERS,
			$this::$PRIVILEGE_VIEW_USERS,
			$this::$PRIVILEGE_SHARE_USERS,
			$this::$PRIVILEGE_ADD_USERS,
			$this::$PRIVILEGE_DEL_USERS,
			$this::$PRIVILEGE_EDIT_USERS);
	}
	
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
				
			if(count(array_intersect($inputPrivilege, $this->docsPrivilegeArr)) == count($this->docsPrivilegeArr))
			{
				$privilegeString .= "; DocsGrant";
				$inputPrivilege = array_diff($inputPrivilege, $this->docsPrivilegeArr);
			}
				
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
		
		$query = "SHOW TABLES LIKE 'user_avaliability';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_avaliability` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`type` VARCHAR(100),
				`userId` INT(11),
				`targetId` INT(11),
				`allowedBy` VARCHER(255),
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute())
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
		
		$query = "SHOW TABLES LIKE 'user_activity';";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_activity` (`id` BIGINT NOT NULL AUTO_INCREMENT,
				`userId` INT(11),
				`acton` VARCHAR(255),
				`target` VARCHAR(255),
				`sender` VARCHAR(255),
				`timestamp` VARCHAR(100),
				`comment` VARCHAR(255),
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
	
	public function GetUserInfo($extRequester)
	{
		$requester = $extRequester;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT * FROM `user_personal` WHERE `id`='".$requester."' LIMIT 1;");
		
		$userInfo = array();
		if($row = $result->fetch_array())
		{
			foreach ($row as $key => $val)
			{
				$userLogin[$key] = $val;
			}			
		}
	
		$c->Disconnect();
		unset($c);
	
		return $userLogin;
	}
	
	public function GetLastActionByActionName($extUserId, $extActionName)
	{
		$userId = $extUserId;
		$actionName = $extActionName;
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `*` FROM `user_activity` WHERE `userId`='".$userId."' AND `acton` = '".$actionName."' ORDER BY `id` ASC LIMIT 1;");
	
		$answer = array();
		$row = $result->fetch_array();
		foreach($row as $key => $val)
		{
			$answer[$key] = $val;
		}
	
		$c->Disconnect();
		unset($c);
	
		return $answer;
	}
	
	public function GetUsersList($userId)
	{
		$userName = $this->GetUserNameById($userId);
		$avalibleUsers = $this->GetAvaliableUsers($userName);
		return $this->GetUsersListByAvaliableIds($avalibleUsers);
	}
	
	public function GetUsersListByAvaliableIds($extAvaliableUsersIds)
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
				$userInfoArr[] = $row;
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
			foreach ($row as $key => $value)
			{
				$userInfo[$key] = $value;
				
				/*array("id"=>$row['id'],
					"login"=>$row['login'],
					"company"=>$row['company'],
					"privilege"=>$row['privilege'],
					"options"=>$row['options'],
					"subscribers"=>$row['subscribers'],
					"author"=>$row['author']);*/
			}
		}

		$c->Disconnect();
		unset($c);
	
		return $userInfo;
	}
	
	public function GetUserPrivilege($extUsername)
	{
		$username = $extUsername;
				
		if($username != '') {
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
		} else {
			return [];
		}
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
	
	public function GetAvaliableFlights($extUsername)
	{
		$username = $extUsername;
	
		$userId = $this->GetIdByUsername($username);
		$avaliabeFlights = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `userId`='".$userId."' AND `type`='".$this::$AVALIABILITY_FLIGHTS."';");
		
		while($row = $result->fetch_array())
		{
			$avaliabeFlights[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`) 
				VALUES ('".$this::$AVALIABILITY_FLIGHTS."', '".$userId."', '".$flightId."', '".$username."');";
		
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
			$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_FLIGHTS."', '".$userId."', '".$flightId."', '".$username."');";
			
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
	
		
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."'	AND
				`targetId` = '".$flightId."' AND `type`='".$this::$AVALIABILITY_FLIGHTS."';";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `targetId` = '".$flightId."' AND " .
			"`type`='".$this::$AVALIABILITY_FLIGHTS."';";
	
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
	
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `targetId`='".$userId."' AND `type`='".$this::$AVALIABILITY_SLICES."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeSlices[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_avaliability` (`type`,`userId`, `targetId`, `allowedBy`)
				VALUES ('".$this::$AVALIABILITY_SLICES."', '".$userId."', '".$sliceId."', '".$username."');";
	
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
			$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_SLICES."', '".$userId."', '".$sliceId."', '".$username."');";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."' AND " .
				"`targetId` = '".$sliceId."' AND `type`='".$this::$AVALIABILITY_SLICES."';";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `targetId` = '".$sliceId."' AND ".
			"`type`='".$this::$AVALIABILITY_SLICES."';";
	
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
	
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `userId`='".$userId."' AND `type`='".$this::$AVALIABILITY_ENGINES."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeEngines[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_engine` (`type`, `userId`, `targetId`, `allowedBy`)
				VALUES ('".$this::$AVALIABILITY_ENGINES."', '".$userId."', '".$engineId."', '".$username."');";
	
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
			$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_ENGINES."', '".$userId."', '".$engineId."', '".$username."');";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."'	AND
				`targetId` = '".$engineId."' AND `type`='".$this::$AVALIABILITY_ENGINES."';";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `targetId` = '".$engineId."' ".
			"AND `type`='".$this::$AVALIABILITY_ENGINES."';";
		
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
	
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `userId`='".$userId."' AND `type`='".$this::$AVALIABILITY_FDR_TYPES."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeBruTypes[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
				VALUES ('".$this::$AVALIABILITY_FDR_TYPES."', '".$userId."', '".$bruTypeId."', '".$username."');";
	
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
			$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_FDR_TYPES."', ".$userId."', '".$bruTypeId."', '".$username."');";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."'	AND
				`targetId` = '".$bruTypeId."' AND `type`='".$this::$AVALIABILITY_FDR_TYPES."';";
		
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
	
		$query = "DELETE FROM `user_avaliability` ".
			"WHERE `targetId` = '".$bruTypeId."' AND `type`='".$this::$AVALIABILITY_FDR_TYPES."';";
	
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
	
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `userId`='".$userId."' AND `type`='".$this::$AVALIABILITY_DOCS."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeDocs[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
				VALUES ('".$this::$AVALIABILITY_DOCS."', '".$userId."', '".$docId."', '".$username."');";
	
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
			$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_DOCS."', ".$userId."', '".$docId."', '".$username."');";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."' AND
				`targetId` = '".$docId."' AND `type`='".$this::$AVALIABILITY_DOCS."';";
	
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
	
	
		$query = "DELETE FROM `user_avaliability` ".
			"WHERE `targetId` = '".$docId."' AND `type`='".$this::$AVALIABILITY_DOCS."';";
	
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
	
		$result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
				"WHERE `userId`='".$userId."' AND `type`='".$this::$AVALIABILITY_USERS."';");
	
		while($row = $result->fetch_array())
		{
			$avaliabeUsers[] = $row['targetId'];
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
	
		$query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
				VALUES ('".$this::$AVALIABILITY_USERS."', '".$userMasterId."', '".$userSlaveId."', '".$username."');";
	
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
			$query = "INSERT INTO `user_avaliability` (`type`,`userId`, `targetId`, `allowedBy`)
					VALUES ('".$this::$AVALIABILITY_USERS."', '".$userMasterId."', '".$userSlaveId."', '".$username."');";
	
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
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userMasterId."'	AND
				`targetId` = '".$userSlaveId."' AND `type`='".$this::$AVALIABILITY_USERS."';";
	
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
	
		$query = "DELETE FROM `user_avaliability` WHERE " . 
			"`targetId` = '".$userSlaveId."' AND `type`='".$this::$AVALIABILITY_USERS."';";
	
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
	
		$query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."';";
	
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
	}
	
	public function SetUserLanguage($login, $lang)
	{	
		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "UPDATE `user_personal` SET `lang` = '".$lang."'
				WHERE `login` = '".$login."';";

		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
			
		$c->Disconnect();
		unset($c);
	}
	
	public function GetLastAction($extUserId, $extAction)
	{
		$userId = $extUserId;
		$action= $extAction;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT * FROM `user_activity` WHERE `action` = '".$action."' AND `userId` = '".$userId."' ORDER BY `id` DESC LIMIT 1;";
		$result = $link->query($query);
	
		$lastAction = null;
	
		if($row = $result->fetch_array())
		{
			$lastAction = array();
			foreach ($row as $key => $val)
			{
				$lastAction[$key] = $val;
			}
		}
	
		$c->Disconnect();
		unset($c);
	
		return $lastAction;
	}
	
	public function GetLastActionFromRange($extUserId, $extActionsRange)
	{
		$userId = $extUserId;
		$actionsRange = implode("','", $extActionsRange);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT * FROM `user_activity` WHERE `action` IN ('".$actionsRange."') AND `userId` = '".$userId."' ORDER BY `id` DESC LIMIT 1;";
		$result = $link->query($query);
		
		$lastAction = null;
		
		if($row = $result->fetch_array())
		{
			$lastAction = array();
			foreach ($row as $key => $val)
			{
				$lastAction[$key] = $val;
			}
		}
	
		$c->Disconnect();
		unset($c);
		
		return $lastAction;
	}
	
	public function RegisterUserAction($extAction, $extStatus, $extUserId,  
			$extSenderId, $extSenderName, $extTargetId, $extTargetName)
	{
		$action = $extAction;
		$status = $extStatus;
		$userId = $extUserId;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "INSERT INTO `user_activity` (`action`,`status`, `userId`, `senderId`, `senderName`, `targetId`, `targetName`)
				VALUES ('".$action."', '".$status."', '".$userId."', " . 
						"'".$senderId."','".$senderName."','".$targetId."', '".$targetName."');";

		$stmt = $link->prepare($query);
		$executionStatus = $stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
		unset($c);
		
		return $executionStatus;
	}
}



?>