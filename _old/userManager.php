<?php 

require_once("includes.php"); 

$V = new UserManagerView($_POST);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();
	
	$V->PutMainMenu();
	$V->PutHeader();
	
	if($V->action == USER_CREATE) //show form to create user
	{
		if(in_array(PRIVILEGE_ADD_USERS, $V->privilege))
		{
			$V->ShowUserCreationForm();
			$V->PutMessageBox();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == USER_EDIT) 
	{
		if(in_array(PRIVILEGE_EDIT_USERS, $V->privilege))
		{
			$V->ShowUserEditingForm();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == USER_DELETE) // delete
	{
		if(in_array(PRIVILEGE_DEL_USERS, $V->privilege))
		{
			$V->DeleteUser();
				
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


