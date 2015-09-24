<?
require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	$username = $_SESSION['username'];
	
	if(isset($_POST['action']) && ($_POST['action'] != NULL))
	{	
		$action = $_POST['action'];
		
		if($action == FLIGHT_CONVERT)
		{
			if(isset($_POST['tempFileName']) && ($_POST['tempFileName'] != NULL))
			{
				$tempFile = $_POST['tempFileName'];
			}
			
			$tempFileRoot = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tempFile;
			
			if(isset($_POST['bort']) && ($_POST['bort'] != NULL))
			{
				$bort = $_POST['bort'];
			}
			
			if(isset($_POST['voyage']) && ($_POST['voyage'] != NULL))
			{
				$voyage = $_POST['voyage'];
			}
			
			if(($_POST['copyCreationTime'] != NULL) && ($_POST['copyCreationDate'] != NULL) &&
			isset($_POST['copyCreationTime']) && isset($_POST['copyCreationDate']))
			{	
				$copyCreationTime = $_POST['copyCreationTime'];
				$copyCreationDate = $_POST['copyCreationDate'];
				if(strlen($copyCreationTime) > 5)
				{
					$startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime);
				}
				else
				{
					$startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime . ":00");
				}
			}
			
			if(isset($_POST['bruType']) && ($_POST['bruType'] != NULL))
			{
				$bruType = utf8_encode($_POST['bruType']);
			}
			
			if(isset($_POST['performer']) && ($_POST['performer'] != NULL))
			{
				$performer = $_POST['performer'];
			}
			
			if(isset($_POST['departureAirport']) && ($_POST['departureAirport'] != NULL))
			{
				$departureAirport = $_POST['departureAirport'];
			}
			
			if(isset($_POST['arrivalAirport']) && ($_POST['arrivalAirport'] != NULL))
			{
				$arrivalAirport = $_POST['arrivalAirport'];
			}
			
			$aditionalInfo = '';
			if(isset($_POST['aditionalInfo']) && ($_POST['aditionalInfo'] != NULL))
			{
				$aditionalInfo = $_POST['aditionalInfo'];
			}
			
			if(isset($_POST['uploadedFile']) && ($_POST['uploadedFile'] != NULL))
			{
				$uploadedFile = $_POST['uploadedFile'];
			}
			
			$Fl = new Flight();
			$flightId = $Fl->InsertNewFlight($bort, $voyage,
					$startCopyTime,
					$bruType, $performer,
					$departureAirport, $arrivalAirport,
					$uploadedFile, $aditionalInfo);
			
			$flightInfo = $Fl->GetFlightInfo($flightId);
			$tableNameAp = $flightInfo['apTableName'];
			$tableNameBp = $flightInfo['bpTableName'];			
			$flightId = $flightInfo['id'];
			$fileName = $flightInfo['fileName'];

			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$frameLength = $bruInfo['frameLength'];
			$stepLength = $bruInfo['stepLength'];
			$wordLength = $bruInfo['wordLength'];
			$headerLength = $bruInfo['headerLength'];
			$headerScr = $bruInfo['headerScr'];
			$frameSyncroCode = $bruInfo['frameSyncroCode'];
			//$gradiAp = $Bru->GetBruApGradi($bruType);
			$gradiApByPrefixes = $Bru->GetBruApGradiPrefixOrganized($bruType);
			$prefixFreqArr = $Bru->GetBruApGradiPrefixFreq($bruType);
			$gradiBp = $Bru->GetBruBpGradi($bruType);
			unset($Bru);
			$apTables = $Fl->CreateFlightParamTables($flightId, $gradiApByPrefixes);
			unset($Fl);
	
			$Fr = new Frame();
			$syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $headerLength, $fileName);
			
			$fileDesc = $Fr->OpenFile($fileName);
			$fileSize = $Fr->GetFileSize($fileName);
			
			$frameNum = 0;
			$totalFrameNum = floor(($fileSize - $syncroWordOffset)  / $frameLength);
			
			$fileNameApArr = array();
			$fileNameApDescArr = array();
			foreach($gradiApByPrefixes as $prefix => $item)
			{
				$fileNameAp = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tableNameAp . "_".$prefix.".tbl";
				$fileNameApArr[$prefix] = $fileNameAp;
				$fileNameApDesc = fopen($fileNameAp, "w");
				$fileNameApDescArr[$prefix] = $fileNameApDesc;
			}
			
			$fileNameBp = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tableNameBp . ".tbl";
			$fileNameBpDesc = fopen($fileNameBp, "w");
			
			fseek($fileDesc, $syncroWordOffset, SEEK_SET);
			$curOffset = $syncroWordOffset;
			
			//file can be accesed by ajax while try to open what can cause warning
			error_reporting(E_ALL ^ E_WARNING);

			$algHeap = array();
			if($frameSyncroCode != '')
			{
				while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
				//while(($frameNum < 20) && ($curOffset < $fileSize))
				{			
					$curOffset = ftell($fileDesc);
					$frame = $Fr->ReadFrame($fileDesc, $frameLength);
					$unpackedFrame = unpack("H*", $frame);
					
					if($Fr->CheckSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true)
					{					
						$splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need
						
						$apPhisicsByPrefixes = array();
						foreach($gradiApByPrefixes as $prefix => $gradiAp)
						{
							$channelFreq = $prefixFreqArr[$prefix];
							$phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $gradiAp, $algHeap);
							$apPhisicsByPrefixes[$prefix] = $phisicsFrame;
						}
						
						$bpPhisics = $Fr->ConvertFrameToBinaryParams($splitedFrame, $frameNum, $gradiBp);
						
						$Fr->AppendApFrameToFile($apPhisicsByPrefixes, $fileNameApDescArr);
						$Fr->AppendBpFrameToFile($bpPhisics, $fileNameBpDesc);
						
						$frameNum++;
					}
					else
					{
						$syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $curOffset, $fileName);
						
						fseek($fileDesc, $syncroWordOffset, SEEK_SET);
	
						$framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
						$totalFrameNum = $frameNum + $framesLeft;
						
					}
					
					$fp = fopen($tempFileRoot, "w");
					fwrite($fp, json_encode($frameNum * $frameLength));
					fclose($fp);
				}
			}
			else
			{
				while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
				//while(($frameNum < 20) && ($curOffset < $fileSize))
				{
					$curOffset = ftell($fileDesc);
					$frame = $Fr->ReadFrame($fileDesc, $frameLength);
					$unpackedFrame = unpack("H*", $frame);
						
					$splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need
				
					$apPhisicsByPrefixes = array();
					foreach($gradiApByPrefixes as $prefix => $gradiAp)
					{
						$channelFreq = $prefixFreqArr[$prefix];
						$phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $gradiAp, $algHeap);
						$apPhisicsByPrefixes[$prefix] = $phisicsFrame;
					}
				
					$bpPhisics = $Fr->ConvertFrameToBinaryParams($splitedFrame, $frameNum, $gradiBp);
				
					$Fr->AppendApFrameToFile($apPhisicsByPrefixes, $fileNameApDescArr);
					$Fr->AppendBpFrameToFile($bpPhisics, $fileNameBpDesc);
				
					$frameNum++;
						
					$fp = fopen($tempFileRoot, "w");
					fwrite($fp, json_encode($frameNum * $frameLength));
					fclose($fp);
				}				
			}
			
			error_reporting(E_ALL);

			//not need any more
			$Fr->CloseFile($fileDesc);
			unlink($uploadedFile);

			foreach($fileNameApArr as $prefix => $fileNameAp)
			{
				fclose($fileNameApDescArr[$prefix]);
				$Fr->LoadFileToTable($tableNameAp . "_" . $prefix, $fileNameAp);
				unlink($fileNameAp);
			}
			
			fclose($fileNameBpDesc);
			$Fr->LoadFileToTable($tableNameBp, $fileNameBp);			
			unlink($fileNameBp);
			
			$Usr = new User();
			$Usr->SetFlightAvaliable($username, $flightId);
			unset($Usr);
	
			unset($Fr);			
			//after processing complt we write "done" to let jvscr know complet
			$fp = fopen($tempFileRoot, "w");
			fwrite($fp, json_encode("done " . $flightId));
			fclose($fp);
		}
		else if($action == FLIGHT_PROC)
		{
			if(isset($_POST['flightId']) && ($_POST['flightId'] != NULL))
			{
				$flightId = $_POST['flightId'];
			}
			
			if(isset($_POST['tempFileName']) && ($_POST['tempFileName'] != NULL))
			{
				$tempFile = $_POST['tempFileName'];
			}
				
			$tempFileRoot = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tempFile;
					
			$fp = fopen($tempFileRoot, "w");
			fwrite($fp, json_encode("proccess"));
			fclose($fp);
			
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($flightId);
			$apTableName = $flightInfo["apTableName"];
			$bpTableName = $flightInfo["bpTableName"];
			$excEventsTableName = $flightInfo["exTableName"];
			$tableGuid = substr($apTableName, 0, 14);
			unset($Fl);
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
			$excListTableName = $bruInfo["excListTableName"];
			$apGradiTableName = $bruInfo["gradiApTableName"];
			$bpGradiTableName = $bruInfo["gradiBpTableName"];
				
			if ($excListTableName != "")
			{
				$Bru = new Bru();
				$bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
				$excListTableName = $bruInfo["excListTableName"];
				$apGradiTableName = $bruInfo["gradiApTableName"];
				$bpGradiTableName = $bruInfo["gradiBpTableName"];
		
				$FEx = new FlightException();
				$flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
				//Get exc refParam list
				$excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);
				unset($Bru);
				
				$exList = $FEx->GetFlightExceptionTable($excListTableName);
	
				//file can be accesed by ajax what can cause warning
				error_reporting(E_ALL ^ E_WARNING);
				
				//perform proc be cached table
				for($i = 0; $i < count($exList); $i++)
				{
					$fp = fopen($tempFileRoot, "w");
					fwrite($fp, json_encode($exList[$i]["code"]));
					fclose($fp);
					
					$curExList = $exList[$i];
					$FEx->PerformProcessingByExceptions($curExList, $flightInfo, $flightExTableName,
							$apTableName, $bpTableName, $flightInfo["startCopyTime"], $bruInfo["stepLength"]);
				}
				
				error_reporting(E_ALL);
				
				$fp = fopen($tempFileRoot, "w");
				fwrite($fp, json_encode("done"));
				fclose($fp);
				
				exit();	
			}
			else
			{
				$fp = fopen($tempFileRoot, "w");
				fwrite($fp, json_encode("done"));
				fclose($fp);
				
				exit();				
			}
		}
		else if($action == FLIGHT_COMPARE_TO_ETALON)
		{
			if(isset($_POST['flightId']) && ($_POST['flightId'] != NULL))
			{
				$flightId = $_POST['flightId'];
			}
			
			if(isset($_POST['sliceId']) && ($_POST['sliceId'] != NULL))
			{
				$sliceId = $_POST['sliceId'];
			}
			
			if(isset($_POST['tempFileName']) && ($_POST['tempFileName'] != NULL))
			{
				$tempFile = $_POST['tempFileName'];
			}
		
			$tempFileRoot = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tempFile;
			
			$fp = fopen($tempFileRoot, "w");
			fwrite($fp, json_encode("proccess"));
			fclose($fp);

			$counstructorData = array("action" => SLICE_COMPARE,
				"flightId" => $flightId,
				"sliceId" => $sliceId);
		
			//bad style to use View class in async scripts but it is very comfortable here
			//do no populate
			$SliceView = new SliceView($counstructorData);
			$Sl = new Slice();
			$sliceInfo = $Sl->GetSliceInfo($sliceId);
			$sliceTypeInfo = $Sl->GetSliceTypeInfo($sliceInfo['code']);
			
			//file can be accesed by ajax what can cause warning
			error_reporting(E_ALL ^ E_WARNING);
			
			if($sliceTypeInfo['children'] != '')
			{
				$childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
				$childCodesArray = array_filter($childCodesArray);
				$childCodesArray = array_map('trim', $childCodesArray);
			
				for($j = 0; $j < count($childCodesArray); $j++)
				{
					$sliceCode = $childCodesArray[$j];
					$sliceTypeInfo = $Sl->GetSliceTypeInfo($sliceCode);

					$fp = fopen($tempFileRoot, "w");
					fwrite($fp, json_encode($sliceCode));
					fclose($fp);
					
					$SliceView->CompareSliceToEtalon($flightId, $sliceInfo, $sliceTypeInfo, $sliceCode);
				}
			}
			else
			{
				$sliceCode = $this->sliceInfo['code'];
				
				$fp = fopen($tempFileRoot, "w");
				fwrite($fp, json_encode($sliceCode));
				fclose($fp);
				
				$SliceView->CompareSliceToEtalon($flightId, $sliceInfo, $sliceTypeInfo, $sliceCode);
			}
			unset($Sl);
			
			error_reporting(E_ALL);
			
			$fp = fopen($tempFileRoot, "w");
			fwrite($fp, json_encode("done"));
			fclose($fp);
			exit();
				
			exit();
		}
		else if($action == FLIGHT_DEL_TEMP)
		{
		
			if(isset($_POST['tempFileName']) && ($_POST['tempFileName'] != NULL))
			{
				$tempFile = $_POST['tempFileName'];
			}
				
			$tempFileRoot = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tempFile;
			
			if(file_exists($tempFileRoot))
			{
				unlink($tempFileRoot);
			}
			
			exit();
		}
		else if($action == FLIGHT_EXPORT)
		{
			if(isset($_POST['flightId']) && ($_POST['flightId'] != NULL))
			{
				$flightId = $_POST['flightId'];
			}
			
			$Fl = new Flight();
			$flightInfo = $Fl->GetFlightInfo($flightId);

			$fileGuid = uniqid();
			
			$exportedFileDir = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/";
			$exportedFileName = $flightInfo['bort'] . "_" . 
				date("Y-m-d", $flightInfo['startCopyTime'])  . "_" . 
				$flightInfo['voyage'] . "_" . $fileGuid . "_" . $username;
			$exportedFileRoot = $exportedFileDir . $exportedFileName;
					
			$exportedFiles = array();
			
			$headerFile['dir'] = $exportedFileDir;
			$headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage']."_".$username.".json";
			$headerFile['root'] = $headerFile['dir'].$headerFile['filename'];
			
			$exportedFiles[] = $headerFile;
			
			$C = new DataBaseConnector();
			$Bru = new Bru();
			$prefixes = $Bru->GetBruApGradiPrefixes($flightInfo["bruType"]);
			
			for($i = 0; $i < count($prefixes); $i++)
			{
				$exportedTable = $C->ExportTable($flightInfo["apTableName"]."_".$prefixes[$i], 
						$flightInfo["apTableName"]."_".$prefixes[$i] . "_" . $username);
								
				$exportedFiles[] = $exportedTable;
				
				$flightInfo["apTables"][] = array(
						"pref" => $prefixes[$i],
						"file" => $exportedTable["filename"]); 
			}
			
			$exportedTable = $C->ExportTable($flightInfo["bpTableName"],
					$flightInfo["bpTableName"] . "_" . $username);
			$exportedFiles[] = $exportedTable;
			
			$flightInfo["bpTables"] = $exportedTable["filename"];
			
			if($flightInfo["exTableName"] != "")
			{
				$exportedTable = $C->ExportTable($flightInfo["exTableName"],
						$flightInfo["exTableName"] . "_" . $username);
				$exportedFiles[] = $exportedTable;
				
				$flightInfo["exTables"] = $exportedTable["filename"];
			}
			
			unset($C);	

			$exportedFileDesc = fopen($headerFile['root'], "w");
			fwrite ($exportedFileDesc , json_encode($flightInfo));
			fclose($exportedFileDesc);
			
			$zip = new ZipArchive;
			if ($zip->open($exportedFileRoot . '.zip', ZipArchive::CREATE) === TRUE) 
			{
				for($i = 0; $i < count($exportedFiles); $i++)
				{
					$zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
				}
				$zip->close();
			} 
			else 
			{
				error_log('Failed zipping flight. Page asyncFileProcessor.php"');
			}
			
			for($i = 0; $i < count($exportedFiles); $i++)
			{
				unlink($exportedFiles[$i]['root']);
			}
			
			$zipURL = 'http';
			if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on"))
			{
				$zipURL .= "s";
			}
			$zipURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$zipURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
			} 
			else 
			{
				$zipURL .= $_SERVER["SERVER_NAME"];
			}
			$zipURL .= "/uploadedFiles/" . $exportedFileName . '.zip';
			
			echo json_encode($zipURL);
		}
		else if($action == FLIGHT_IMPORT)
		{
			if(isset($_POST['importedFileUrl']) && ($_POST['importedFileUrl'] != NULL))
			{
				$importedFileUrl = $_POST['importedFileUrl'];
			}
			$importedFileUrl = urldecode($importedFileUrl);
			$parsedUrl = parse_url($importedFileUrl);		
			$importedFilePath = $_SERVER["DOCUMENT_ROOT"] . $parsedUrl["path"];
			$importedFilePathExpl = explode("/", $importedFilePath);
			$importedFileName = $importedFilePathExpl[count($importedFilePathExpl) - 1];
			
			$copiedFilesDir = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/";
			
			$timeY = date("Y-m-d");
			$timeH = date("H-i-s");
			$copiedFilePath = $copiedFilesDir.$timeY."_".$timeH."_".$importedFileName;
			
			$copyRes = copy($importedFilePath, $copiedFilePath);
			if($copyRes)
			{
				unlink($importedFilePath);
				
				$zip = new ZipArchive;
				$res = $zip->open($copiedFilePath);
				if ($res === TRUE) {				
					$i = 0;
					do 
					{
						$name = $zip->getNameIndex($i);
						
						$i++;
					} while((strpos($name, "header") > 0) && ($i < $zip->numFiles));
					
					$zip->extractTo($copiedFilesDir, $name);
					
					$json = file_get_contents($copiedFilesDir."/".$name);
					unlink($copiedFilesDir."/".$name);
					$flightInfoImported = json_decode($json, true);
					
					$bruType = $flightInfoImported['bruType'];
					
					$Fl = new Flight();
					$flightId = $Fl->InsertNewFlight($flightInfoImported['bort'], $flightInfoImported['voyage'], 
						$flightInfoImported['startCopyTime'],
						$flightInfoImported['bruType'], $flightInfoImported['performer'],
						$flightInfoImported['departureAirport'], $flightInfoImported['arrivalAirport'],
						$importedFileName, $flightInfoImported['flightAditionalInfo']);
					
					$flightInfo = $Fl->GetFlightInfo($flightId);
					
					$tableNameAp = $flightInfo['apTableName'];
					$tableNameBp = $flightInfo['bpTableName'];
					
					$Bru = new Bru();
					$bruInfo = $Bru->GetBruInfo($bruType);
					$gradiApByPrefixes = $Bru->GetBruApGradiPrefixOrganized($bruType);
					$prefixFreqArr = $Bru->GetBruApGradiPrefixFreq($bruType);
					$gradiBp = $Bru->GetBruBpGradi($bruType);
					unset($Bru);
					$apTables = $Fl->CreateFlightParamTables($flightId, $gradiApByPrefixes);			
					
					$apTables = $flightInfoImported["apTables"];
					
					$Fr = new Frame();
					for($j = 0; $j < count($apTables); $j++)
					{
						$zip->extractTo($copiedFilesDir, $apTables[$j]["file"]);
						$Fr->LoadFileToTable($tableNameAp . "_" . $apTables[$j]["pref"], 
								$copiedFilesDir."/".$apTables[$j]["file"]);
						unlink($copiedFilesDir."/".$apTables[$j]["file"]);
					}
					
					$bpTables = $flightInfoImported["bpTables"];
					$zip->extractTo($copiedFilesDir, $bpTables);
					$Fr->LoadFileToTable($tableNameBp, $copiedFilesDir."/".$bpTables);
					unlink($copiedFilesDir."/".$bpTables);
					
					$FlE = new FlightException();
					if(isset($flightInfoImported["exTableName"]) && 
						$flightInfoImported["exTableName"] != "")
					{
						$tableGuid = substr($tableNameAp, 0, 14);
						$FlE->CreateFlightExceptionTable($flightId, $tableGuid);
						$flightInfo = $Fl->GetFlightInfo($flightId);
						
						$exTables = $flightInfoImported["exTables"];
						$zip->extractTo($copiedFilesDir, $exTables);
						$Fr->LoadFileToTable($flightInfo["exTableName"], $copiedFilesDir."/".$exTables);
						unlink($copiedFilesDir."/".$exTables);
					}
					unset($Fl);
					unset($FlE);
					unset($Fr);
					
					$zip->close();
					unset($zip);
					
					$Usr = new User();
					$Usr->SetFlightAvaliable($username, $flightId);
					unset($Usr);
					
					unlink($copiedFilePath);
					
					echo json_encode('ok');
					
				} else {
					error_log("Unziping imported file failed. Page asyncFileProcessor.php");
				}
			}
			else 
			{
				error_log("Copy imported file failed. Page asyncFileProcessor.php");
			}		
		}
	}
	else
	{
		//log
		echo("Action not set. Page asyncFileProcessor.php");
		//echo("<script>location.href=location.protocol + '//' + location.host + '/fileUploader.php'</script>");
  		exit();	
	}
}
else
{
	echo("Authorization error. Page asyncFileProcessor.php");
}
		
?>
