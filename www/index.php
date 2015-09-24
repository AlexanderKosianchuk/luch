<?php 

require_once(@__DIR__."/includes.php"); 
require_once(@__DIR__."/model/IndexModel.php");

// Start a secure session if none is running
if (!sses_running())
{
	sses_start();
}

$M = new IndexModel();

if ($M->IsAppLoggedIn())
{
	$M->PutCharset();
	$M->PutTitle();
	$M->PutStyleSheets();
	$M->GetUserPrivilege();
	
	$M->PutHeader();
	$M->EventHandler();
		
/*	$M->PutMainMenu();
	$M->PutLeftMenu();
	$M->PutMainContainer();
	
	$M->FileUploadBlock(); */
	
/* 	$M->FileUploadBlock();
  	$M->FileImportBlock();
   	$M->SliceCreationBlock(); */
		
	$M->PutMessageBox();
	$M->PutExportLink();
	 
	$M->PutFooter();
	$M->PutScripts();
}
else 
{
	$M->PutCharset();
	$M->PutTitle();
	$M->PutStyleSheets();
	
	$M->PutHeader();
	
	$M->ShowLoginForm();
	
	$M->PutFooter();
}

unset($M);

?>