<?php  

require_once("includes.php"); 

$V = new GoogleEarthView($_POST, $_GET);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->PutGoogleEarthScrSrc();
	$V->GetUserPrivilege();

	$V->PutHeader();
	$V->PutMainMenu();
	
	if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
	{	
		$V->PutInfo();
		$V->PutModelControl();
		$V->PutPluginContainer();
	}
	else
	{
		echo($V->lang->notAllowedByPrivilege);
	}

	$V->PutScripts();
	$V->PutFooter();
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


