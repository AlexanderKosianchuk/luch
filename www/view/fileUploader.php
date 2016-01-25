<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/UploaderModel.php");

$M = new UploaderModel($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	if($M->action == $M->flightActions["flightShowUploadingOptions"]) //show form for uploading
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['index']) && 
					isset($M->data['bruType']) && 
					isset($M->data['file']))
			{
				$index = $M->data['index'];
				$bruType = $M->data['bruType'];
				$filePath = UPLOADED_FILES_PATH . $M->data['file'];
	
				$flightParamsSrt = $M->ShowFlightParams($index, $bruType, $filePath);
				
				$answ["status"] = "ok";
				$answ["data"] = $flightParamsSrt;
				echo(json_encode($answ));
			}
			else 
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
		
		unset($U);
	}
	else if($M->action == $M->flightActions["flightUploaderPreview"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_EDIT_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['bruType']) &&
					isset($M->data['file']))
			{
				$bruType = $M->data['bruType'];
				$filePath = UPLOADED_FILES_PATH . $M->data['file'];
				
				$M->CopyPreview($bruType, $filePath);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". " .
						"Action: " .
						$M->action . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	
		unset($U);
	}
	else if($M->action == $M->flightActions["flightCutFile"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_EDIT_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['bruType']) &&
					isset($M->data['file']) &&
					isset($M->data['startCopyTime']) &&
					isset($M->data['endCopyTime']) &&
					isset($M->data['startSliceTime']) &&
					isset($M->data['endSliceTime']))
			{		
				$bruType = $M->data['bruType'];
				$filePath = $M->data['file'];
					
				$startCopyTime = $M->data['startCopyTime'];
				$endCopyTime = $M->data['endCopyTime'];
				$startSliceTime = $M->data['startSliceTime'];
				$endSliceTime = $M->data['endSliceTime'];
				
				$M->CutCopy($bruType, $filePath, 
					$startCopyTime, $endCopyTime, 
					$startSliceTime, $endSliceTime);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". " .
						"Action: " .
						$M->action . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	
		unset($U);
	}
	else if($M->action == $M->flightActions["flightCyclicSliceFile"]) 
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_EDIT_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['bruType']) &&
					isset($M->data['file']) &&
					isset($M->data['startCopyTime']) &&
					isset($M->data['endCopyTime']) &&
					isset($M->data['startSliceTime']))
			{
				$bruType = $M->data['bruType'];
				$filePath = $M->data['file'];
					
				$startCopyTime = $M->data['startCopyTime'];
				$endCopyTime = $M->data['endCopyTime'];
				$startSliceTime = $M->data['startSliceTime'];
	
				$M->CyclicSliceCopy($bruType, $filePath,
						$startCopyTime, $endCopyTime, $startSliceTime);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". " .
						"Action: " .
						$M->action . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	
		unset($U);
	}
	else if($M->action == $M->flightActions["flightProcces"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
		{	
			if(isset($M->data['bruType']) &&
					isset($M->data['fileName']) &&
					isset($M->data['tempFileName']) &&
					isset($M->data['flightInfo']) &&
					isset($M->data['flightAditionalInfo']))
			{			
				$bruType = $M->data['bruType'];
				$uploadedFile = $M->data['fileName'];
					
				$tempFileName = $M->data['tempFileName'];
				$receivedFlightInfo = $M->data['flightInfo'];
				$receivedFlightAditionalInfo = $M->data['flightAditionalInfo'];
				$flightInfo = array();
				$flightAditionalInfo = array();
				
				//in such way it was passed in js because of imposible to do it by usual asoc arr
				for($i = 0; $i < count($receivedFlightInfo); $i+=2)
				{
					if((string)$receivedFlightInfo[$i + 1] != '')
					{
						$flightInfo[(string)$receivedFlightInfo[$i]] = 
							(string)$receivedFlightInfo[$i + 1];	
					}
					else 
					{
						$flightInfo[(string)$receivedFlightInfo[$i]] = "x";
					}				
				}
				
				$aditionalInfoVars = '';
				if($receivedFlightAditionalInfo != '0')
				{
					for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2)
					{
						$flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] = 
							(string)$receivedFlightAditionalInfo[$i + 1];
					}
					
					foreach($flightAditionalInfo as $key => $val) 
					{
						$aditionalInfoVars .= $key . ":" . $val . ";";
					}
				}
				
				$bort = $flightInfo["bort"];
				$voyage = $flightInfo["voyage"];
				$copyCreationTime = $flightInfo["copyCreationTime"];
				$copyCreationDate = $flightInfo["copyCreationDate"];
				$performer = $flightInfo["performer"];
				$departureAirport = $flightInfo["departureAirport"];
				$arrivalAirport = $flightInfo["arrivalAirport"];
				$totalPersentage = 100;
				
				$M->ProccessFlightData($tempFileName,
					$bort,
					$voyage,
					$copyCreationTime,
					$copyCreationDate,
					$bruType,
					$performer,
					$departureAirport,
					$arrivalAirport,
					$aditionalInfoVars,
					$uploadedFile,
					$totalPersentage
				);
				
				$answ = array(
						"status" => "ok",
						"data" => $uploadedFile
				);
				echo(json_encode($answ));
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". " .
						"Action: " .
						$M->action . ". Page fileUploader.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	
		unset($U);
	}
	else if($M->action == $M->flightActions["flightProccesAndCheck"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
		{		
			$U = new User();
		
			if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
			{	
				if(isset($M->data['bruType']) &&
						isset($M->data['fileName']) &&
						isset($M->data['tempFileName']) &&
						isset($M->data['flightInfo']) &&
						isset($M->data['flightAditionalInfo']))
				{			
					$bruType = $M->data['bruType'];
					$uploadedFile = $M->data['fileName'];
						
					$tempFileName = $M->data['tempFileName'];
					$receivedFlightInfo = $M->data['flightInfo'];
					$receivedFlightAditionalInfo = $M->data['flightAditionalInfo'];
					$flightInfo = array();
					$flightAditionalInfo = array();
					
					//in such way it was passed in js because of imposible to do it by usual aasoc arr
					for($i = 0; $i < count($receivedFlightInfo); $i+=2)
					{
						if((string)$receivedFlightInfo[$i + 1] != '')
						{
							$flightInfo[(string)$receivedFlightInfo[$i]] = 
								(string)$receivedFlightInfo[$i + 1];	
						}
						else 
						{
							$flightInfo[(string)$receivedFlightInfo[$i]] = "x";
						}				
					}
					
					$aditionalInfoVars = '';
					if($receivedFlightAditionalInfo != '0')
					{
						for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2)
						{
							$flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] = 
								(string)$receivedFlightAditionalInfo[$i + 1];
						}
						
						foreach($flightAditionalInfo as $key => $val) 
						{
							$aditionalInfoVars .= $key . ":" . $val . ";";
						}
					}
					
					$bort = $flightInfo["bort"];
					$voyage = $flightInfo["voyage"];
					$copyCreationTime = $flightInfo["copyCreationTime"];
					$copyCreationDate = $flightInfo["copyCreationDate"];
					$performer = $flightInfo["performer"];
					$departureAirport = $flightInfo["departureAirport"];
					$arrivalAirport = $flightInfo["arrivalAirport"];
					$totalPersentage = 50;
					
					$flightId = $M->ProccessFlightData($tempFileName,
						$bort,
						$voyage,
						$copyCreationTime,
						$copyCreationDate,
						$bruType,
						$performer,
						$departureAirport,
						$arrivalAirport,
						$aditionalInfoVars,
						$uploadedFile,
						$totalPersentage
					);
					
					$M->ProccesFlightException($flightId,
							$tempFileName
					);
					
					$answ = array(
							"status" => "ok",
							"data" => $uploadedFile
					);
					echo(json_encode($answ));
				}
				else
				{
					$answ["status"] = "err";
					$answ["error"] = "Not all nessesary params sent. Post: ".
							json_encode($_POST) . ". " .
							"Action: " .
							$M->action . ". Page fileUploader.php";
					echo(json_encode($answ));
				}
			}
			else
			{
				echo($M->lang->notAllowedByPrivilege);
			}
		
			unset($U);
		}
	}
	else if($M->action == $M->flightActions["flightProccesCheckAndCompareToEtalon"]) //show form for uploading
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
		{
				
			$answ["status"] = "ok";
			/*if(isset($M->data['bruType']) &&
			 isset($M->data['file']) &&
					isset($M->data['startCopyTime']) &&
					isset($M->data['endCopyTime']) &&
					isset($M->data['startSliceTime']) &&
					isset($M->data['endSliceTime']))
			{
			$bruType = $M->data['bruType'];
			$filePath = $M->data['file'];
				
			$startCopyTime = $M->data['startCopyTime'];
			$endCopyTime = $M->data['endCopyTime'];
			$startSliceTime = $M->data['startSliceTime'];
			$endSliceTime = $M->data['endSliceTime'];
	
			$M->CutCopy($bruType, $filePath,
					$startCopyTime, $endCopyTime,
					$startSliceTime, $endSliceTime);
			}
			else
			{
			$answ["status"] = "err";
			$answ["error"] = "Not all nessesary params sent. Post: ".
			json_encode($_POST) . ". " .
			"Action: " .
			$M->action . ". Page fileUploader.php";
			echo(json_encode($answ));
			}*/
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	
		unset($U);
	}
	else if($M->action == $M->flightActions["flightEasyUpload"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
		{
			$U = new User();
	
			if(in_array($U::$PRIVILEGE_ADD_FLIGHTS, $M->privilege))
			{
				if(isset($M->data['bruType']) &&
						isset($M->data['fileName']) &&
						isset($M->data['tempFileName']))
				{
					$bruType = $M->data['bruType'];
					$fileName = $M->data['fileName'];
					$uploadedFile = UPLOADED_FILES_PATH . $fileName;
					$tempFileName = $M->data['tempFileName'];
					
					$flightInfoFromHeader = $M->ReadHeader($bruType, $uploadedFile);
					
					$bort = "x";
					if(isset($flightInfoFromHeader["bort"]))
					{
						$bort = $flightInfoFromHeader["bort"];
					}
					
					$voyage = "x";
					if(isset($flightInfoFromHeader["voyage"]))
					{
						$voyage = $flightInfoFromHeader["voyage"];
					}
					
					$departureAirport = "x";
					if(isset($flightInfoFromHeader["departureAirport"]))
					{
						$departureAirport = $flightInfoFromHeader["departureAirport"];
					}
					
					$arrivalAirport = "x";
					if(isset($flightInfoFromHeader["arrivalAirport"]))
					{
						$arrivalAirport = $flightInfoFromHeader["arrivalAirport"];
					}
					
					$copyCreationTime = "00:00:00";
					$copyCreationDate = "2000-01-01";
					if(isset($flightInfoFromHeader['startCopyTime']))
					{
						$startCopyTime = strtotime($flightInfoFromHeader['startCopyTime']);
						$copyCreationTime = date('H:i:s', $startCopyTime);
						$copyCreationDate = date('Y-m-d', $startCopyTime);
					}
			
					$performer = null;
					
					$aditionalInfoVars = $M->CheckAditionalInfoFromHeader($bruType, $flightInfoFromHeader);
					$totalPersentage = 50;
					
					$flightId = $M->ProccessFlightData($tempFileName,
							$bort,
							$voyage,
							$copyCreationTime,
							$copyCreationDate,
							$bruType,
							$performer,
							$departureAirport,
							$arrivalAirport,
							$aditionalInfoVars,
							$uploadedFile,
							$totalPersentage
					);
						
					$M->ProccesFlightException($flightId,
							$tempFileName
					);
						
					$answ = array(
							"status" => "ok",
							"data" => $fileName
					);
					echo(json_encode($answ));
				}
				else
				{
					$answ["status"] = "err";
					$answ["error"] = "Not all nessesary params sent. Post: ".
							json_encode($_POST) . ". " .
							"Action: " .
							$M->action . ". Page fileUploader.php";
					echo(json_encode($answ));
				}
			}
			else
			{
				echo($M->lang->notAllowedByPrivilege);
			}
	
			unset($U);
		}
	}
	else if($M->action == $M->flightActions["flightDelete"]) // delete
	{
		if(in_array($U::$PRIVILEGE_DEL_FLIGHTS, $M->privilege))
		{
			//$M->DropCache();
			$M->DeleteFlight();
				
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
			exit();
		}
		else
		{
			echo($M->lang->notAllowedByPrivilege);
		}
	}
	else 
	{
			echo("Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".");
			
			error_log("Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".");
	}
}
else 
{
	echo("Authorization error. Page: " . $M->currPage);
	error_log("Authorization error. Page: " . $M->currPage);
}

?>


