<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

class Channel
{
// 	private function GetFlightChannel($extTableName, $extChannels,
// 		$extStepLength, $extStartCopyTime,
// 		$extStartFrame, $extEndFrame, $extDivider)
// 	{
// 		$tableName = $extTableName;
// 		$channels = (array)$extChannels;
// 		$assosiativeChannel = array();
// 		$count = count($channels);
// 		$startFrame = $extStartFrame;
// 		$endFrame = $extEndFrame;
// 		$divider = $extDivider;

// 		$stepLength = $extStepLength;
// 		$stepTime = $stepLength * 1000;
// 		$startCopyTime = $extStartCopyTime;
// 		//date_default_timezone_set('Europe/Kiev');
// 		$startTime = $startCopyTime * 1000;
// 		$stepMicroTime = round($stepTime / $count, 0);

// 		$c = new DataBaseConnector();
// 		$link = $c->Connect();

// 		$query = "SELECT `frameNum`, `channel`, `value` FROM `".$tableName."` WHERE (";
// 		for($i = 0; $i < count($channels); $i++)
// 		{
// 			$query .= " (`channel` = ".$channels[$i].") OR";
// 			$assosiativeChannel[$channels[$i]] = $i;
// 		}

// 		$query = substr($query, 0, -2);
// 		$query .= ") AND ((`frameNum` >= ".$startFrame.") AND
// 			(`frameNum` <= ".$endFrame.")) ";
// 		//divider for extreame zipping
// 		if($divider > 1)
// 		{
// 			$query .= "AND (`frameNum` % ".$divider." = 0) ";
// 		}

// 		$query .= "ORDER BY `frameNum`, `channel` ASC";

// 		$result = $link->query($query);

// 		$pointPairList = array();
// 		$stepCounter = 0;
// 		$steps = count($channels);
// 		while($row = $result->fetch_array())
// 		{
// 			$dateInterval = $row['frameNum'] * $stepTime;
// 			$microTime = $assosiativeChannel[$row['channel']] * $stepMicroTime;
// 			$currTime = $startTime + $dateInterval + $microTime;
// 			$point = array($currTime, $row['value']);
// 			$pointPairList[] = $point;
// 		}

// 		$result->free();
// 		$c->Disconnect();

// 		unset($c);

// 		return $pointPairList;
// 	}

// 	public function GetFlightParam($extApTableName,
// 			$extStartCopyTime, $extStepLength, $extSeriesCountDivider,
// 			$extStartFrame, $extEndFrame, $extChannels)
// 	{
// 		$apTableName = $extApTableName;
// 		$startCopyTime = $extStartCopyTime;
// 		$startFrame = $extStartFrame;
// 		$endFrame = $extEndFrame;
// 		$stepLength = $extStepLength;
// 		$seriesDivider = $extSeriesCountDivider;
// 		$channels = $extChannels;

// 		$framesCount = $endFrame - $startFrame;
// 		$pointCount = $framesCount * count($channels) *
// 		$seriesDivider;
// 		$divider = 1;

// 		$pointPairList = array();

// 		if($pointCount < POINT_MAX_COUNT)
// 		{
// 			if($pointCount < POINT_NO_COMPRESSION_COUNT)
// 			{

// 				$pointPairList = $this->GetFlightChannel($apTableName, $channels, $stepLength, $startCopyTime, $startFrame, $endFrame, $divider);
// 			}
// 			else if(($pointCount >= POINT_NO_COMPRESSION_COUNT) &&
// 					($pointCount < POINT_HALF_COMPRESSION_COUNT))
// 			{
// 				$halfChannel = array();
// 				for($i = 0; $i < (count($channels) / 2); $i++)
// 				{
// 				array_push($halfChannel, $channels[$i]);
// 				}

// 				$pointPairList = $this->GetFlightChannel($apTableName, $halfChannel, $stepLength, $startCopyTime, $startFrame, $endFrame, $divider);
// 			}
// 			else
// 			{
// 			$pointPairList = $this->GetFlightChannel($apTableName, $channels[0], $stepLength, $startCopyTime, $startFrame, $endFrame, $divider);
// 			}
// 			}
// 			else
// 			{
// 			//modifying divider to perform compression
// 				$divider = intval($framesCount * $seriesDivider /
// 						POINT_MAX_COUNT);
// 						$pointPairList = $this->GetFlightChannel($apTableName, $channels[0], $stepLength, $startCopyTime, $startFrame, $endFrame, $divider);
// 			}

// 			//$pointPairList = $this->GetFlightChannel($apTableName, $channels, $stepLength, $startCopyTime);

// 				return $pointPairList;
// 				//return $pointPairList;
// 	}

