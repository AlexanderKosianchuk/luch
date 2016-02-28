<?php
	
require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if((isset($_POST['filePath']) && ($_POST['filePath'] != NULL) &&
		isset($_POST['bruType']) && ($_POST['bruType'] != NULL)))
	{
		$filePath = $_POST['filePath'];
		$bruType = $_POST['bruType'];
	
		$Fl = new Flight();
		$file = $filePath;
		
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfo($bruType);
		$frameLength = $bruInfo['frameLength'];
		$stepLength = $bruInfo['stepLength'];
		$wordLength = $bruInfo['wordLength'];
		$headerLength = $bruInfo['headerLength'];
		$headerScr = $bruInfo['headerScr'];
		$frameSyncroCode = $bruInfo['frameSyncroCode'];
		unset($Bru);
		
		$flightInfo['bruType'] = $bruType;
		
		if(($headerScr != '') || ($headerScr != null))
		{
			eval ($headerScr);
			
			unset($Fl);
			
			$flightInfo['startCopyTime'] = date('H:i:s Y-m-d', $flightInfo['startCopyTime']);
	
			echo json_encode($flightInfo);
		}
	}
	else
	{
		error_log("Incorect params set. POST - " . json_encode($_POST) . ". AsyncHeaderReader.php");
		echo("Incorect params set. Page asyncHeaderReader.php");	
	}
}
else
{
	echo("Authorization error. Page asyncHeaderReader.php");
}

?>