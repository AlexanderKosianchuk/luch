<?

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{
	if(isset($_POST['action']))
	{
		$action = $_POST['action'];
		if(($action == UPLOADER_PREVIEW) && isset($_POST['fileName']) && isset($_POST['bruType']))
		{
	
			$fileName = $_POST['fileName'];
			$bruType = $_POST['bruType'];
		
			$file = $fileName;
			
			$flightInfo['bruType'] = $bruType;
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$frameLength = $bruInfo['frameLength'];
			$stepLength = $bruInfo['stepLength'];
			$wordLength = $bruInfo['wordLength'];
			$headerLength = $bruInfo['headerLength'];
			$headerScr = $bruInfo['headerScr'];
			$frameSyncroCode = $bruInfo['frameSyncroCode'];
	
			$previewParams = $bruInfo['previewParams'];
			$gradiAp = $Bru->GetBruApCyclo($bruType, -1, -1, -1);
			
			$previewParams = explode(";", $previewParams);
			$previewParams = array_map('trim', $previewParams);
			
			$previewGradi = array();
			$gradiApByPrefixes = array();
			
			foreach ($gradiAp as $row => $val)
			{
				if(in_array($val['code'], $previewParams))
				{
					$previewGradi[] = $val;
					if(!in_array($val['prefix'], $gradiApByPrefixes))
					{
						$prefixFreqArr[$val['prefix']] = count(explode(",",$val['channel']));
					}
					
					$gradiApByPrefixes[$val['prefix']][] = $val;
				}
			}
			
			$prefixFreqArr = $Bru->GetBruApGradiPrefixFreq($bruType);
			unset($Bru);
			
			$Fr = new Frame();
			$fileDesc = $Fr->OpenFile($fileName);
			$fileSize = $Fr->GetFileSize($fileName);
			unset($Fr);
			
			if(($headerScr != '') || ($headerScr != null))
			{
				eval ($headerScr);
			}
			
			$startCopyTime = 0; // to be 0 hours
			/*if(isset($flightInfo['startCopyTime']))
			{
				$startCopyTime = $flightInfo['startCopyTime'] * 1000;
			}*/
			
			$Fr = new Frame();
			$syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $headerLength, $fileName);
			
			$fileDesc = $Fr->OpenFile($fileName);
			$fileSize = $Fr->GetFileSize($fileName);	
	
			$frameNum = 0;
			$totalFrameNum = floor(($fileSize - $headerLength - $syncroWordOffset)  / $frameLength);
			
			fseek($fileDesc, $syncroWordOffset, SEEK_SET);
			$curOffset = $syncroWordOffset;
			
			$algHeap = array();
			$data = array();
			
			while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
			//while(($frameNum < 30) && ($curOffset < $fileSize))
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
						
						$phisicsFrame = $phisicsFrame[0]; // 0 - ap 1 - bp
	
						for($i = 0; $i < count($gradiAp); $i++)
						{
							$data[$gradiAp[$i]['code']][] = array($phisicsFrame[1], $phisicsFrame[$i + 2]); //+2 because 0 - frameNum, 1 - time
						}
					}
					
					$frameNum++;
				}
				else
				{
					$syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $curOffset, $fileName);
					
					fseek($fileDesc, $syncroWordOffset, SEEK_SET);

					$framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
					$totalFrameNum = $frameNum + $framesLeft;
					
				}
			}
			
			$Fr->CloseFile($fileDesc);
			unset($Fr);
			echo(json_encode($data));
		}
		else if(($action == UPLOADER_SLICE) && isset($_POST['fileName']) && isset($_POST['bruType']) && 
				isset($_POST['startCopyTime']) && isset($_POST['endCopyTime']) && 
				isset($_POST['startSliceTime']) && isset($_POST['endSliceTime']))
		{
			$fileName = $_POST['fileName'];
			$bruType = $_POST['bruType'];
			$startCopyTime = $_POST['startCopyTime'];
			$endCopyTime = $_POST['endCopyTime'];
			$startSliceTime = $_POST['startSliceTime'];
			$endSliceTime = $_POST['endSliceTime'];	
			
			$newFileName = $fileName;
			$index = 'x';
			
			do 
			{
				$index++;
			}
			while(file_exists($newFileName . $index));
			$newFileName = $newFileName . $index;
			
			$Bru = new Bru();
			$bruInfo = $Bru->GetBruInfo($bruType);
			$headerLength = $bruInfo['headerLength'];
			$frameLength = $bruInfo['frameLength'];
			
			$handle = fopen($fileName, "r");
			$newHandle = fopen($newFileName, "w");
			
			if($headerLength > 0)
			{
				$fileHeader = fread($handle, $headerLength);
				fwrite($newHandle, $fileHeader);
			}
			
			//$writtenHeaderLength = file_put_contents($newFileName, $fileHeader);
			
			$fileSize = filesize ($fileName);
			$Bs = ($fileSize - $headerLength) / ($endCopyTime - $startCopyTime);			
			$stB = $Bs * ($startSliceTime - $startCopyTime) + $headerLength;
			$endB = $Bs * ($endSliceTime - $startCopyTime) + $headerLength;
			
			$stB = round($stB / $frameLength , 0) * $frameLength + $headerLength;
			
			if($endB > $fileSize)
			{
				$endB = $fileSize;
			}
			
			if($stB > 0 && $stB < $fileSize && $endB > 0 && $endB <= $fileSize)
			{
				fseek($handle, $stB);
				while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB)
				{
					$fileFrame = fread($handle, $frameLength);
					fwrite($newHandle, $fileFrame);
				}
				fclose($handle);
				fclose($newHandle);
				unlink($fileName);
			}
			else 
			{
				error_log("Invalid slice range. Page asyncCopyPreview.php");
				echo('err');
				exit();
			}

			echo json_encode($newFileName);
		}
		else
		{
			error_log("File priview params not set. Page asyncCopyPreview.php");
			echo("File priview params not set. Page asyncCopyPreview.php");
		}
		
	}
	else
	{
		error_log("Action not set. Page asyncCopyPreview.php");
		echo("Action not set. Page asyncCopyPreview.php");
	}
}
else
{
	error_log("Authorization error. Page asyncCopyPreview.php");
	echo("Authorization error. Page asyncCopyPreview.php");
}
?>