	private function GetFlightChannel($extTableName, $extCode, $extCodeTablePrefix,
			$extStartFrame, $extEndFrame, $extDivider = 1)
	{
		$tableName = $extTableName;
		$code = $extCode;
		$prefix = $extCodeTablePrefix;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$divider = $extDivider;

		if($divider == 1)
		{
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
			((`frameNum` >= ".$startFrame.") AND
			(`frameNum` < ".$endFrame."))
			ORDER BY `time` ASC";
		}
		else
		{
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
			((`frameNum` >= ".$startFrame.") AND
			(`frameNum` < ".$endFrame.")) AND
			(`frameNum` % ".$divider." = 0)
			GROUP BY frameNum
			ORDER BY `time` ASC";
		}

		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);

		$pointPairList = array();
		while($row = $result->fetch_array())
		{
			$point = array($row['time'], $row[$code]);
			$pointPairList[] = $point;
		}

		$result->free();
		$c->Disconnect();

		unset($c);

		return $pointPairList;
	}
	
	public function GetFlightParam($extApTableName,
			$extSeriesCountDivider,	$extStartFrame, $extEndFrame,
			$extCode, $extCodeTablePrefix, $extCodeFreq)
	{
		$apTableName = $extApTableName;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$seriesDivider = $extSeriesCountDivider;
		$code = $extCode;
		$prefix = $extCodeTablePrefix;
		$codeFreq = $extCodeFreq;
	
		$framesCount = $endFrame - $startFrame;
		$pointCount = $framesCount * $codeFreq * $seriesDivider;
	
		$pointPairList = array();
	
		if($pointCount < POINT_MAX_COUNT)
		{
			$pointPairList = $this->GetFlightChannel($apTableName, $code, $prefix,
					$startFrame, $endFrame);
		}
		else
		{
			//modifying divider to perform compression
			$divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
			$pointPairList = $this->GetFlightChannel($apTableName, $code, $prefix,
					$startFrame, $endFrame, $divider);
		}
		return $pointPairList;
	}
	
	public function GetFlightParamValue($extApTableName,
			$extFrame, $extCode, $extCodeTablePrefix)
	{
		$apTableName = $extApTableName;
		$frame = $extFrame;
		$code = $extCode;
		$prefix = $extCodeTablePrefix;
		
		$pointPairList = array();
		
		$query = "SELECT `time`, `".$code."` FROM `".$apTableName."_".$prefix."` WHERE
		`frameNum` = ".$frame."
		ORDER BY `time` ASC";
		
		error_log($query);
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		
		$row = $result->fetch_array();

		$point = array($row['time'], $row[$code]);
		
		$result->free();
		$c->Disconnect();
		
		unset($c);
		
		return $point;
	}

	private function GetFlightChannelWithExactSection($extTableName, $extCode, $extCodeTablePrefix,
			$extStartFrame, $extEndFrame, $extSeriesDivider, $extTotalFramesCount, $extDivider, $extBrute)
	{
		$tableName = $extTableName;
		$code = $extCode;
		$prefix = $extCodeTablePrefix;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$divider = $extDivider;
		$seriesDivider = $extSeriesDivider;
		$totalFramesCount = $extTotalFramesCount;
		$brute = $extBrute;
	
		$pointPairList = array();
	
		if($brute == false)
		{	
			$divider = ceil($totalFramesCount * $seriesDivider / POINT_MAX_COUNT);
				
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
				((`frameNum` < ".$startFrame.") AND
				(`frameNum` % ".$divider." = 0))
				GROUP BY frameNum
				ORDER BY `time` ASC";
			
			$c = new DataBaseConnector();
			$link = $c->Connect();
			$result = $link->query($query);
				
			while($row = $result->fetch_array())
			{
				$point = array($row['time'], $row[$code]);
				$pointPairList[] = $point;
			}
			$result->free();
				
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
				((`frameNum` >= ".$startFrame.") AND
				(`frameNum` <= ".$endFrame."))
				GROUP BY frameNum
				ORDER BY `time` ASC";
				
			$result = $link->query($query);
	
			while($row = $result->fetch_array())
			{
				$point = array($row['time'], $row[$code]);
				$pointPairList[] = $point;
			}
			$result->free();
				
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
				((`frameNum` > ".$endFrame.") AND
				(`frameNum` % ".$divider." = 0))
				GROUP BY frameNum
				ORDER BY `time` ASC";
			$result = $link->query($query);
	
			while($row = $result->fetch_array())
			{
				$point = array($row['time'], $row[$code]);
				$pointPairList[] = $point;
			}
			$result->free();
			$c->Disconnect();
			unset($c);
		}
		else
		{
			$query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
			(`frameNum` % ".$divider." = 0)
			GROUP BY frameNum
			ORDER BY `time` ASC";
				
			$c = new DataBaseConnector();
			$link = $c->Connect();
			$result = $link->query($query);
				
			while($row = $result->fetch_array())
			{
				$point = array($row['time'], $row[$code]);
				$pointPairList[] = $point;
			}
				
			$result->free();
			$c->Disconnect();
				
			unset($c);
		}
	
		return $pointPairList;
	}
	
	public function GetFlightParamWithExactSection($extApTableName,
			$extSeriesCountDivider,	$extStartFrame, $extEndFrame,
			$extCode, $extCodeTablePrefix, $extCodeFreq)
	{
		$apTableName = $extApTableName;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$seriesDivider = $extSeriesCountDivider;
		$code = $extCode;
		$prefix = $extCodeTablePrefix;
		$codeFreq = $extCodeFreq;
		$totalFramesCount = $extEndFrame - $extStartFrame;
	
		$framesCount = $endFrame - $startFrame;
		$pointCount = $framesCount * $codeFreq * $seriesDivider;
	
		$pointPairList = array();
	
		//if not much points betweet start and end frames
		//we should build this segment very exact
		//else use general compression by divider
		if($pointCount < POINT_MAX_COUNT)
		{
			$divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
			$pointPairList = $this->GetFlightChannelWithExactSection($apTableName, $code, $prefix,
					$startFrame, $endFrame, $seriesDivider, $totalFramesCount, $divider, false);
		}
		else
		{
			//modifying divider to perform compression
			$divider = ceil($framesCount * $seriesDivider / POINT_MAX_COUNT);
			$pointPairList = $this->GetFlightChannelWithExactSection($apTableName, $code, $prefix,
					$startFrame, $endFrame, $seriesDivider, $totalFramesCount, $divider, true);
		}
		return $pointPairList;
	}

	/*private function GetBinaryChannel($extTableName, $extChannels,
			$extMasks, $extStepLength, $extStartCopyTime,
			$extStartFrame, $extEndFrame)
	{
	
		$tableName = $extTableName;
		$channels = (array)$extChannels;
		$assosiativeChannel = array();
		$masks = (array)$extMasks;
		$count = count($channels);
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
	
		$stepLength = $extStepLength;
		$stepTime = $stepLength * 1000;
		$startCopyTime = $extStartCopyTime;
		$startTime = $startCopyTime * 1000;
		$stepMicroTime = round($stepTime / $count, 0);
	
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$channelsInQuery = implode(", ",$channels);
		$maskInQuery = implode(", ",$masks);
	
		$query = "SELECT `frameNum`, `channel` FROM `".$tableName."` WHERE
		`channel` IN (".$channelsInQuery.") AND
		`mask` IN (".$maskInQuery.") ";
	
		for($i = 0; $i < $count; $i++)
		{
			$assosiativeChannel[$channels[$i]] = $i;
		}
	
		$query .= "ORDER BY frameNum, channel ASC;";

		$result = $link->query($query);

		$pointPairList = array();
		$pointPairList2 = array();

		//if exists though one row in table
		if($row = $result->fetch_array())
		{
			$point = array('null','null');
			$pointPairList[] = $point;
			$dateInterval = $row['frameNum'] * $stepTime;
			$microTime = $assosiativeChannel[$row['channel']] * $stepMicroTime;
			$currTime = $startTime + $dateInterval + $microTime;
			$point = array($currTime, 1);
			$pointPairList[] = $point;
			$previousTime = $currTime;

			$pointPairList2[] = $point;

			//our task is to find first appearence of bp, write it, path to the last
			//appearence, also write it, put null and than search next appearance
			while($row = $result->fetch_array())
			{
				$dateInterval = $row['frameNum'] * $stepTime;
				$microTime = $assosiativeChannel[$row['channel']] * $stepMicroTime;
				$currTime = $startTime + $dateInterval + $microTime;

				$pointPairList2[] = array($currTime, 1);
				if($previousTime == $currTime - $stepMicroTime)
				{
					if(count($pointPairList) > 2)
					{
						if($pointPairList[count($pointPairList) - 3][1] == 'null')
						{
							$point = array($currTime, 1);
							$pointPairList[count($pointPairList) - 1] = $point;
							$previousTime = $currTime;
						}
						else
						{
							$point = array($currTime, 1);
							$pointPairList[] = $point;
							$previousTime = $currTime;
						}
					}
					else
					{
						$point = array($currTime, 1);
						$pointPairList[] = $point;
						$previousTime = $currTime;
					}
				}
				else
				{
					$point = array('null','null');
					$pointPairList[] = $point;
					$point = array($currTime, 1);
					$pointPairList[] = $point;
					$previousTime = $currTime;
				}
			}

			$result->free();
		}
		else
		{
			$point = array('null','null');
			$pointPairList[] = $point;
		}
		
					////$point = array($currTime->format('H:i:s').".".$stepMicroTime * $stepCounter, $row['value']);
			//					if($row['value'] > 0)
			//					{
			//						//if bin var is already set, we dont need to set it again
			//						$lastEl = end($pointPairList);
			//						if($lastEl[1] != 1)
			//						{
			//							$point = array(strtotime($startCopyTime)*1000 + $stepMicroTime * $stepCounter,
			//								$row['value']);
			//							$previousPoint = array(strtotime($startCopyTime)*1000 + $stepMicroTime * $stepCounter,
			//								$row['value']);
			//							$pointPairList[] = $point;
			//							$stepCounter++;
			//						}
			//						else
			//						{
			//							//to use when bp downs to zero
			//							$previousPoint = array(strtotime($startCopyTime)*1000 + $stepMicroTime * $stepCounter,
			//								$row['value']);
			//							$stepCounter++;
			//						}
			//					}
			//					else
			//					{
			//						//if bin var is 0 we need to set null to flot chart
			//						$lastEl = end($pointPairList);
			//						if($lastEl[1] != "null")
			//						{
			//							$point = array('null','null');
			//							//add last point where bp was set to 1
			//							$pointPairList[] = $previousPoint;
			//							$pointPairList[] = $point;
			//							$stepCounter++;
			//						}
			//						else
			//						{
			//							$stepCounter++;
			//						}
			//					}
			//				}
			//				else
			//				{
			//					if($row['value'] > 0)
			//					{
			//						$lastEl = end($pointPairList);
			//						if($lastEl[1] != 1)
			//						{
			//							$currTime->add($dateInterval);
			//							$startCopyTime = $currTime->format('y-m-d H:i:s');
			//							$point = array(strtotime($startCopyTime)*1000, $row['value']);
			//							$pointPairList[] = $point;
			//							$stepCounter = 1;
			//						}
			//						else
			//						{
			//							$currTime->add($dateInterval);
			//							$startCopyTime = $currTime->format('y-m-d H:i:s');
			//							$previousPoint = array(strtotime($startCopyTime)*1000, $row['value']);
			//							$stepCounter = 1;
			//						}
			//					}
			//					else
			//					{
			//						//if bin var is 0 we need to set null to flot chart
			//						$lastEl = end($pointPairList);
			//						if($lastEl[1] != "null")
			//						{
			//							$currTime->add($dateInterval);
			//							$startCopyTime = $currTime->format('y-m-d H:i:s');
			//							$point = array('null','null');
			//							if($lastEl[1] != $previousPoint[1])
			//							{
			//								$pointPairList[] = $previousPoint;
			//							}
			//							$pointPairList[] = $point;
			//							$stepCounter = 1;
			//						}
			//						else
			//						{
			//							$currTime->add($dateInterval);
			//							$startCopyTime = $currTime->format('y-m-d H:i:s');
			//							$stepCounter = 1;
			//						}
			//					}
			//				}
		$c->Disconnect();

		unset($c);


		return $pointPairList;
	}*/

	private function GetBinaryChannel($extTableName, $extCode, $extStepLength, $extFreq)
	{
		$tableName = $extTableName;
		$code = $extCode;
		$stepLength = $extStepLength;
		$freq = $extFreq;
		$stepMicroTime = $stepLength / $freq * 1000;
		
		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE " .
			"`code` = '".$code."' " . 
			"ORDER BY `time` ASC;";

		$result = $link->query($query);

		$pointPairList = array();
		$pointPairList2 = array();

		//if exists though one row in table
		if($row = $result->fetch_array())
		{
			$point = array('null','null');
			$pointPairList[] = $point;
			$currTime = $row['time'];
			
			$point = array($currTime, 1);
			$pointPairList[] = $point;
			$previousTime = $currTime;

			$pointPairList2[] = $point;

			//our task is to find first appearence of bp, write it, path to the last
			//appearence, also write it, put null and than search next appearance
			while($row = $result->fetch_array())
			{
				$currTime = $row['time'];

				$pointPairList2[] = array($currTime, 1);
				if($previousTime == $currTime - $stepMicroTime)
				{
					if(count($pointPairList) > 2)
					{
						if($pointPairList[count($pointPairList) - 3][1] == 'null')
						{
							$point = array($currTime, 1);
							$pointPairList[count($pointPairList) - 1] = $point;
							$previousTime = $currTime;
						}
						else
						{
							$point = array($currTime, 1);
							$pointPairList[] = $point;
							$previousTime = $currTime;
						}
					}
					else
					{
						$point = array($currTime, 1);
						$pointPairList[] = $point;
						$previousTime = $currTime;
					}
				}
				else
				{
					$point = array('null','null');
					$pointPairList[] = $point;
					$point = array($currTime, 1);
					$pointPairList[] = $point;
					$previousTime = $currTime;
				}


			}

			$result->free();
		}
		else
		{
			$point = array('null','null');
			$pointPairList[] = $point;
		}
		$c->Disconnect();

		unset($c);


		return $pointPairList;
	}

	public function GetBinaryParam($extTableName, $extCode, $extStepLength, $extFreq)
	{
		$bpTableName = $extTableName;
		$stepLength = $extStepLength;
		$code = $extCode;
		$freq = $extFreq;

		$pointPairList = array();

		$pointPairList = $this->GetBinaryChannel($bpTableName, $code, $stepLength, $freq);

		$tempString = json_encode($pointPairList);
		//in bin params point equal to null we had put ["null","null"]
		$searchSubstr = '["null","null"]';
		//$searchSubstr = 'null';
		$transmitStr = str_replace($searchSubstr, 'null', $tempString);
		//var_dump($transmitStr);


		return json_decode($transmitStr);
		//return $pointPairList;
	}

