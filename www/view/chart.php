<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/ChartModel.php");

$M = new ChartModel($_POST, $_SESSION);

if ($M->IsAppLoggedIn())
{
	$M->GetUserPrivilege();	
	
	if($M->action == $M->chartActions["putChartContainer"]) //show form for uploading
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['data']))
			{
				$workspace = $M->PutWorkspace();
				
				$data = array(		
						'workspace' => $workspace
				);
				
				$answ["status"] = "ok";
				$answ["data"] = $data;
				
				echo json_encode($answ);
			}
			else 
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
		
		unset($U);
	}
	else if($M->action == $M->chartActions["figurePrint"])
	{
				
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['fromTime']) &&
					isset($M->data['toTime']) &&
					isset($M->data['prms']))
			{
				$flightId = $M->data['flightId'];
				$fromTime = $M->data['fromTime'] / 1000; //to cast js to php timestamps
				$toTime = $M->data['toTime'] / 1000;
				$prms = $M->data['prms'];
				
				$globalRawParamArr = $M->GetTableRawData($flightId, $prms, $fromTime, $toTime);
				$totalRecords = count($globalRawParamArr[1]); // 0 is time and may be lager than data
				
				$exportFileInfo = $M->GetExportFileName($flightId);
				$exportedFileName = $exportFileInfo["name"];
				$exportedFilePath = $exportFileInfo["path"];
				
				$exportedFileDesc = fopen($exportedFilePath, "w");

				$figPrRow = "time;";
				for($i = 0; $i < count($prms); $i++)
				{
					$paramInfo = $M->GetParamInfo($flightId, $prms[$i]);
					$figPrRow .= $prms[$i] . ", " . $paramInfo['name'] . ";";
				}
				

				
				$figPrRow = substr($figPrRow, 0, -1);
				$figPrRow .= PHP_EOL;
				fwrite ($exportedFileDesc , $figPrRow);

				for($i = 0; $i < $totalRecords; $i++)
				{
					$figPrRow = "";
 				 	for($j = 0; $j < count($globalRawParamArr); $j++)
 			 		{
 				 		$figPrRow .= $globalRawParamArr[$j][$i] . ";";
 			 		}
			 			
 			 		$figPrRow = substr($figPrRow, 0, -1);
 			 		$figPrRow .= PHP_EOL;
 				 	fwrite ($exportedFileDesc , $figPrRow);
				}
				//fputcsv($exportedFileDesc, $globalRawParamArr);
				fclose($exportedFileDesc);
				
				$href = 'http';
				if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on"))
				{
					$href .= "s";
				}
				$href .= "://";
				if ($_SERVER["SERVER_PORT"] != "80") {
					$href .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
				}
				else
				{
					$href .= $_SERVER["SERVER_NAME"];
				}
				$href .= "/fileUploader/files/exported/" . $exportedFileName;
					
				$answ["status"] = "ok";
				$answ["data"] = $href;
				
				echo json_encode($answ);
				
// 				header("Content-type: text/csv");
// 				header("Content-Disposition: attachment; filename=file.csv");
// 				header("Pragma: no-cache");
// 				header("Expires: 0");
				
// 				flush();				
// 				for($i = 0; $i < $totalRecords; $i++)
// 				{
// 				 		$figPrRow = "";
// 				 		//array_push($figPrRow, $globalRawParamArr[0][$i][0]);//time
// 				 		for($j = 0; $j < count($globalRawParamArr); $j++)
// 			 			{
// 				 			$figPrRow .= $globalRawParamArr[$j][$i] . ",";
// 			 			}
			 			
// 			 			$figPrRow = substr($figPrRow, 0, -1);
// 			 			$figPrRow .= PHP_EOL;
// 				 		echo $figPrRow;
// 				 		flush();
// 				}
				
				/*header('Cache-control: private');
				header('Content-Type: application/octet-stream');
				header('Content-Length: '.filesize($local_file));
				header('Content-Disposition: filename='.$download_file);
				
				flush();
				$file = fopen($local_file, "r");
				while(!feof($file))
				{
					// send the current file part to the browser
					print fread($file, round($download_rate * 1024));
					// flush the content to the browser
					flush();
					// sleep one second
					sleep(1);
				}
				fclose($file);*/
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["getApParamValue"]) 
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) && 
					isset($M->data['paramApCode']) &&
					isset($M->data['totalSeriesCount']) &&
					isset($M->data['startFrame']) &&
					isset($M->data['endFrame']))
			{

				$flightId = $M->data['flightId'];
				$paramApCode = $M->data['paramApCode'];
				$totalSeriesCount = $M->data['totalSeriesCount'];
				$startFrame = $M->data['startFrame'];
				$endFrame = $M->data['endFrame'];
				
				$paramData = $M->GetApParamValue($flightId, 
					$startFrame, $endFrame, $totalSeriesCount,
					$paramApCode);
				
				echo json_encode($paramData);
			}
			else 
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
					json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
		
		unset($U);
	}
	else if($M->action == $M->chartActions["getBpParamValue"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
				isset($M->data['paramBpCode']))
			{
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramBpCode'];	
				
				$paramData = $M->GetBpParamValue($flightId, $paramCode);
	
				echo json_encode($paramData);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["rcvLegend"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['paramCodes']))
			{
				$flightId = $M->data['flightId'];
				$paramCodes = $M->data['paramCodes'];
	
				$legend = $M->GetLegend($flightId, $paramCodes);
	
				echo json_encode($legend);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["getParamMinmax"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
				isset($M->data['paramCode']) &&
				isset($M->data['tplName']))
			{
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramCode'];
				$tplName = $M->data['tplName'];
	
				$minmax = $M->GetParamMinmax($flightId, $paramCode, $tplName);
	
				echo json_encode($minmax);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["setParamMinmax"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
				isset($M->data['paramCode']) &&
				isset($M->data['tplName']) &&
				isset($M->data['min']) &&
				isset($M->data['max']))
			{
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramCode'];
				$tplName = $M->data['tplName'];
				$min = $M->data['min'];
				$max = $M->data['max'];
	
				$status = $M->SetParamMinmax($flightId, $paramCode, $tplName, $min, $max);
	
				$answ["status"] = $status;
				echo json_encode($answ);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["getParamColor"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['paramCode']))
			{
	
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramCode'];
					
				$color = $M->GetParamColor($flightId, $paramCode);
	
				echo json_encode($color);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["getParamInfo"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['paramCode']))
			{
	
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['paramCode'];
					
				$info = $M->GetParamInfo($flightId, $paramCode);
	
				echo json_encode($info);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else if($M->action == $M->chartActions["getFlightExceptions"])
	{
		$U = new User();
	
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data['flightId']) &&
					isset($M->data['refParam']))
			{
	
				$flightId = $M->data['flightId'];
				$paramCode = $M->data['refParam'];
					
				$exceptions = $M->GetFlightExceptions($flightId, $paramCode);
	
				echo json_encode($exceptions);
			}
			else
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Post: ".
						json_encode($_POST) . ". Page chart.php";
				echo(json_encode($answ));
			}
		}
		else
		{
			$answ["status"] = "err";
			$answ["error"] = $M->lang->notAllowedByPrivilege;
			echo(json_encode($answ));
		}
	
		unset($U);
	}
	else 
	{
		$msg = "Undefined action. Data: " . json_encode($_POST['data']) . 
				" . Action: " . json_encode($_POST['action']) . 
				" . Page: " . $M->curPage. ".";
		echo($msg);
		error_log($msg);
	}
}
else 
{
	echo("Authorization error. Page: " . $M->curPage);
	error_log("Authorization error. Page: " . $M->curPage);
}

?>


