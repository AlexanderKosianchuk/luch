<?

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
		
		if($action == BRUTYPE_GET_INFO)
		{
			if(isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null)
			{
				$bruTypeId = $_GET['bruTypeId'];
				

				$output = array(
					"Result" => "OK",
				);

				echo json_encode($output);
			}
			else 
			{
				error_log("Undefined bruTypeId. Page asyncBruTypeInfo.php");
				echo("Undefined bruTypeId. Page asyncBruTypeInfo.php");
			}
		}
		else if($action == BRUTYPE_SET_INFO)
		{
			if((isset($_GET['bruTypeId']) && $_GET['bruTypeId'] != null))
			{
				$bruTypeId = $_GET['bruTypeId'];
				$paramData = $_POST;
		
				//$resStat = UpdateApParam($bruTypeId, $paramData);	
				
				$output = array(
					"Result" => $resStat
				);
		
				echo json_encode($output);
			}
			else
			{
				error_log("Undefined bruTypeId. Page asyncBruTypeInfo.php");
				echo("Undefined bruTypeId. Page asyncBruTypeInfo.php");
			}
		}		
	}
	else 
	{
		error_log("Action is not set. Page asyncBruTypeInfo.php");
		echo("Action is not set. Page asyncBruTypeInfo.php");
	}
}
else 
{
	error_log("Authorization error. Page asyncBruTypeInfo.php");
	echo("Authorization error. Page asyncBruTypeInfo.php");
}


?>