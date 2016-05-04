<?php

require_once(@SITE_ROOT_DIR ."/includes.php"); 

//User privilege
//------------

/*
viewFlight,shareFlight,addFlight,editFlight,delFlight,followFlight,tuneFlight,
viewBruType,shareBruType,addBruType,editBruType,delBruType,
optionsUsers,viewUsers,shareUsers,addUser,delUser,editUser
*/

class UserOptions
{
	public function CreateUserOptionssTables()
	{			
		$query = "SHOW TABLES LIKE 'user_personal';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `user_settings` (
				`id` BIGINT NOT NULL AUTO_INCREMENT,
				`user_id` INT,
				`name` VARCHAR(200),
				`value` VARCHAR(200),
				`dt_cr` DATETIME DEFAULT CURRENT_TIMESTAMP
				`dt_up` DATETIME ON UPDATE CURRENT_TIMESTAMP
				PRIMARY KEY (`id`));";
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

	public function GetOptionValue($userId, $optionName)
	{
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT `value` FROM `user_settings` WHERE `user_id`=".$userId." AND `name`='".$optionName."' LIMIT 1;");

		$value = null;
		if($row = $result->fetch_array()) {
			$value = $row['value'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $value;
	}	
}