// 	public function NormalizeApParam($extApTableName,
// 			$extStepDivider, $extChannels)
// 	{
// 		$tableName = $extApTableName;
// 		$stepDivider = $extStepDivider;
// 		$channels = $extChannels;
// 		$steps = count($channels);
// 		$duplication = $stepDivider / $steps - 1;

// 		$c = new DataBaseConnector();
// 		$link = $c->Connect();

// 		$query = "SELECT `value` FROM `".$tableName."` WHERE (";
// 		for($i = 0; $i < $steps; $i++)
// 		{
// 			$query .= " (`channel` = ".$channels[$i].") OR";
// 		}

// 		$query = substr($query, 0, -2);
// 		$query .= " ) ORDER BY `frameNum`, `channel` ASC";

// 		$result = $link->query($query);

// 		$normArr = array();
// 		while($row = $result->fetch_array())
// 		{
// 			array_push($normArr, $row['value']);
// 			for($i = 0; $i < $duplication; $i++)
// 			{
// 				array_push($normArr, $row['value']);
// 			}
// 		}
// 		return $normArr;
// 	}

// 	public function NormalizeBpParam($extBpTableName,
		// 			$extStepDivider, $extChannels, $extMasks, $extTotalFrameNum)
// 	{
// 		$tableName = $extBpTableName;
// 		$stepDivider = $extStepDivider;
// 		$channels = $extChannels;
// 		$masks = $extMasks;
// 		$totalFrameNum = $extTotalFrameNum;
// 		$steps = $totalFrameNum * $stepDivider;
// 		$count = count($channels);

