<?php 

require_once("includes.php"); 

$V = new UploaderView($_FILES, $_POST, $_SESSION);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();	
	
	$V->PutMainMenu();
	$V->PutHeader();
	
	$V->PutInfo();
	
	if($V->action == FILE_UPLOAD) //show form for uploading
	{
		if(in_array(PRIVILEGE_ADD_FLIGHTS, $V->privilege))
		{
			//if process we need to show list of events
			$V->CopyFiles();
			$V->ShowFlightParams();
		
			$V->PutRedirectForm();
			$V->PutMessageBox();
			$V->PutDragProgressBar();
			$V->PutLoadingBox();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == FILE_DELETE) // delete
	{
		if(in_array(PRIVILEGE_DEL_FLIGHTS, $V->privilege))
		{
			//$V->DropCache();
			$V->DeleteFlight();
				
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
			exit();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
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

?>


