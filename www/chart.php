<?php 

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/model/ChartModel.php");

$M = new ChartModel($_POST, $_SESSION, $_GET);

if ($M->IsAppLoggedIn())
{
	$M->GetUserPrivilege();	
	
	if($M->action == $M->chartActions["putChartInNewWindow"]) //show form for uploading
	{
		$U = new User();
		
		if(in_array($U::$PRIVILEGE_VIEW_FLIGHTS, $M->privilege))
		{
			if(isset($M->data) && ($M->data != null) && (is_array($M->data)))
			{
				$M->PutCharset();
				$M->PutTitle();
				$M->PutStyleSheets();
				$M->PutHeader();
				$M->PrintInfoFromRequest();
				$M->PrintWorkspace();
				$M->PutScripts();
				$M->PutFooter();
				
			}
			else 
			{
				$answ["status"] = "err";
				$answ["error"] = "Not all nessesary params sent. Request: ".
					json_encode($_GET) . ". Page chart.php";
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
	echo("Authorization error. Page: " . $M->currPage);
	error_log("Authorization error. Page: " . $M->currPage);
}

?>


