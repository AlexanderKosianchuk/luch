<?php 

require_once("includes.php"); 

$V = new BruTypeManagerView($_POST);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();
	
	$V->PutMainMenu();
	$V->PutHeader();
	$V->PutInfo();
	
	if($V->action == BRUTYPE_ADD) //show form to create user
	{
		if(in_array(PRIVILEGE_ADD_BRUTYPES, $V->privilege))
		{
			$V->ShowBruTypeCreationForm();
			$V->PutMessageBox();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == BRUTYPE_VIEW) //show form to create user
	{
		if(in_array(PRIVILEGE_VIEW_BRUTYPES, $V->privilege))
		{
			$V->ShowBruTypeCreationForm();
			$V->PutMessageBox();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == BRUTYPE_EDIT) 
	{
		if(in_array(PRIVILEGE_EDIT_BRUTYPES, $V->privilege))
		{
			$V->ShowBruTypeEditingForm();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == BRUTYPE_DELETE) // delete
	{
		if(in_array(PRIVILEGE_DEL_BRUTYPES, $V->privilege))
		{
			$V->DeleteBruType();
				
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