// 		$c = new DataBaseConnector();
// 		$link = $c->Connect();

// 		$query = "SELECT `frameNum`, `channel`, `mask` FROM `".$tableName."` WHERE (";
		// 		for($i = 0; $i < $count; $i++)
	// 		{
		// 			$query .= " ((`channel` = ".$channels[$i].") AND
				// 			(`mask` = ".$masks[$i].")) OR";
		// 		}

		// 		$query = substr($query, 0, -2);
		// 		$query .= ") ORDER BY `frameNum`, `channel`, `mask` ASC";
// 		$result = $link->query($query);

// 		$normArr = array();
// 		for($i = 0; $i < $steps; $i++)
	// 		{
// 			$normArr[$i] = 0;
// 		}

// 		while($row = $result->fetch_array())
	// 		{
// 			$position = $row['frameNum'] * $stepDivider;
// 			$normArr[$position] = 1;
// 			for($i = 1; $i < $stepDivider; $i++)
	// 			{
// 				$position = $row['frameNum'] * $stepDivider + $i;
// 				$normArr[$position] = 1;
// 			}
// 		}
// 		return $normArr;
// 	}

	public function GetNormalizedApParam($extApTableName, 
			$extStepDivider, $extCode, $extFreq, $extPefix,
			$extStartFrame, $extEndFrame)
	{
		$tableName = $extApTableName . "_" . $extPefix;
		$stepDivider = $extStepDivider;
		$code = $extCode;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$steps = $extFreq;
		$duplication = $stepDivider / $steps;

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT `".$code."` FROM `".$tableName."` WHERE 
			`frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame." 
			ORDER BY `frameNum`ASC";
		
		$result = $link->query($query);

		$normArr = array();
		while($row = $result->fetch_array())
		{
			array_push($normArr, $row[$code]);
			for($i = 1; $i < $duplication; $i++)
			{
				array_push($normArr, $row[$code]);
			}
		}
		
		$result->free();
		$c->Disconnect();
		
		return $normArr;
	}

	public function GetNormalizedBpParam($extBpTableName,
			$extStepDivider, $extCode, $extFreq, $extPefix,
			$extStartFrame, $extEndFrame)
	{
		$tableName = $extBpTableName . "_".$extPefix;
		$stepDivider = $extStepDivider;
		$code = $extCode;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		$steps = $extFreq;
		$duplication = $stepDivider / $steps;
		$totalRows = ($endFrame - $startFrame) * $stepDivider;

		$c = new DataBaseConnector();
		$link = $c->Connect();

		$query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE `code` = '" . $code . "' ".
			"AND `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame. " ".
			"ORDER BY `time` ASC";
	
		$result = $link->query($query);		

		$normArr = array();
		for($i = 0; $i < $totalRows; $i++)
		{
			$normArr[$i] = 0;
		}

		while($row = $result->fetch_array())
		{
			$position = ($row['frameNum'] - $startFrame) * $stepDivider;
			//error_log($position);
			$normArr[$position] = 1;
			for($i = 1; $i < $stepDivider; $i++)
			{
				$position = ($row['frameNum'] - $startFrame) * $stepDivider + $i;
				$normArr[$position] = 1;
			}
		}
				
		$result->free();
		$c->Disconnect();
		
		return $normArr;
	}
	
	
