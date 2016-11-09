<?php

require_once("includes.php");

$V = new PrinterView($_POST, $_SESSION);

if ($V->IsAppLoggedIn())
{
	$V->GetUserPrivilege();
	
	if($V->action == PRINT_COLOR_EVENTS) //show form to create user
	{
		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
		{
			$V->ConstructColorFlightEventsList();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else if($V->action == PRINT_BLACK_EVENTS) //show form to create user
	{
		if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
		{
			$V->ConstructBlackFlightEventsList();
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	}
	else
	{
		exit("Unexpected action. Page asyncPrint.php");
	}
}
else
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();

	$V->PutHeader();

	$V->ShowLoginForm();

	$V->PutFooter();
}

?>