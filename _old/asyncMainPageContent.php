<?php

require_once("includes.php");

//if authorized
if(isset($_SESSION['uid']) && 
	isset($_SESSION['username']) && 
	isset($_SESSION['loggedIn']) && 
	($_SESSION['loggedIn'] === true))
{
	if(isset($_POST['action']) && $_POST['action'] != null)
	{
		$action = $_POST['action'];
		
		if($action == MAIN_CONTENT_FLIGHTS)
		{
			$ulogin = new uLogin();
			
			$username = $_SESSION['username'];
			
			unset($_SESSION['uid']);
			unset($_SESSION['username']);
			unset($_SESSION['loggedIn']);
			
			$ulogin->SetAutologin($username, false);
			unset($ulogin);
			
			echo json_encode("Logout succes. Page asyncUserOperation.php");		
		}
		else 
		{
			echo("Unexpected action. Page asyncUserOperation.php");
		}		

	}
	else
	{
		echo("Action is not set. Page asyncUserOperation.php");
	}
}
else 
{
	echo("Authorization error. Page asyncUserO.php");
}

?>