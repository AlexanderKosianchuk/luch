<?

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) && 
	isset($_SESSION['username']) && 
	isset($_SESSION['loggedIn']) && 
	($_SESSION['loggedIn'] === true))
{
	if(isset($_GET['action']) && $_GET['action'] != null)
	{
		$action = $_GET['action'];
		
		if($action == BRUTYPE_PARAM_LIST)
		{
			if(isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null)
			{
				$bruTypeId = $_GET['bruTypeId'];
				
				$jtStartIndex = 0;
				if(isset($_GET['jtStartIndex']) && $_GET['jtStartIndex'] != null)
				{
					$jtStartIndex = $_GET['jtStartIndex'];
				}
				
				$jtPageSize = -1;
				if(isset($_GET['jtPageSize']) && $_GET['jtPageSize'] != null)
				{
					$jtPageSize = $_GET['jtPageSize'];
				}
				
				$jtSorting = -1;
				if(isset($_GET['jtSorting']) && $_GET['jtSorting'] != null)
				{
					$jtSorting = $_GET['jtSorting'];
				}
				
				$count = GetApParamsCount($bruTypeId);
				$data = GetApParamsList($bruTypeId, $jtStartIndex, $jtPageSize, $jtSorting);
				$output = array(
					"Result" => "OK",
					"Records" => $data,
					"TotalRecordCount" => $count
				);

				echo json_encode($output);
			}
			else 
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_PARAM_UPDATE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramData = $_POST;
		
				$resStat = UpdateApParam($bruTypeId, $paramData);	
				
				$output = array(
					"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_PARAM_CREATE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramData = $_POST;
		
				$resStat = CreateApParam($bruTypeId, $paramData);
		
				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_PARAM_DELETE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$idParam = $_POST['id'];
		
				$resStat = DeleteApParam($bruTypeId, $idParam);

				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_GRADI_LIST)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
				(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
				
				$data = GetGradiList($bruTypeId, $paramId);
				$output = array(
					"Result" => "OK",
					"Records" => $data
				);

				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_GRADI_UPDATE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
					(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
				$gradiRow = $_POST;

				$gradiList = GetGradiList($bruTypeId, $paramId);
				$newGradiList = array();
				
				foreach ($gradiList as $key => $val)
				{
					if(intval($val['gradiId']) == intval($gradiRow['gradiId']))
					{
						$newGradiList[$gradiRow['gradiCode']] = $gradiRow['gradiPh'];
					}
					else 
					{
						$newGradiList[$val['gradiCode']] = $val['gradiPh'];
					}
				}

				$resStat = UpdateGradi($bruTypeId, $paramId, $newGradiList);
				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_GRADI_DELETE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
					(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
				$gradiRow = $_POST;

				error_log(json_encode($gradiRow));
		
				$gradiList = GetGradiList($bruTypeId, $paramId);
				$newGradiList = array();
		
				foreach ($gradiList as $key => $val)
				{
					if(intval($val['gradiId']) != intval($gradiRow['gradiId']))
					{
						$newGradiList[$val['gradiCode']] = $val['gradiPh'];
					}
				}
		
				$resStat = UpdateGradi($bruTypeId, $paramId, $newGradiList);
				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_GRADI_CREATE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
					(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
				$gradiRow = $_POST;
		
				$gradiList = GetGradiList($bruTypeId, $paramId);
				$newGradiList = array();
		
				foreach ($gradiList as $key => $val)
				{
					$newGradiList[$val['gradiCode']] = $val['gradiPh'];
				}
				
				$newGradiList[$gradiRow['gradiCode']] = $gradiRow['gradiPh'];
		
				$resStat = UpdateGradi($bruTypeId, $paramId, $newGradiList);
				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_SRC_LIST)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
					(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
		
				$data = GetSrc($bruTypeId, $paramId);
				$output = array(
						"Result" => "OK",
						"Records" => $data
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else if($action == BRUTYPE_SRC_UPDATE)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null) &&
					(isset($_GET['paramId']) && $_GET['paramId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramId = $_GET['paramId'];
				$src = $_POST;
		
				$resStat = UpdateSrc($bruTypeId, $paramId, $src);
				$output = array(
						"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
				echo("Undefined bruTypeId. Page acyncApParamsBruTypeManager.php");
			}
		}
		else
		{
			error_log("Undefined action. Page acyncApParamsBruTypeManager.php");
			echo("Undefined action. Page acyncApParamsBruTypeManager.php");
		}

		
	}
	else 
	{
		error_log("Action is not set. Page acyncApParamsBruTypeManager.php");
		echo("Action is not set. Page acyncApParamsBruTypeManager.php");
	}
}
else 
{
	error_log("Authorization error. Page acyncApParamsBruTypeManager.php");
	echo("Authorization error. Page acyncApParamsBruTypeManager.php");
}

function GetApParamsCount($extBruTypeId)
{
	$bruTypeId = $extBruTypeId;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$count = $Bru->GetBruApCycloRowsTotalCount($bruType);
	unset($Bru);
	return $count;
}

function GetApParamsList($extBruTypeId, $extJtStartIndex, $extJtPageSize, $extJtSorting)
{
	$bruTypeId = $extBruTypeId;
	$jtStartIndex = $extJtStartIndex;
	$jtPageSize = $extJtPageSize;
	$jtSorting = $extJtSorting;
	
	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$apCyclo = $Bru->GetBruApCyclo($bruType, $jtStartIndex, $jtPageSize, $jtSorting);
	unset($Bru);
	return $apCyclo;
}

function UpdateApParam($extBruTypeId, $extParamData)
{
	$bruTypeId = $extBruTypeId;
	$paramData = $extParamData;
	$paramId = $paramData["id"];
	
	unset($paramData["id"]);

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$res = $Bru->UpdateApCyclo($bruType, $paramId, $paramData);
	unset($Bru);
	
	return $res;
}

function CreateApParam($extBruTypeId, $extParamData)
{
	$bruTypeId = $extBruTypeId;
	$paramData = $extParamData;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$res = $Bru->CreateApCycloParam($bruType, $paramData);
	unset($Bru);

	return $res;
}

function DeleteApParam($extBruTypeId, $extParamId)
{
	$bruTypeId = $extBruTypeId;
	$paramId = $extParamId;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$res = $Bru->DeleteApCycloParam($bruType, $paramId);
	unset($Bru);

	return $res;
}

function GetGradiList($extBruTypeId, $extParamId)
{
	$bruTypeId = $extBruTypeId;
	$paramId = $extParamId;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$apGradi = $Bru->GetBruApGradi($bruType, $paramId);
	unset($Bru);
	return $apGradi;
}

function UpdateGradi($extBruTypeId, $extParamId, $extGradiList)
{
	$bruTypeId = $extBruTypeId;
	$gradiList = $extGradiList;
	$paramId = $extParamId;
	
	ksort($gradiList);
	
	$sortedGradiList = array();
	$i = 0;
	foreach ($gradiList as $key => $val)
	{
		$sortedGradiList[strval($i)] = array(
				"x" => $val,
				"y" => $key
		);
		$i++;
	}

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$res = $Bru->UpdateApGradi($bruType, $paramId, $sortedGradiList);
	unset($Bru);

	return $res;
}

function GetSrc($extBruTypeId, $extParamId)
{
	$bruTypeId = $extBruTypeId;
	$paramId = $extParamId;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$apCycloRow = $Bru->GetBruApCycloParam($bruType, $paramId);
	unset($Bru);
	return $apCycloRow;
}

function UpdateSrc($extBruTypeId, $extParamId, $extVal)
{
	$bruTypeId = $extBruTypeId;
	$paramId = $extParamId;
	$val = $extVal;

	$Bru = new Bru();
	$bruInfo = $Bru->GetBruInfoById($bruTypeId);
	$bruType = $bruInfo['bruType'];
	$stat = $Bru->UpdateApCycloParamAttr($bruType, $paramId, "alg", $val);
	unset($Bru);
	return $stat;
}

?>