// 	public function NormalizeBpParam($extBpTableName,
// 			$extStepDivider, $extChannels, $extMasks,
// 			$extStartFrame, $extEndFrame)
// 	{
// 		$tableName = $extBpTableName;
// 		$stepDivider = $extStepDivider;
// 		$channels = $extChannels;
// 		$masks = $extMasks;
// 		$startFrame = $extStartFrame;
// 		$endFrame = $extEndFrame;
// 		$steps = ($endFrame - $startFrame) * $stepDivider;
	
// 		$count = count($channels);
	
// 		$c = new DataBaseConnector();
// 		$link = $c->Connect();
	
// 		$query = "SELECT `frameNum`, `channel`, `mask` FROM `".$tableName."` WHERE (";
// 		for($i = 0; $i < $count; $i++)
// 		{
// 		$query .= " ((`channel` = ".$channels[$i].") AND
// 			(`mask` = ".$masks[$i].")) OR";
// 		}
	
// 		$query = substr($query, 0, -2);
// 		$query .= ") AND `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame."
// 		ORDER BY `frameNum`, `channel`, `mask` ASC";
// 		$result = $link->query($query);
	
// 			$normArr = array();
// 			for($i = 0; $i < $steps; $i++)
// 			{
// 			$normArr[$i] = 0;
// 			}
	
