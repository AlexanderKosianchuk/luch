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
		
		if($action == USER_LOGOUT)
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
		else if($action == USER_CREATE)
		{
			if(isset($_POST['user']) && 
					isset($_POST['company']) &&
					isset($_POST['pwd']) &&
					isset($_POST['privilege']) &&
					isset($_POST['mySubscriber']) &&
					isset($_POST['author']))
			{
				$user = $_POST['user'];
				$company = $_POST['company'];
				$pwd = $_POST['pwd'];
				$privilege = $_POST['privilege'];
				$mySubscriber = $_POST['mySubscriber'];
				$author = $_POST['author'];
				
				$permittedFlights = array();
				if(isset($_POST['permittedFlights']))
				{
					if($_POST['permittedFlights'] != "")
					{
						if(strpos(",", $_POST['permittedFlights']) !== false)
						{
							$permittedFlights = explode(",", $_POST['permittedFlights']);
						}
						else 
						{
							$permittedFlights[] = $_POST['permittedFlights'];
						}
					}
				}
				
				$permittedSlices = array();
				if(isset($_POST['permittedSlices']))
				{
					if($_POST['permittedSlices'] != "")
					{
						if(strpos(",", $_POST['permittedSlices']) !== false)
						{
							$permittedSlices = explode(",", $_POST['permittedSlices']);
						}
						else
						{
							$permittedSlices[] = $_POST['permittedSlices'];
						}
					}
				}
				
				$permittedEngines = array();
				if(isset($_POST['permittedEngines']))
				{
					if($_POST['permittedEngines'] != "")
					{
						if(strpos(",", $_POST['permittedEngines']) !== false)
						{
							$permittedEngines = explode(",", $_POST['permittedEngines']);
						}
						else
						{
							$permittedEngines[] = $_POST['permittedEngines'];
						}
					}
				}
				
				$permittedBruTypes = array();
				if(isset($_POST['permittedBruTypes']))
				{
					if($_POST['permittedBruTypes'] != "")
					{
						if(strpos(",", $_POST['permittedBruTypes']) !== false)
						{
							$permittedBruTypes = explode(",", $_POST['permittedBruTypes']);
						}
						else
						{
							$permittedBruTypes[] = $_POST['permittedBruTypes'];
						}
					}
				}
				
				$permittedDocs = array();
				if(isset($_POST['permittedDocs']))
				{
					if($_POST['permittedDocs'] != "")
					{
						if(strpos(",", $_POST['permittedDocs']) !== false)
						{
							$permittedDocs = explode(",", $_POST['permittedDocs']);
						}
						else
						{
							$permittedDocs[] = $_POST['permittedDocs'];
						}
					}
				}
				
				$permittedUsers = array();
				if(isset($_POST['permittedUsers']))
				{
					if($_POST['permittedUsers'] != "")
					{
						if(strpos(",", $_POST['permittedUsers']) !== false)
						{
							$permittedUsers = explode(",", $_POST['permittedUsers']);
						}
						else
						{
							$permittedUsers[] = $_POST['permittedUsers'];
						}
					}
				}
				
				$Usr = new User();
				
				if (!$Usr->CheckUserPersonalExist($user))
				{			
					$ulogin = new uLogin();
					
					if (!$ulogin->CreateUser( $_POST['user'],  $_POST['pwd']))
					{
						$msg = 'userCreationFailed';
					}
					else
					{
						$Usr->CreateUserPersonal($user, $privilege, $author, $company);
						
						if($mySubscriber)
						{
							$Usr->AppendSubscriber($author, $user);
						}
						
						$createdUserId = $Usr->GetIdByUsername($user);
						$Usr->SetUsersAvaliable($author, $createdUserId);
						
						$subscribersArr = $Usr->SelectSubscribers($author);
						
						foreach($subscribersArr as $subscriber)
						{
							$Usr->SetUsersAvaliable($subscriber, $createdUserId);
						}
						
						//=====================
							
						foreach($permittedFlights as $id)
						{
							error_log($user . " " . $id);
							$Usr->SetFlightAvaliable($user, $id);
						}
						
						foreach($permittedSlices as $id)
						{
							$Usr->SetSliceAvaliable($user, $id);
						}
						
						foreach($permittedEngines as $id)
						{
							$Usr->SetEnginesAvaliable($user, $id);
						}
						
						foreach($permittedBruTypes as $id)
						{
							error_log($user . " " . $id);
							$Usr->SetBruTypeAvaliable($user, $id);
						}
						
						foreach($permittedDocs as $id)
						{
							$Usr->SetDocsAvaliable($user, $id);
						}
						
						foreach($permittedUsers as $id)
						{
							$Usr->SetUsersAvaliable($user, $id);
						}
						
						$msg = 'ok';
					}
				}
				else 
				{
					$msg = 'userAlreadyExist';
				}
			}
			else 
			{
				$msg = 'notAllFieldsMatched';
			}
			
			unset($Usr);
			
			echo json_encode($msg);
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
	echo("Authorization error. Page asyncUserOperation.php");
}

?>