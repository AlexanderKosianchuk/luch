<?php

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) &&
	isset($_SESSION['username']) &&
	isset($_SESSION['loggedIn']) &&
	($_SESSION['loggedIn'] === true))
{

	if((isset($_POST['username'])) && ($_POST['username'] != NULL))
	{
		$user = $_POST['username'];

		if(((isset($_POST['flightId'])) && ($_POST['flightId'] != NULL))
			|| ((isset($_GET['flightId'])) && ($_GET['flightId'] != NULL)))
		{
			if((isset($_POST['flightId'])) && ($_POST['flightId'] != NULL))
			{
				$flightId = $_POST['flightId'];
			}
			else
			{
				$flightId = $_GET['flightId'];		
			}
		
			if((((isset($_POST['action'])) && ($_POST['action'] != NULL))) || 
			(((isset($_GET['action'])) && ($_GET['action'] != NULL))))
			{
				if(((isset($_POST['action'])) && ($_POST['action'] != NULL)))
				{
					$action = $_POST['action'];
				}
				else
				{
					$action = $_GET['action'];			
				}
				//=============================================================
				
				//=============================================================
				if($action == TPL_CACHE)
				{
					if(isset($_POST['paramCode']) || isset($_GET['paramCode']))
					{
						if(isset($_POST['paramCode']))
						{
							$paramsToCache = explode("-",$_POST['paramCode']);	
						}
						else if(isset($_GET['paramCode']))
						{
							$paramsToCache = explode("-",$_GET['paramCode']);
						}
						
						$Fl = new Flight();
						$flightInfo = $Fl->GetFlightInfo($flightId);
						$apTableName = $flightInfo['apTableName'];
						$bpTableName = $flightInfo['bpTableName'];
						$excEventsTableName = $flightInfo['exTableName'];
						$bruType = $flightInfo['bruType'];
						unset($Fl);
						
						$Bru = new Bru();
						$bruInfo = $Bru->GetBruInfo($bruType);
						$apGradiTableName = $bruInfo['gradiApTableName'];
						$bpGradiTableName = $bruInfo['gradiBpTableName'];
						$stepLength = $bruInfo['stepLength'];
						$stepDivider = $bruInfo['stepDivider'];
						$startCopyTime = $flightInfo['startCopyTime'];
						$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
						
						//results only bp
						$channelsAndMasks = $Bru->GetChannelsAndMasksByCode(
							$bpGradiTableName, $paramsToCache);
						
						//results only ap
						$channels = $Bru->GetChannelsByCode(
							$apGradiTableName, $paramsToCache);
						unset($Bru);
						
						$C = new Cache();
						$C->DropCacheBp($bpTableName);
						$C->DropCacheAp($apTableName);
		
						if(count($channelsAndMasks[0]) > 0)
						{
							$C->CacheBp($bpTableName, $channelsAndMasks[0]);
						}
						if(count($channels) > 0)
						{
							$C->CacheAp($apTableName, $channels);
						}		
		
						$C->eraseExpired();
						
						unset($C);
							
					}
					else
					{
						//log err
						echo("No params to cache!");
						/*echo("<script>location.href=location.protocol + '//' + location.host + '/tuner.php'</script>");	
						exit();*/
					}
					
				}
				//=============================================================
				
				//=============================================================
				else if($action == TPL_ADD)
				{
					if(isset($_POST['paramCode']) || isset($_GET['paramCode']))
					{
						if(isset($_POST['paramCode']))
						{
							$paramsToAdd = explode("-",$_POST['paramCode']);	
						}
						else if(isset($_GET['paramCode']))
						{
							$paramsToAdd = explode("-",$_GET['paramCode']);
						}
						
						if(isset($_POST['tplName']) || isset($_GET['tplName']))
						{
							if(isset($_POST['tplName']))
							{
								$tplName = $_POST['tplName'];	
							}
							else if(isset($_GET['tplName']))
							{
								$tplName = $_GET['tplName'];
							}
							
							$Fl = new Flight();
							$flightInfo = $Fl->GetFlightInfo($flightId);
							$bruType = $flightInfo['bruType'];
		
							$apTableName = $flightInfo['apTableName'];
							$bpTableName = $flightInfo['bpTableName'];
							unset($Fl);
							
							$Bru = new Bru();
							$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
							$gradiApTableName = $bruInfo['gradiApTableName'];
							$gradiBpTableName = $bruInfo['gradiBpTableName'];
							$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
							
							$paramsWithType = array();
							$Ch = new Channel();
	
							for($i = 0; $i < count($paramsToAdd); $i++)
							{				
								$paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramsToAdd[$i]);
								if($paramInfo['paramType'] == PARAM_TYPE_AP)
								{							
									$apTableNameWithPrefix = $apTableName . "_" . $paramInfo['prefix'];
									$paramMinMax = $Ch->GetParamMinMax($apTableNameWithPrefix, 
											$paramsToAdd[$i], $user);
									
									$paramsWithType[PARAM_TYPE_AP][] = array(
										'code' => $paramsToAdd[$i],
										'min' => $paramMinMax['min'],
										'max' => $paramMinMax['max']);
								}
								else if($paramInfo['paramType'] == PARAM_TYPE_BP)
								{
									$paramsWithType[PARAM_TYPE_BP][] = array(
										'code' => $paramsToAdd[$i]);
								}
							}
							unset($Bru);
							
							$PSTempl = new PSTempl();
							$PSTempl->DeleteTemplate($PSTTableName, $tplName, $user);
							
							$apCount = count($paramsWithType[PARAM_TYPE_AP]);
							
							for($i = 0; $i < count($paramsWithType[PARAM_TYPE_AP]); $i++)
							{
								$paramCode = $paramsWithType[PARAM_TYPE_AP][$i];
								$yMax = $paramsWithType[PARAM_TYPE_AP][$i]['max'];
								$yMin = $paramsWithType[PARAM_TYPE_AP][$i]['min'];
								$curCorridor = 0;
									
								if($yMax > 0){
									$curCorridor = ($yMax - $yMin);
								} else {
									$curCorridor = -($yMin - $yMax);
								}
							
								$axisMax = $yMax + ($i * $curCorridor);
								$axisMin = $yMin - (($apCount - $i) * $curCorridor);
								
								$PSTempl->AddParamToTemplateWithMinMax($PSTTableName,
										$tplName, $paramCode['code'], $axisMin, $axisMax, 
										$user);
							}
							
							if(isset($paramsWithType[PARAM_TYPE_BP])) 
							{						
								$busyCorridor = (($apCount - 1) / $apCount * 100);
								$freeCorridor = 100 - $busyCorridor;//100%
								
								$bpCount = count($paramsWithType[PARAM_TYPE_BP]);					
								$curCorridor = $freeCorridor / $bpCount;
								$j = 0;
								
								for($i = $apCount; $i < $apCount + $bpCount; $i++){
										
									$axisMax = 100 - ($curCorridor * $j);
									$axisMin = 0 - ($curCorridor * $j);
									
									$PSTempl->AddParamToTemplateWithMinMax($PSTTableName,
										$tplName, $paramsWithType[PARAM_TYPE_BP][$j]['code'], $axisMin, $axisMax, 
										$user);
									$j++;
								};
							}
		
							unset($Ch);
							unset($PSTempl);
							
							exit();	
						}
						else
						{
							//log err
							echo("Template name not set");	
							exit();
						}
					}
					//=============================================================
					
					//=============================================================
					else
					{
						//log err
						echo("No params to operate!");
						/*echo("<script>location.href=location.protocol + '//' + location.host + '/tuner.php'</script>");	
						exit();*/
					}
				}
				//=============================================================
				
				//=============================================================
				else if($action == TPL_DEL)
				{		
					if(isset($_POST['tplName']) || isset($_GET['tplName']))
					{
						if(isset($_POST['tplName']))
						{
							$tplName = $_POST['tplName'];	
						}
						else if(isset($_GET['tplName']))
						{
							$tplName = $_GET['tplName'];
						}
						
						$Fl = new Flight();
						$flightInfo = $Fl->GetFlightInfo($flightId);
						$bruType = $flightInfo['bruType'];
						unset($Fl);
						
						$Bru = new Bru();
						$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
						$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
						unset($Bru);
						
						$PSTempl = new PSTempl();
						$PSTempl->DeleteTemplate($PSTTableName, $tplName, $user);
		
						unset($PSTempl);
						
						exit();	
					}
					else
					{
						//log err
						echo("Template name not set");	
						exit();
					}			
				}
				//=============================================================
				
				//=============================================================
				else if($action == TPL_DEFAULT)
				{
					if(isset($_POST['tplName']) || isset($_GET['tplName']))
					{
						if(isset($_POST['tplName']))
						{
							$tplName = $_POST['tplName'];
						}
						else if(isset($_GET['tplName']))
						{
							$tplName = $_GET['tplName'];
						}
				
						$Fl = new Flight();
						$flightInfo = $Fl->GetFlightInfo($flightId);
						$bruType = $flightInfo['bruType'];
						unset($Fl);
				
						$Bru = new Bru();
						$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
						$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
						unset($Bru);
				
						$PSTempl = new PSTempl();
						$PSTempl->SetDefaultTemplate($PSTTableName, $tplName, $user);
				
						unset($PSTempl);
				
						exit();
					}
					else
					{
						//log err
						echo("Template name not set. Page asyncTplServer.php");
						exit();
					}
				}
			
				//=============================================================
				
				//=============================================================
				else if($action == TPL_SET_PARAM_MINMAX)
				{
					if(isset($_POST['paramCode']) || isset($_GET['paramCode']))
					{
						if(isset($_POST['paramCode']))
						{
							$paramCode = $_POST['paramCode'];
						}
						else if(isset($_GET['paramCode']))
						{
							$paramCode = $_GET['paramCode'];
						}
				
						if(isset($_POST['tplName']) || isset($_GET['tplName']))
						{
							if(isset($_POST['tplName']))
							{
								$tplName = $_POST['tplName'];
							}
							else if(isset($_GET['tplName']))
							{
								$tplName = $_GET['tplName'];
							}
							
							if((isset($_POST['min']) || isset($_GET['min'])) && (isset($_POST['max']) || isset($_GET['max'])))
							{
								if(isset($_POST['min']) && isset($_POST['max']))
								{
									$min = $_POST['min'];
									$max = $_POST['max'];
								}
								else if(isset($_GET['min']) && isset($_GET['max']))
								{
									$min = $_GET['min'];
									$max = $_GET['max'];
								}
								
								$Fl = new Flight();
								$flightInfo = $Fl->GetFlightInfo($flightId);
								$bruType = $flightInfo['bruType'];
								unset($Fl);
									
								$Bru = new Bru();
								$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
								$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
								unset($Bru);
								
								$PSTempl = new PSTempl();
								$PSTempl->UpdateParamMinMax($PSTTableName, $tplName, $paramCode, $min, $max, $user);
								unset($PSTempl);
								
								//echo "ok";
							}
							else 
							{
								//log err
								echo("Min max not set");
								exit();
							}
						}
						else
						{
							//log err
							echo("Template name not set");
							exit();
						}
					}
					else
					{
						//log err
						echo("No params to operate!");
						/*echo("<script>location.href=location.protocol + '//' + location.host + '/tuner.php'</script>");
						exit();*/
					}
				}
				//=============================================================
				
				//=============================================================
				else if($action == TPL_GET_PARAM_MINMAX)
				{	
					if(isset($_POST['paramCode']) || isset($_GET['paramCode']))
					{
						if(isset($_POST['paramCode']))
						{
							$paramCode = $_POST['paramCode'];
						}
						else if(isset($_GET['paramCode']))
						{
							$paramCode = $_GET['paramCode'];
						}
				
						if(isset($_POST['tplName']) || isset($_GET['tplName']))
						{
							if(isset($_POST['tplName']))
							{
								$tplName = $_POST['tplName'];
							}
							else if(isset($_GET['tplName']))
							{
								$tplName = $_GET['tplName'];
							}
					
							$Fl = new Flight();
							$flightInfo = $Fl->GetFlightInfo($flightId);
							$bruType = $flightInfo['bruType'];
							unset($Fl);
			
							$Bru = new Bru();
							$bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
							$PSTTableName = $bruInfo['paramSetTemplateListTableName'];
							unset($Bru);
							
							$PSTempl = new PSTempl();
							$minMax = $PSTempl->GetParamMinMax($PSTTableName, $tplName, 
									$paramCode, $user);
							unset($PSTempl);
	
							if($minMax == '')
							{
								$minMax = array(
									'min' => -1,
									'max' => 1);	
							}
							
							echo json_encode($minMax);
						}
						else
						{
							//log err
							echo("Template name not set. Page asyncTplServer.php");
							exit();
						}
					}
					//=============================================================
						
					//=============================================================
					else
					{
						//log err
						echo("No params to operate. Page asyncTplServer.php");
						/*echo("<script>location.href=location.protocol + '//' + location.host + '/tuner.php'</script>");
							exit();*/
					}
				}
			}
			else
			{
				//log err
				echo("Action not set. Page asyncTplServer.php");
				/*echo("<script>location.href=location.protocol + '//' + location.host + '/tuner.php'</script>");*/
				exit();		
			}
	
		}
		else
		{
			//log err
			echo("Flight not selected. Page asyncTplServer.php");
			/*echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");*/
			exit();	
				
		}
	}
	else
	{
		//log err
		echo("User not set. Page asyncTplServer.php");
		exit();
	}
}
else
{
	echo("Authorization error. Page asyncTplServer.php");
}
?>
