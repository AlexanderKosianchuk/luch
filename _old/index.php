<?php 

require_once("includes.php"); 

// Start a secure session if none is running
if (!sses_running())
{
	sses_start();
}

$V = new IndexView();

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();
	
	$V->PutHeader();
	
	$V->PutMainMenu();
	
	$V->FileUploadBlock();
	$V->FileImportBlock();
	$V->SliceCreationBlock();
	
	//$V->ShowSearchBox();
	$V->ShowFlightList();
	$V->ShowSliceList();
	$V->ShowEngineList();
	$V->ShowBruTypesList();
	$V->ShowUsersList();
	
	$V->PutMessageBox();
	$V->PutExportLink();
	 
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