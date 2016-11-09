<?php 

require_once("includes.php");

$V = new TunerView($_POST);	

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();

	$V->PutHeader();
	$V->PutMainMenu();
	$V->PutInfo();
	
	if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
	{
		printf("<div id='accordion'>");
	
		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
		{
			//if process we need to show list of events
			$V->ShowFlightEventsTable();
		}
			
		$V->ShowTempltList();
		$V->ShowParamsListToCreateTemplt();
		$V->ShowParamsListToEditTemplt(); 
		$V->ShowParamsList();
	           
		printf("</div>");
		
		$V->PutMessageWindow();
	}
	else
	{
		echo($V->lang->notAllowedByPrivilege);
	}
	
	$V->PutFooter();
	$V->PutScripts();
}
else
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();

	$V->PutHeader();

	$V->ShowLoginForm();

	$V->PutFooter();
}
unset($V);
?>

