<?php

require_once("includes.php"); 

$V = new SliceView($_POST);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();

	if($V->action == SLICE_CREALE) // create slice
	{
		if(in_array(PRIVILEGE_ADD_SLICES, $V->privilege))
		{	
			$V->InsertSlice();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	
	}
	else if($V->action == SLICE_APPEND) // append
	{
		if(in_array(PRIVILEGE_EDIT_SLICES, $V->privilege))
		{
			$V->AppendFligthToSlice();
			$V->UpdateSliceTime();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	
	}
	else if($V->action == SLICE_DEL) // delete
	{
		if(in_array(PRIVILEGE_ADD_SLICES, $V->privilege))
		{
			$V->DeleteSlice();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	
	}
	else if($V->action == SLICE_ETALON) // create etalon
	{
		if(in_array(PRIVILEGE_EDIT_SLICES, $V->privilege))
		{
			$V->CreateSliceEtalon();
			$V->CalcSliceEtalonRealValueBased();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
	
	}
	else if($V->action == SLICE_COMPARE)
	{
		if(in_array(PRIVILEGE_EDIT_ENGINES, $V->privilege))
		{
			$V->CalcComparingSliceWithEtalon();
			echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
		}
		else
		{
			echo($V->lang->notAllowedByPrivilege);
		}
		
	}
	else if($V->action == SLICE_SHOW) // show slice avg
	{
		if(in_array(PRIVILEGE_VIEW_SLICES, $V->privilege))
		{
			$V->ShowSliceComment();
			$V->ShowSliceSummer();
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

