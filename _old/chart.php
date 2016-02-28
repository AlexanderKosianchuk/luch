<?php  

require_once("includes.php"); 

$V = new ChartView($_POST, $_GET);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();
	
	$V->PutHeader();
	$V->PutMainMenu();
	
	if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
	{
		$V->PutChartContainter();
		$V->PutInfo();
		
		$V->PutLoadingBox();
		
		////
		//tableHolder to calc params after graph open 
		//this gives posibility, when user opens table params 
		//already calculated and cached
		///	
		//$V->PutTable();
		
		//form to open table in new tab
		$V->PutFormToOpenTable();
		
		//form to open table in new tab
		$V->PutFormToOpenMap();
		
		//form to open model in new tab
		$V->PutFormToOpenModel();
		
		//form to open google earth in new tab
		$V->PutFormToOpenGoogleEarth();
		
		//info dialog
		$V->PutInfoForm();
		
		$V->PutScripts();
		$V->PutFooter();
	}
	else 
	{
		echo($V->lang->notAllowedByPrivilege);
	}

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
