<?php 

require_once("includes.php"); 

$V = new DiagnosticView($_POST);	
	
if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();

	$V->PutHeader();
	$V->PutMainMenu();

	if($V->action == ENGINE_DIAGNOSTIC)
	{
		if(in_array(PRIVILEGE_VIEW_ENGINES, $V->privilege))
		{
			printf("<div id='accordion'>");
			$V->PutDiagnosticReport();
			$V->PutDiagnosticLog();
			$V->PutDiagnosticContainer();
			printf("</div>");
			
			$V->PutMessageBox();
		}
		else 
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == ENGINE_DEL) 
	{
		if(in_array(PRIVILEGE_DEL_ENGINES, $V->privilege))
		{
			$V->DeleteEngine();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
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


