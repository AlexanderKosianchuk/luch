<?php

require_once("includes.php");

class Frame
{
	public function MoveUploadingFile($extFileName, $extFilePath)
	{
		$fileName = $extFileName;
		$filePath = $extFilePath;

		$uploadedFilesDir = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/";

		$timeY = date("Y-m-d");
		$timeH = date("H-i-s");
		$uploadedFile = $uploadedFilesDir.$timeY."_".$timeH."_".$fileName;
		if($filePath != NULL)
		{
			$moveUploadedFile = move_uploaded_file(@$filePath, $uploadedFile);
		}
		else
		{
			//log
			error_log("filePath - " . $filePath. " No file assignment during file uploading. Frame.php");
		}

		return $uploadedFile;
	}

	public function OpenFile($extUploadedFile)
	{
		$uploadedFile = $extUploadedFile;
		$fileDesc = fopen($uploadedFile, 'rb');
		if(!$fileDesc)
		{
			//log
			echo("File openning error");
		}

		return $fileDesc;
	}

	public function GetFileSize($extFileDesc)
	{
		$fileDesc = $extFileDesc;
		return filesize($fileDesc);

	}

	public function CloseFile($extFileDesc)
	{
		$fileDesc = $extFileDesc;
		fclose($fileDesc);
	}

	public function ReadHeader($extFileDesc, $extHeaderLength)
	{
		$fileDesc = $extFileDesc;
		$headerLength = $extHeaderLength;
		$header = fread($fileDesc, $headerLength);
		return $header;
	}

	public function ReadFrame($extFileDesc, $extFrameLength)
	{
		$fileDesc = $extFileDesc;
		$frameLength = $extFrameLength;
		$frame = fread($fileDesc, $frameLength);
		return $frame;
	}