// 			while($row = $result->fetch_array())
// 			{
// 			$position = ($row['frameNum'] - $startFrame) * $stepDivider;
// 			//error_log($position);
// 			$normArr[$position] = 1;
// 			for($i = 1; $i < $stepDivider; $i++)
// 			{
// 			$position = ($row['frameNum'] - $startFrame) * $stepDivider + $i;
// 			$normArr[$position] = 1;
// 			}
// 			}
	
// 			//error_log(json_encode($normArr));
	
// 			$result->free();
// 			$c->Disconnect();
	
// 			return $normArr;
// 	}

	public function NormalizeTime($extStepDivider, $extStepLength,
			$extTotalFrameNum, $extStartCopyTime, $extStartFrame, $extEndFrame)
	{
		$stepLength = $extStepLength;
		$stepDivider = $extStepDivider;
		$totalFrameNum = $extTotalFrameNum;
		$startCopyTime = $extStartCopyTime;
		$startFrame = $extStartFrame;
		$endFrame = $extEndFrame;
		//date_default_timezone_set('Europe/Kiev');
		$stepMicroTime = round($stepLength * 1000 / $stepDivider, 0);

		$normTime = array();
		for($i = $startFrame; $i < $endFrame; $i++)
		{
			$microTime = 0;
			$dateInterval = $i * $stepLength;
			$currTime = $startCopyTime + $dateInterval;
			array_push($normTime, date("H:i:s", $currTime). "." . $microTime);
			for($j = 1; $j < $stepDivider; $j++)
			{
				$microTime = $j * $stepMicroTime;
				$dateInterval = $i * $stepLength;
				$currTime = $startCopyTime + $dateInterval;
				array_push($normTime, date("H:i:s", $currTime) . "." . $microTime);
			}
		}
		return $normTime;
	}
	
	public function GetParamMinMax($extApTableName, $extParamCode)
	{
		$apTableName = $extApTableName;
		$paramCode = $extParamCode;
		
		$minMax = array();
		
		$c = new DataBaseConnector();
		$link = $c->Connect();
		
		$query = "SELECT MIN(`".$paramCode."`), MAX(`".$paramCode."`) FROM `".$apTableName."` WHERE 1;";
		//error_log($query);
		$result = $link->query($query);
		
		$row = $result->fetch_array();
		$minMax['min'] = $row["MIN(`".$paramCode."`)"];
		$minMax['max'] = $row["MAX(`".$paramCode."`)"];
		
		$result->free();
		$c->Disconnect();
		
		return $minMax;
		
	}
}

?>