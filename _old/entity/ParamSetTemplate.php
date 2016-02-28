<?php

require_once("includes.php"); 

class PSTempl
{
	public function CreatePSTTable($extPSTTableName)
	{
		$PSTTableName = $extPSTTableName;
		
		$query = "SHOW TABLES LIKE '".$PSTTableName."';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE IF NOT EXISTS `".$PSTTableName."` (
				`id` MEDIUMINT NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(20),
				`paramCode` VARCHAR(20),
				`isDefault` BOOLEAN NOT NULL DEFAULT 0,
				`minYaxis` FLOAT DEFAULT NULL,
				`maxYaxis` FLOAT DEFAULT NULL,
				`user` VARCHAR(200) DEFAULT '',
				PRIMARY KEY (id))";
				
			$stmt = $link->prepare($query);
			$stmt->execute();
			$stmt->close();
		}
		$c->Disconnect();
		unset($c);
	}
	
	public function AddPSTTable($extBruType, $extPSTTableName)
	{
		$bruType = $extBruType;
		$PSTTableName = $extPSTTableName;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "UPDATE `brutypes` 
			SET paramSetTemplateListTableName = '".
			$PSTTableName."' WHERE bruType='".$bruType."';";
		$stmt = $link->prepare($query);
		$stmt->execute();		
		$stmt->close();
		$c->Disconnect();
		
		unset($c);
	}
	
	public function GetPSTList($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
		$PSTList = array();
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$link2 = $c->Connect();
		
		$query = "SELECT DISTINCT `name` FROM `".$PSTListTableName."` WHERE `user` = '".$user."';";
		$result = $link->query($query);
		while($row = $result->fetch_array())
		{
			$query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE (`name` = ?) AND (`user` = ?);";
			$stmt = $link2->prepare($query);
			$stmt->bind_param('ss', $templateName, $user);
			$templateName = $row['name'];
			$stmt->execute();
			$stmt->bind_result($paramCode); 
			$PSTRow = array();
			$paramCodeList = array();
			while ($stmt->fetch()) 
			{
				array_push($paramCodeList, $paramCode);			
			}
			$PSTRow = array($templateName, $paramCodeList);	
			array_push($PSTList, $PSTRow);		
		}
		$c->Disconnect();
		unset($c);

		return $PSTList;
	}
	
	public function GetDefaultPST($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
		$PSTList = array();
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT DISTINCT `name` FROM `".$PSTListTableName."` WHERE `isDefault` = 1 " .
			"AND `user` = '".$user."';";
		$result = $link->query($query);
		$templateName = '';
		if($row = $result->fetch_array())
		{
			$templateName = $row['name'];
		}
		$c->Disconnect();
		unset($c);
	
		return $templateName;
	}
	
	
	public function AddParamToTemplate($extPSTTableName, 
		$extTemplateName,$extParamName, $extUser)
	{
		$PSTTableName = $extPSTTableName; 
		$templateName = $extTemplateName;
		$paramName = $extParamName;
		$user = $extUser;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT `name`, `paramCode`
			FROM `".$PSTTableName."` 
			WHERE ((`name` = '".$templateName."') 
			AND (`paramCode` = '".$paramName."')
			AND (`user` = '".$user."'));";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "INSERT INTO `".$PSTTableName."` 
			(`name`, `paramCode`, `user`) 
			VALUES ('".$templateName."','".$paramName."','".$user."');";
			$stmt = $link->prepare($query);
			$stmt->execute();
		}
		$c->Disconnect();
		unset($c);
	}
	
	public function AddParamToTemplateWithMinMax($extPSTTableName,
			$extTemplateName, $extParamName, $extAxisMin, $extAxisMax, $extUser)
	{
		$PSTTableName = $extPSTTableName;
		$templateName = $extTemplateName;
		$paramName = $extParamName;
		$axisMin = $extAxisMin;
		$axisMax = $extAxisMax;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `name`, `paramCode`
			FROM `".$PSTTableName."`
			WHERE ((`name` = '".$templateName."')
			AND (`paramCode` = '".$paramName."')
			AND (`minYaxis` = '".$axisMin."')
			AND (`maxYaxis` = '".$axisMax."')
			AND (`user` = '".$user."'));";
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "INSERT INTO `".$PSTTableName."`
			(`name`, `paramCode`, `minYaxis`, `maxYaxis`, `user`)
			VALUES ('".$templateName."','".$paramName."', '".$axisMin."', '".$axisMax."', '".$user."');";
			$stmt = $link->prepare($query);
			$stmt->execute();
		}
		$c->Disconnect();
		unset($c);
	}
	
	public function GetPSTByName($extPSTListTableName, $extPSTName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$PSTName = $extPSTName;
		$user = $extUser;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE (`name` = ? AND `user` = ?);";
		$stmt = $link->prepare($query);
		$stmt->bind_param('ss', $PSTName, $user);
		$stmt->execute();
		$stmt->bind_result($paramCode); 
		$paramCodeList = array();
		while ($stmt->fetch()) 
		{
			array_push($paramCodeList, $paramCode);			
		}
		
		$c->Disconnect();
		unset($c);

		return $paramCodeList;
	}
	
	public function GetPSTParams($extPSTListTableName, $extPSTName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$PSTName = $extPSTName;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE (`name` = ? AND `user` = ?);";
		$stmt = $link->prepare($query);
		$stmt->bind_param('ss', $PSTName, $user);
		$stmt->execute();
		$stmt->bind_result($paramCode);
		$paramCodeList = array();
		while ($stmt->fetch())
		{
			array_push($paramCodeList, $paramCode);
		}
	
		$c->Disconnect();
		unset($c);
	
		return $paramCodeList;
	}
	
	public function GetAllPSTParams($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT DISTINCT `paramCode` FROM `".
			$PSTListTableName."` WHERE `user` = '".$user."';";
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->bind_result($paramCode); 
		$paramCodeList = array();
		while ($stmt->fetch()) 
		{
			array_push($paramCodeList, $paramCode);			
		}	
		
		$c->Disconnect();
		unset($c);

		return $paramCodeList;
	}
	
	public function DeleteTemplate($extPSTListTableName, $extTemplateName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$tplName = $extTemplateName;
		$user = $extUser;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "DELETE FROM `".$PSTListTableName."` WHERE `name` = '".$tplName."' AND `user` = '".$user."';";
		$link->query($query);	
		$c->Disconnect();
		unset($c);
	}
	
	public function SetDefaultTemplate($extPSTListTableName, $extTemplateName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$tplName = $extTemplateName;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "UPDATE `".$PSTListTableName."` SET `isDefault` = '0' WHERE `user` = '".$user."';";
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$query = "UPDATE `".$PSTListTableName."` SET `isDefault` = '1' 
			WHERE `name` ='".$tplName."' AND `user` = '".$user."';";
		
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
		
		$c->Disconnect();
		
		unset($c);
	}
	
	public function GetDefaultTemplateParams($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `paramCode` FROM `".$PSTListTableName."` WHERE `isDefault` = '1' ". 
				"AND `user` = '".$user."';";
		$result = $link->query($query);
		$paramCodeList = array();
		while($row = $result->fetch_array())
		{
			array_push($paramCodeList, $row['paramCode']);
		}
		
		$c->Disconnect();
		unset($c);

		return $paramCodeList;
	}
	
	public function GetDefaultTemplateName($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT DISTINCT (`name`) FROM `".$PSTListTableName."` WHERE `isDefault` = '1' AND `user` = '".$user."';";
		$result = $link->query($query);
		$name = ""; //if no default try to use last
		if($row = $result->fetch_array())
		{
			$name =  $row['name'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $name;
	}
	
	public function GetLastTemplateName($extPSTListTableName, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT DISTINCT (`name`) FROM `".$PSTListTableName."` WHERE `name` = 'last' AND `user` = '".$user."';";
		$result = $link->query($query);
		$name = ""; //if no default try to use last
		if($row = $result->fetch_array())
		{
			$name =  $row['name'];
		}
	
		$c->Disconnect();
		unset($c);
	
		return $name;
	}
	
	public function UpdateParamMinMax($extPSTListTableName, $extTemplateName, $extParamCode, 
			$extMin, $extMax, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$tplName = $extTemplateName;
		$paramCode = $extParamCode;
		$min = $extMin;
		$max = $extMax;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "UPDATE `".$PSTListTableName."`SET `minYaxis` = '".$min."', `maxYaxis` = '".$max."' 
				WHERE `name` = '".$tplName."' AND  `paramCode` = '".$paramCode."' AND `user` = '".$user."';";
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();
	
		$c->Disconnect();
	
		unset($c);
	}
	
	public function GetParamMinMax($extPSTListTableName, $extTemplateName, $extParamCode, $extUser)
	{
		$PSTListTableName = $extPSTListTableName;
		$tplName = $extTemplateName;
		$paramCode = $extParamCode;
		$user = $extUser;
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$query = "SELECT `minYaxis`, `maxYaxis` FROM `".$PSTListTableName."` " .
				"WHERE `name` = '".$tplName."' AND  `paramCode` = '".$paramCode."' ". 
				"AND `user` = '".$user."';";

		$result = $link->query($query);
		$minMax = "";
		if($row = $result->fetch_array())
		{
			$minMax = array( 
				'min' => $row['minYaxis'],
				'max' => $row['maxYaxis']
				);
		}
	
		$c->Disconnect();
		unset($c);
	
		return $minMax;
	}
	
}

?>