	public function ConvertFrameToPhisics($extSplitedFrame, $extStartTime, $extStepLength, $extChannelFreq, $extFrameNum, $extGradiAp, & $algHeap)
	{
		$frame = $extSplitedFrame;
		$startTime = $extStartTime;
		$stepLength = $extStepLength;
		$channelFreq = $extChannelFreq;
		$frameNum = $extFrameNum;
		$gradiAp = $extGradiAp;
		//$algHeap = $extAlgHeap;
		
		$phisicsFrame = array();
				
		for($ind1 = 0; $ind1 < count($gradiAp); $ind1++)
		{
			$gradiParam = $gradiAp[$ind1];
			$channels = explode(",", $gradiParam["channel"]);
			$channels = array_map('trim', $channels);
			
			//$channels = array_filter($channels);
			$interview = array();
			for($ind2 = 0; $ind2 < count($channels); $ind2++)
			{
				$codeValue = $frame[$channels[$ind2]];
				$gradiParamType = $gradiParam['type'];
				
				/*if(strpos($gradiParamType, "r") !== false)
				{
					$gradiParamType = str_replace("r", "", $gradiParamType);
					
					$rotatedArr = str_split($codeValue, 2);
					for($rotI = 0; $rotI < count($rotatedArr) / 2; $rotI++)
					{
						$tmp = $rotatedArr[$rotI];
						$rotatedArr[$rotI] = $rotatedArr[count($rotatedArr) - 1 - $rotI];
						$rotatedArr[count($rotatedArr) - 1 - $rotI] = $tmp;
					}
					$apCode = hexdec(implode($rotatedArr));
				}
				
				if(strpos($gradiParamType, "i") !== false)
				{
					$gradiParamType = str_replace("i", "", $gradiParamType);
					$apCode = ~hexdec($apCode);
				}*/
				
				//get phisics analog param from code
				if($gradiParam['type'] == 1)//type 1 uses for graduated params
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> $gradiParam['shift'];
					$gradi = $gradiParam['xy'];

					for($j = 0; $j < count($gradi); $j++)
					{
						if($apCode <= $gradi[$j]['y'])
						{
							break;
						}
					}

					//faling extrapolation
					if($j == 0)
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[0];
							$p1 = $gradi[1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}
					//rising extrapolation
					else if($j >= (count($gradi) - 1))
					{
						//exact match
						if($apCode == $gradi[count($gradi) - 1]['y'])
						{
							$phisics = $gradi[count($gradi) - 1]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[count($gradi) - 2];
							$p1 = $gradi[count($gradi) - 1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}

					}
					//interpolation
					else
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[$j - 1];
							$p1 = $gradi[$j];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 2)//calc param
				{
					//$algHeap to store global temp values
					//$Ch = new Cache();
					//$algHeap = unserialize($Ch->retrieve('algHeap'));

					$alg = $gradiParam['alg'];
					$alg = str_replace("[p]", "'" . $codeValue . "'", $alg);
					$alg = str_replace("[k]", $gradiParam['k'], $alg);
					$alg = str_replace("[mask]", $gradiParam['mask'], $alg);
					$alg = str_replace("[shift]", $gradiParam['shift'], $alg);
					$alg = str_replace("[minus]", $gradiParam['minus'], $alg);

					eval($alg);//$phisics must be assigned in alg

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 3)//left bit as sign
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> ($gradiParam['shift']);
					$minus = (hexdec($codeValue) & $gradiParam['minus']);
					if($minus > 0)
					{
						$apCode = $apCode * -1;
					}

					$phisics = $apCode * $gradiParam['k'];

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 4)//unsigned params with coef
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> $gradiParam['shift'];
					$phisics = $apCode * $gradiParam['k'];

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 5)//signed params with coef
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> ($gradiParam['shift'] + 1);
					$minus = (hexdec($codeValue) & $gradiParam['mask']) >> ($gradiParam['shift']);
					if($minus > $gradiParam['mask'] / 2)
					{
						$apCode = $apCode - $gradiParam['mask'];
					}
					$phisics = $apCode * $gradiParam['k'];

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 6)//unsigned params with coef with rotation bytes in word
				{
					$tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
					$tempStr2 = substr ($codeValue, 2, 2);
					$rotatedStr = $tempStr2 . $tempStr1;

					$apCode = (hexdec($rotatedStr) & $gradiParam['mask']) >> $gradiParam['shift'];
					$phisics = $apCode * $gradiParam['k'];

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 7)//using field minus to find negative values with rotation bytes in word
				{
					$tempStr1 = substr ($codeValue, 0, 2);
					$tempStr2 = substr ($codeValue, 2, 2);
					$rotatedStr = $tempStr2 . $tempStr1;
					$apCode = (hexdec($rotatedStr) & $gradiParam['mask']) >> $gradiParam['shift'];
					if($apCode >= $gradiParam['minus'])
					{
						$apCode -= $gradiParam['minus'] * 2;
					}
					$phisics = $apCode * $gradiParam['k'];
					
					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 8)//signed params with coef with negative values
				{
					$apCode = (hexdec($codeValue));
					if($apCode > 32768)
					{
						$apCode -= 65535;
					}
					$phisics = $apCode * $gradiParam['k'];

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 9)//signed params with coef with gradual rotation
				{
					$apCode = (hexdec($codeValue));
					if($apCode > 32768)
					{
						$apCode -= 65535;
					}
					$phisics = $apCode * $gradiParam['k'];
					if($phisics < 0)
					{
						$phisics += 360;
					}

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 10)//using field minus to find negative values
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> $gradiParam['shift'];
					if($apCode >= $gradiParam['minus'])
					{
						$apCode -= $gradiParam['minus'] * 2;
					}
					$phisics = $apCode * $gradiParam['k'];
					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 21)// graduated with invertion
				{
					$apCode = (hexdec($codeValue) & $gradiParam['mask']) >> $gradiParam['shift'];
					$apCode = 255 - $apCode;
					$gradi = $gradiParam['xy'];

					for($j = 0; $j < count($gradi); $j++)
					{
						if($apCode <= $gradi[$j]['y'])
						{
							break;
						}
					}

					//faling extrapolation
					if($j == 0)
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[0];
							$p1 = $gradi[1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}
					//rising extrapolation
					else if($j >= (count($gradi) - 1))
					{
						//exact match
						if($apCode == $gradi[count($gradi) - 1]['y'])
						{
							$phisics = $gradi[count($gradi) - 1]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[count($gradi) - 2];
							$p1 = $gradi[count($gradi) - 1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}

					}
					//interpolation
					else
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[$j - 1];
							$p1 = $gradi[$j];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}

					array_push($interview, $phisics);
				}
				else if($gradiParam['type'] == 22)// graduated with rotation
				{
					$tempStr1 = substr ($codeValue, 0, 2);
					$tempStr2 = substr ($codeValue, 2, 2);
					$rotatedStr = $tempStr2 . $tempStr1;
					$apCode = (hexdec($rotatedStr) & $gradiParam['mask']) >> $gradiParam['shift'];
					$gradi = $gradiParam['xy'];

					for($j = 0; $j < count($gradi); $j++)
					{
						if($apCode <= $gradi[$j]['y'])
						{
							break;
						}
					}

					//faling extrapolation
					if($j == 0)
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[0];
							$p1 = $gradi[1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}
					//rising extrapolation
					else if($j >= (count($gradi) - 1))
					{
						//exact match
						if($apCode == $gradi[count($gradi) - 1]['y'])
						{
							$phisics = $gradi[count($gradi) - 1]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[count($gradi) - 2];
							$p1 = $gradi[count($gradi) - 1];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}

					}
					//interpolation
					else
					{
						//exact match
						if($apCode == $gradi[$j]['y'])
						{
							$phisics = $gradi[$j]['x'];
						}
						else
						{
							$p = $apCode;
							$p0 = $gradi[$j - 1];
							$p1 = $gradi[$j];

							if($p1['y'] - $p0['y'] == 0)
							{
								$phisics = 0;
							}
							else
							{
								$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
										($p - $p0['y'])) / ($p1['y'] - $p0['y']);
							}
						}
					}

					array_push($interview, $phisics);
				}
			}
			array_push($phisicsFrame, $interview);
		}
		

		$phisicsFrame = $this->RotatePhisicsFrame($phisicsFrame, $startTime, $stepLength, $channelFreq, $frameNum);
		return $phisicsFrame;
	}
	
	private function RotatePhisicsFrame($extPhisicsFrame, $extStartTime, $extStepLength, $extChannelFreq, $extFrameNum)
	{
		$phisicsFrame = $extPhisicsFrame;
		$startTime = $extStartTime;
		$stepLength = $extStepLength;
		$channelFreq = $extChannelFreq;
		$frameNum = $extFrameNum;
		
		$phisicsFrameCopy = $phisicsFrame;
		$phisicsFrame = array();
		
		for($i = 0; $i < $channelFreq; $i++)
		{
			$line = array();
			array_push($line, $frameNum);
			array_push($line, ($startTime + ($frameNum * $stepLength) + ($stepLength / $channelFreq * $i)) * 1000);
			
			for($j = 0; $j < count($phisicsFrameCopy); $j++)
			{
				array_push($line, $phisicsFrameCopy[$j][$i]);
			}
			array_push($phisicsFrame, $line);
		}
		
		return $phisicsFrame;	
	}

	public function ConvertFrameToBinaryParams($extSplitedFrame, $extFrameNum, $extGradiBp)
	{
		$frame = $extSplitedFrame; 
		$frameNum = $extFrameNum; 
		$gradiBp = $extGradiBp;
		
		$phisicsBinaryParamsFrame = array();
		
		//get binary param from this code
		foreach($gradiBp as $binParam)
		{
			$codeValue = $frame[$binParam["channel"]];
			
			if($binParam['type'] == 1)
			{
				$bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
				if($bpCode > 0)
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'],
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			else if($binParam['type'] == 2)//rotation bytes in word
			{
				$tempStr1 = substr ($codeValue, 0, 2);
				$tempStr2 = substr ($codeValue, 2, 2);
				$rotatedStr = $tempStr2 . $tempStr1;
				$bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
				if($bpCode > 0)
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'],
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			//virtual bp
			else if($binParam['type'] == 3)
			{
				$bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
				if(!($bpCode > 0))
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'] + 5000,
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			//virtual bp
			else if($binParam['type'] == 4)//rotation bytes in word
			{
				$tempStr1 = substr ($codeValue, 0, 2);
				$tempStr2 = substr ($codeValue, 2, 2);
				$rotatedStr = $tempStr2 . $tempStr1;
				$bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
				if(!($bpCode > 0))
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'] + 5000,
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			else if($binParam['type'] == 5)
			{
				$bpCode = (hexdec($codeValue) & $binParam['basis']);//decbin
				if($bpCode == $binParam['mask'])
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'],
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			else if($binParam['type'] == 6)
			{
				$bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
				if(!($bpCode > 0))
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'],
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
			else if($binParam['type'] == 7) 
			{
				$bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin ( virtual type  6)
				if(($bpCode > 0))
				{
					$param = array("frameNum" => $frameNum,
							"channel" => $binParam['channel'] + 5000,
							"mask" => $binParam['mask']);
					array_push($phisicsBinaryParamsFrame, $param);
				}
			}
		}
		
		return $phisicsBinaryParamsFrame;
	}

	/*public function ConvertFrameToPhisict($extFrame, $extFrameLength, $extFrameNum, $gradiAp, $gradiBp, $extWordLength, $algHeap)
	 {
	$wordLength = $extWordLength;

	$unpackedFrame = unpack("H*", $extFrame);
	$splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need
	$frameNum = $extFrameNum;
	$frameLength = $extFrameLength;
	$phisicsAnalogParamsFrame = array();
	$phisicsBinaryParamsFrame = array();
	$phisicsFrame = array();

	$paramCounter = 0;
	$gradiParam = $gradiAp[$paramCounter];

	$binCounter = 0;
	$binParam = $gradiBp[$binCounter];

	$i = 0;

	while($i < $frameLength / $wordLength)
	{
	if($gradiParam['channel'] == $i)
	{
	//get phisics analog param from code
	if($gradiParam['type'] == 1)//type 1 uses for graduated params
	{
	$v = $gradiParam;
	$apCode = (hexdec($splitedFrame[$i]) & $gradiParam['mask']) >> $gradiParam['shift'];
	$gradi = $v['xy'];

	for($j = 0; $j < count($gradi); $j++)
	{
	if($apCode <= $gradi[$j]['y'])
	{
	break;
	}
	}

	//faling extrapolation
	if($j == 0)
	{
	//exact match
	if($apCode == $gradi[$j]['y'])
	{
	$phisics = $gradi[$j]['x'];
	}
	else
	{
	$p = $apCode;
	$p0 = $gradi[0];
	$p1 = $gradi[1];

	if($p1['y'] - $p0['y'] == 0)
	{
	$phisics = 0;
	}
	else
	{
	$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
			($p - $p0['y'])) / ($p1['y'] - $p0['y']);
	}
	}
	}
	//rising extrapolation
	else if($j >= (count($gradi) - 1))
	{
	//exact match
	if($apCode == $gradi[count($gradi) - 1]['y'])
	{
	$phisics = $gradi[count($gradi) - 1]['x'];
	}
	else
	{
	$p = $apCode;
	$p0 = $gradi[count($gradi) - 2];
	$p1 = $gradi[count($gradi) - 1];

	if($p1['y'] - $p0['y'] == 0)
	{
	$phisics = 0;
	}
	else
	{
	$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
			($p - $p0['y'])) / ($p1['y'] - $p0['y']);
	}
	}

	}
	//interpolation
	else
	{
	//exact match
	if($apCode == $gradi[$j]['y'])
	{
	$phisics = $gradi[$j]['x'];
	}
	else
	{
	$p = $apCode;
	$p0 = $gradi[$j - 1];
	$p1 = $gradi[$j];

	if($p1['y'] - $p0['y'] == 0)
	{
	$phisics = 0;
	}
	else
	{
	$phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
			($p - $p0['y'])) / ($p1['y'] - $p0['y']);
	}
	}
	}

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 2)//unsigned params with coef
	{
	//$algHeap to store global temp values
	//$Ch = new Cache();
	//$algHeap = unserialize($Ch->retrieve('algHeap'));

	$alg = $gradiParam['alg'];
	$alg = str_replace("[p]", "'" . $splitedFrame[$i] . "'", $alg);
	$alg = str_replace("[k]", $gradiParam['k'], $alg);
	$alg = str_replace("[mask]", $gradiParam['mask'], $alg);
	$alg = str_replace("[shift]", $gradiParam['shift'], $alg);
	$alg = str_replace("[minus]", $gradiParam['minus'], $alg);

	eval($alg);//$phisics must be assigned in alg

	//$Ch->store('algHeap', serialize($algHeap), $expiration = 1);
	//unset($Ch);

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	//3 not used yet
	else if($gradiParam['type'] == 4)//unsigned params with coef
	{
	$apCode = (hexdec($splitedFrame[$i]) & $gradiParam['mask']) >> $gradiParam['shift'];
	$phisics = $apCode * $gradiParam['k'];

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 5)//signed params with coef
	{
	$apCode = (hexdec($splitedFrame[$i]) & $gradiParam['mask']) >> ($gradiParam['shift'] + 1);
	$minus = (hexdec($splitedFrame[$i]) & $gradiParam['mask']) >> ($gradiParam['shift']);
	if($minus > $gradiParam['mask'] / 2)
	{
	$apCode = $apCode - $gradiParam['mask'];
	}
	$phisics = $apCode * $gradiParam['k'];

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 6)//unsigned params with coef with rotation bytes in word
	{
	$tempStr1 = substr ($splitedFrame[$i], 0, 2);//because 2 hex  digits in byte
	$tempStr2 = substr ($splitedFrame[$i], 2, 2);
	$rotatedStr = $tempStr2 . $tempStr1;

	$apCode = (hexdec($rotatedStr) & $gradiParam['mask']) >> $gradiParam['shift'];
	$phisics = $apCode * $gradiParam['k'];

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 7)//using field minus to find negative values with rotation bytes in word
	{
	$tempStr1 = substr ($splitedFrame[$i], 0, 2);
	$tempStr2 = substr ($splitedFrame[$i], 2, 2);
	$rotatedStr = $tempStr2 . $tempStr1;
	$apCode = (hexdec($rotatedStr) & $gradiParam['mask']) >> $gradiParam['shift'];
	if($apCode >= $gradiParam['minus'])
	{
	$apCode -= $gradiParam['minus'] * 2;
	}
	$phisics = $apCode * $gradiParam['k'];
	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 8)//signed params with coef with negative values
	{
	$apCode = (hexdec($splitedFrame[$i]));
	if($apCode > 32768)
	{
	$apCode -= 65535;
	}
	$phisics = $apCode * $gradiParam['k'];

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 9)//signed params with coef with gradual rotation
	{
	$apCode = (hexdec($splitedFrame[$i]));
	if($apCode > 32768)
	{
	$apCode -= 65535;
	}
	$phisics = $apCode * $gradiParam['k'];
	if($phisics < 0)
	{
	$phisics += 360;
	}

	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}
	else if($gradiParam['type'] == 10)//using field minus to find negative values
	{
	$apCode = (hexdec($splitedFrame[$i]) & $gradiParam['mask']) >> $gradiParam['shift'];
	if($apCode >= $gradiParam['minus'])
	{
	$apCode -= $gradiParam['minus'] * 2;
	}
	$phisics = $apCode * $gradiParam['k'];
	$param = array("frameNum" => $frameNum, "channel" => $gradiParam['channel'], "value" => $phisics);
	array_push($phisicsAnalogParamsFrame, $param);
	}

	$paramCounter++;
	if(count($gradiAp) > $paramCounter)
	{
	$gradiParam = $gradiAp[$paramCounter];
	}
	}

	//get binary param from this code
	while($binParam['channel'] == $i)
	{
	if($binParam['type'] == 1)
	{
	$bpCode = (hexdec($splitedFrame[$i]) & $binParam['mask']);//decbin
	if($bpCode > 0)
	{
	$param = array("frameNum" => $frameNum,
			"channel" => $binParam['channel'],
			"mask" => $binParam['mask']);
	array_push($phisicsBinaryParamsFrame, $param);
	}
	}
	else if($binParam['type'] == 2)//rotation bytes in word
	{
	$tempStr1 = substr ($splitedFrame[$i], 0, 2);
	$tempStr2 = substr ($splitedFrame[$i], 2, 2);
	$rotatedStr = $tempStr2 . $tempStr1;
	$bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
	if($bpCode > 0)
	{
	$param = array("frameNum" => $frameNum,
			"channel" => $binParam['channel'],
			"mask" => $binParam['mask']);
	array_push($phisicsBinaryParamsFrame, $param);
	}
	}
	if($binParam['type'] == 3)
	{
	$bpCode = (hexdec($splitedFrame[$i]) & $binParam['mask']);//decbin
	if(!($bpCode > 0))
	{
	$param = array("frameNum" => $frameNum,
			"channel" => $binParam['channel'] + 5000,
			"mask" => $binParam['mask']);
	array_push($phisicsBinaryParamsFrame, $param);
	}
	}
	else if($binParam['type'] == 4)//rotation bytes in word
	{
	$tempStr1 = substr ($splitedFrame[$i], 0, 2);
	$tempStr2 = substr ($splitedFrame[$i], 2, 2);
	$rotatedStr = $tempStr2 . $tempStr1;
	$bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
	if(!($bpCode > 0))
	{
	$param = array("frameNum" => $frameNum,
			"channel" => $binParam['channel'] + 5000,
			"mask" => $binParam['mask']);
	array_push($phisicsBinaryParamsFrame, $param);
	}
	}

	$binCounter++;
	if(count($gradiBp) > $binCounter)
	{
	$binParam = $gradiBp[$binCounter];
	}
	else
	{
	$binParam = false;
	}
	}

	$i++;
	}

	array_push($phisicsFrame, $phisicsAnalogParamsFrame);
	array_push($phisicsFrame, $phisicsBinaryParamsFrame);

	return $phisicsFrame;
	}*/

	public function DeleteFile($extFileName)
	{
		$fileName = $extFileName;
		unlink($fileName);
	}

	public function InsertApFrame($extPhisicsFrame, $extTableName, $prefix, $link)
	{
		$phisicsFrame = $extPhisicsFrame;
		$tableName = $extTableName;

		if($link == null)
		{
			$c = new DataBaseConnector();
			$link = $c->Connect();
		}

		$query = "INSERT INTO `".$tableName."_".$prefix."` VALUES ";
		$i = 0;
		while($i < count($phisicsFrame))
		{
			$query .= "(";
			$line = $phisicsFrame[$i];
			for($j = 0; $j < count($line); $j++)
			{
				$param = $line[$j];
				$query .= $param . ", ";
			}
			$query = substr($query, 0, -2);
			$query .= "), ";
			$i++;
		}

		$query = substr($query, 0, -2);
		$query .=";";
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();

		if($link == null)
		{
			$c->Disconnect();
		}

		unset($c);
	}

	public function InsertBpFrame($extBinFrame, $extTableName, $link)
	{
		$binFrame = $extBinFrame;
		$tableName = $extTableName;

		if($link == null)
		{
			$c = new DataBaseConnector();
			$link = $c->Connect();
		}

		$query = "INSERT INTO `".$tableName."` (frameNum, channel, mask) VALUES ";
		$i = 0;

		while($i < count($binFrame))
		{
			$param = $binFrame[$i];
			$query .="(".$param['frameNum'].", ".$param['channel'].", ".$param['mask']."),";
			$i++;
		}

		$query = substr($query, 0, -1);
		$stmt = $link->prepare($query);
		$stmt->execute();

		$stmt->close();

		if($link == null)
		{
			$c->Disconnect();
		}

		unset($c);
	}

	public function AppendApFrameToFile($extPhisicsFrameByPrefixes, $extFileDescArr)
	{
		$phisicsFramesByPrefixes = $extPhisicsFrameByPrefixes;
		$files = $extFileDescArr;

		foreach($phisicsFramesByPrefixes as $prefix => $phisicsFrames)
		{
			$curFile = $files[$prefix];
			for($i = 0; $i < count($phisicsFrames); $i++)
			{
				$line = $phisicsFrames[$i];
				$lineToWrite = implode(",", $line);
				fwrite($curFile, $lineToWrite. ";");
			}
		}
	}

	public function AppendBpFrameToFile($extBinFrame, $extFileDesc)
	{
		$binFrame = $extBinFrame;
		$file = $extFileDesc;

		$i = 0;

		while($i < count($binFrame))
		{
			$param = $binFrame[$i];
			fwrite($file, $param['frameNum'].",".$param['channel'].",".$param['mask']. ";");
			$i++;
		}
	}

	public function LoadFileToTable($extTableName, $extFileName)
	{
		$tableName = $extTableName;
		$file = $extFileName;

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "LOAD DATA LOCAL INFILE '".$file."' INTO TABLE `".$tableName."` FIELDS TERMINATED BY ',' LINES TERMINATED BY ';';";
		$link->query($query);

		$c->Disconnect();

		unset($c);
	}

	public function GetFramesCount($extApTableName, $extPrefix)
	{
		$apTableName = $extApTableName;
		$prefix = $extPrefix;

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT MAX(`frameNum`) FROM `".$apTableName."_". $prefix ."` LIMIT 1;";
		$result = $link->query($query);

		$row = $result->fetch_array();
		$framesCount = $row[0];

		$result->free();
		$c->Disconnect();

		unset($c);

		return $framesCount;
	}

	public function GetFlightFrame($extFlightId, $extFrameNum, $extCodesArray)
	{
		$flightId = $extFlightId;
		$frameNum = $extFrameNum;
		$codesArray = $extCodesArray;

		$flightInfo = $this->GetFlightInfo($flightId);
		$apTableName = $flightInfo['apTableName'];

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT `channel`, `value` FROM `".$apTableName."` WHERE `frameNum` = ".$frameNum." ORDER BY `channel` ASC;";
		$result = $link->query($query);

		$frame = array();

		while($row = $result->fetch_array())
		{
			$param = array("channel" => $row['channel'],
					"code" => $codesArray[$row['channel']],
					"value" => $row['value']);
			array_push($frame, $param);
		}

		$normFrame = array();

		$maxFreq = 16;

		for($a = 0; $a < count($frame); $a++)
		{
			$curParam = $frame[$a];
			$code = $curParam['code'];
			$channel = $curParam['channel'];
			$param = array("code" => $code, "value" => $curParam['value']);
			$preparedParam = array();
			array_push($preparedParam, $param);
			unset($frame[$a]);
			$frame = array_values($frame);
			$a--;
			for($b = 0; $b < count($frame);)
			{
				$tempParam = $frame[$b];
				if($code == $tempParam['code'])
				{
					$param = array("code" => $code, "value" => $tempParam['value']);
					array_push($preparedParam, $param);
					unset($frame[$b]);
					$frame = array_values($frame);
				}
				else
				{
					$b++;
				}
			}

			for($i = 0; $i < count($preparedParam); $i++)
			{
				for($j = 0; $j < ($maxFreq / count($preparedParam)); $j++)
				{
					array_push($normFrame, $preparedParam[$i]);
				}
			}


		}

		//		for($h = 0; $h < count($normFrame); $h++)
		//		{
		//			echo($normFrame[$h]['code']. " " . $normFrame[$h]['value']. "</br>");
		//		}

		$result->free();
		$c->Disconnect();

		unset($c);

		return $normFrame;
	}

	public function CreateTableNormalizedFrames($extAPheaders)
	{
		$bruType = $extBRUtype;
		$APheaders = $extAPheaders;
		$tableName = uniqid().time()."tmp";

		$c = new DataBaseConnector();

		$query = "CREATE TABLE ".$tableName." (id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY, T VARCHAR(255), ";
		for($i = 0; $i < count($APheaders); $i++)
		{
			$query .= "p".$i." VARCHAR(255),";
		}
		$query = substr($query, 0, -1);
		$query .=");";

		$link = $c->Connect();
		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();

		$query = "INSERT INTO ".$tableName." (T,";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "p".$j.",";
		}

		$query = substr($query, 0, -1);
		$query .=") VALUES ('serv1',";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "'".$APheaders[$j]['code']."',";
		}

		$query = substr($query, 0, -1);
		$query .="), ('serv2',";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "'".$APheaders[$j]['name']."',";
		}

		$query = substr($query, 0, -1);
		$query .="), ('serv3',";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "'".$APheaders[$j]['dim']."',";
		}

		$query = substr($query, 0, -1);
		$query .="), ('serv4',";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "'".$APheaders[$j]['minValue']."',";
		}

		$query = substr($query, 0, -1);
		$query .="), ('serv5',";

		for($j = 0; $j < count($APheaders); $j++)
		{
			$query .= "'".$APheaders[$j]['maxValue']."',";
		}

		$query = substr($query, 0, -1);
		$query .=");";

		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();

		$c->Disconnect();

		unset($c);

		return $tableName;
	}

	public function InsertNormalizedFrame($extFrame, $extTableName, $extAPheaders, $extStepDivider, $extCurrFrameTime)
	{
		$frame = $extFrame;
		$tableName = $extTableName;
		$paramsCount = count($extAPheaders);
		$stepDivider = $extStepDivider;
		$currFrameTime = date_format($extCurrFrameTime, "H:i:s");

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "INSERT INTO ".$tableName." ( T, ";

		for($i = 0; $i < $paramsCount; $i++)
		{
			$query .= "p".$i.",";
		}
		$query = substr($query, 0, -1);
		$query .=") VALUES ";

		$k = 0;

		for($i = 0; $i < $stepDivider; $i++)
		{
			$query .= "('".$currFrameTime.".".round(100 / $stepDivider * $i, 0)."', ";

			for($j = $i; $j < count($frame); $j+=$stepDivider)
			{
				$param = $frame[$j];
				$query .= round($param['value'],2).",";
			}

			$query = substr($query, 0, -1);
			$query .="),";
		}

		$query = substr($query, 0, -1);
		$query .=";";

		$stmt = $link->prepare($query);
		$stmt->execute();
		$stmt->close();

		$c->Disconnect();

		unset($c);
	}

	public function GetNormalizedFrame($extTableName, $extId)
	{
		$tableName = $extTableName;
		$id = $extId;

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT * FROM ".$tableName." WHERE id = ".$id.";";

		$result = $link->query($query);

		$normFrame = array();
		while($row = $result->fetch_array())
		{
			for($i = 0; $i < count($row) / 2; $i++)
			{
				array_push($normFrame, $row[$i]);
			}
		}

		$c->Disconnect();

		unset($c);

		return $normFrame;
	}

	public function ShowFlightFrame($extFrame, $extStepDivider, $extCurrFrameTime)
	{
		$frame = $extFrame;
		$stepDivider = $extStepDivider;

		$currFrameTime = $extCurrFrameTime;
		$dateString = date_format($currFrameTime, "H:i:s");

		$cellCount = count($frame) / $stepDivider;
		$tableWidth = $cellCount * 180 + 180;

		printf("<table border=\"1\" width=\"%s px\">", $tableWidth);
		for($i = 0; $i < $stepDivider; $i++)
		{
			printf("<tr><td class=\"VievTableCell\" style=\"text-align:center; \">%s.%s</td>",
					$dateString, round(100 / $stepDivider * $i, 0));

			for($j = $i; $j < count($frame); $j+=$stepDivider)
			{
				//echo($j."</br>");
				printf("<td class=\"VievTableCell\">%s</td>", round($frame[$j]['value'],3));
			}

			printf("</tr>");
		}

		printf("</table>");
	}

	public function GetFlightNormalizedFrame($extFlightId, $extCurrFrameId)
	{
		$flightId = $extFlightId;
		$currFrameId = $extCurrFrameId;

		$d = new DataTransitionProvider();
		$flightInfo = $d->GetFlightInfo($flightId);
		$startCopyTime = $flightInfo['startCopyTime'];
		$bruType = $flightInfo['bruType'];
		$bruInfo = $d->GetBRUinfo($bruType);
		$stepLength = $bruInfo['stepLength'];
		$stepDivider = $bruInfo['stepDivider'];
		$codesArray = $d->GetCodesArray($bruType);

		$frame = $d->GetFlightFrame($flightId, $currFrameId, $codesArray);

		unset($d);

		return $frame;
	}

	/*public function ShowTimeRangeSlider($extStartCopyTime, $extFrameCount, $extStepLength)
	{
		$startCopyTime = $extStartCopyTime;
		$frameCount = $extFrameCount;
		$stepLength = $extStepLength;

		//jQ script will provide range slider
		printf("
				<input id=\"startCopyTime\" type=\"hidden\" value=\"%s\" />
				<input id=\"frameCount\" type=\"hidden\" value=\"%s\" />
				<input id=\"stepLength\" type=\"hidden\" value=\"%s\" />
				<p>
				<label for=\"amount\">Price range:</label>
				<input type=\"text\" id=\"amount\" style=\"border: 0; color: #f6931f; font-weight: bold;\" />
				</p>

				<div id=\"slider-range\"></div>
				<input id=\"startFrame\" type=\"hidden\" value=\"0\" />
				<input id=\"endFrame\" type=\"hidden\" value=\"%s\" />", $startCopyTime, $frameCount, $stepLength, $frameCount);
	}*/
	
	public function SearchSyncroWord($extFrameSyncroCode, $extOffset, $extFileName)
	{
		$frameSyncroCode = $extFrameSyncroCode;
		$offset = $extOffset;
		$fileName = $extFileName;
		
		$fileDesc = $this->OpenFile($fileName);
		$fileSize = $this->GetFileSize($fileName) - $offset;
		fseek($fileDesc, $offset, SEEK_SET);
		
		$syncroWordSeek = $offset;
		$frameSyncroCode = strtolower($frameSyncroCode);
		if($frameSyncroCode != '')
		{
			if(substr($frameSyncroCode, -1) == '*')
			{
				$updatedSyncroCode = substr($frameSyncroCode, 0, -1);
				$syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte
				
				$word = fread($fileDesc, $syncroCodeLength);
				$word = unpack("H*", $word);
				$preparedWord = $word[1];
				
				$syncroWordSeek += $syncroCodeLength;
				do
				{
					$byte = unpack("H*", fread($fileDesc, 1));
					$byte = $byte[1];
					$preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
					$syncroWordSeek++;
		
					$proccesedSyncroCode = $updatedSyncroCode;
					$proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
					$proccessedPreparedWordArr = str_split($preparedWord, 1);

					while(in_array('x', $proccesedSyncroCodeArr))
					{
						$xPos = array_search('x', $proccesedSyncroCodeArr);
						$proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
					}
					
					$proccesedSyncroCode = implode($proccesedSyncroCodeArr);
				}
				while(($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));
			}
			else
			{
				$updatedSyncroCode = $frameSyncroCode;
				$syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte
				
				$word = fread($fileDesc, $syncroCodeLength);
				$word = unpack("H*", $word);
				$preparedWord = $word[1];
				
				$syncroWordSeek += $syncroCodeLength;
				do
				{
					$byte = unpack("H*", fread($fileDesc, 1));
					$byte = $byte[1];
					$preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
					$syncroWordSeek++;
		
					$proccesedSyncroCode = $updatedSyncroCode;
					$proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
					$proccessedPreparedWordArr = str_split($preparedWord, 1);

					while(in_array('x', $proccesedSyncroCodeArr))
					{
						$xPos = array_search('x', $proccesedSyncroCodeArr);
						$proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
					}
					
					$proccesedSyncroCode = implode($proccesedSyncroCodeArr);
				}
				while(($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));
				
				$syncroWordSeek -= $syncroCodeLength;
			}
		}
		
		$this->CloseFile($fileDesc);
		
		return $syncroWordSeek;
	}
	
	public function CheckSyncroWord($extFrameSyncroCode, $extUnpackedFrame)
	{
		$frameSyncroCode = $extFrameSyncroCode;
		$unpackedFrame = $extUnpackedFrame;
	
		$syncroWordFound = false;
		if($frameSyncroCode != '')
		{
			$frameSyncroCode = strtolower($frameSyncroCode);
			
			if(substr($frameSyncroCode, -1) == '*')
			{
				$updatedSyncroCode = substr($frameSyncroCode, 0, -1);
				$syncroCodeLength = strlen($updatedSyncroCode);			
				$suggestedSyncroWord = substr($unpackedFrame, strlen($unpackedFrame) - $syncroCodeLength, $syncroCodeLength);
				
				$proccesedSyncroCode = $updatedSyncroCode;
				$proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
				$proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

				while(in_array('x', $proccesedSyncroCodeArr))
				{
					$xPos = array_search('x', $proccesedSyncroCodeArr);
					$proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
				}
				
				$proccesedSyncroCode = implode($proccesedSyncroCodeArr);
				
				if($suggestedSyncroWord == $proccesedSyncroCode)
				{
					$syncroWordFound = true;
				}			
			}
			else
			{
				$updatedSyncroCode = $frameSyncroCode;
				$syncroCodeLength = strlen($updatedSyncroCode);			
				$suggestedSyncroWord = substr($unpackedFrame, 0, $syncroCodeLength);
				
				$proccesedSyncroCode = $updatedSyncroCode;
				$proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
				$proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

				while(in_array('x', $proccesedSyncroCodeArr))
				{
					$xPos = array_search('x', $proccesedSyncroCodeArr);
					$proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
				}
				
				$proccesedSyncroCode = implode($proccesedSyncroCodeArr);
				
				if($suggestedSyncroWord == $proccesedSyncroCode)
				{
					$syncroWordFound = true;
				}	
			}
		}
		else
		{
			$syncroWordFound = true;
		}
	
		return $syncroWordFound;
	}

	public function FrameNumToTime($extFrameNum, $extStepLength, $extStartCopyTime)
	{
		$frameNum = $extFrameNum;
		$stepLength = $extStepLength;
		$startCopyTime = $extStartCopyTime;

		$dateInterval = $frameNum * $stepLength;
		$currTime = date("H:i:s", $startCopyTime + $dateInterval);
		return $currTime;

	}

	public function FrameCountToDuration($extFramesCount, $extStepLength)
	{
		$framesCount = $extFramesCount;
		$stepLength =  $extStepLength;

		$timeInterval = $framesCount * $stepLength;

		$hours = floor($timeInterval / (60*60));
		$mins = floor(($timeInterval - $hours * 60*60) / 60);
		$secs = floor(($timeInterval - $hours * 60*60 - $mins * 60));

		if(strlen($hours) < 2)
		{
			$hours = "0".$hours;
		}
		if(strlen($mins) < 2)
		{
			$mins = "0".$mins;
		}
		if(strlen($secs) < 2)
		{
			$secs = "0".$secs;
		}
		$duration = $hours .":".$mins.":".$secs;
		return $duration;

	}
	
	public function TimeStampToDuration($extMicrosecsCount)
	{
		$microsecsCount = $extMicrosecsCount;
	
		if($microsecsCount > 1000)
		{
			$timeInterval = $microsecsCount / 1000;
		
			$hours = floor($timeInterval / (60*60));
			$mins = floor(($timeInterval - $hours * 60*60) / 60);
			$secs = floor(($timeInterval - $hours * 60*60 - $mins * 60));
		
			if(strlen($hours) < 2)
			{
				$hours = "0".$hours;
			}
			if(strlen($mins) < 2)
			{
				$mins = "0".$mins;
			}
			if(strlen($secs) < 2)
			{
				$secs = "0".$secs;
			}
			$duration = $hours .":".$mins.":".$secs;
			return $duration;
		}
		else 
		{
			return (float)($microsecsCount / 1000);
		}
	
	}

}

?>