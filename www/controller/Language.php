<?php

define("ACTIONS_FILE", $_SERVER['DOCUMENT_ROOT'] . "/lang/_actions.info");
define("LANG_FILES_PATH", $_SERVER['DOCUMENT_ROOT'] . "/lang/");
define("LANG_FILE_DEFAULT", $_SERVER['DOCUMENT_ROOT'] . "/lang/Default.lang");

class Language
{	
	public function GetLanguageName()
	{
		if(isset($_SESSION['lang']) && ($_SESSION['lang'] != '')) {
			return $_SESSION['lang'];
		} else {
			$_SESSION['lang'] = 'en';
			return 'en';
		}
	}
	
	public function SetLanguageName($extSelectedLang)
	{
		$selectedLang = $extSelectedLang;
		$_SESSION['lang'] = $selectedLang;
	}
	
	public function GetLanguage($extRequesterPage)
	{
		$requesterPage = $extRequesterPage;
		$selectedLang = $this->GetLanguageName();
		
		$filePath = LANG_FILES_PATH . strtoupper($selectedLang) . ".lang";
		$defaultFilePath = LANG_FILE_DEFAULT;
		
		$lang = null;
		
		$content = file_get_contents($filePath);
		$langObj = json_decode($content, true);
	
		if(is_array($langObj))
		{
			if(array_key_exists($requesterPage, $langObj))
			{
				$lang = (object)$langObj[$requesterPage];
			}
			else
			{
				//if no cur page in lang file try to use default
				$content = file_get_contents($defaultFilePath);
				$langObj = json_decode($content);
				if(is_array($langObj))
				{
					if(array_key_exists($requesterPage, $langObj))
					{
						$lang = (object)$langObj[$requesterPage];
					}
					else
					{
						echo("No language object in file for current page. " . $requesterPage);
						error_log("No language object in file for current page. " . $requesterPage);
						exit();
					}
				}
				else
				{
					echo("No language object in file for current page. " . $requesterPage);
					error_log("No language object in file for current page. " . $requesterPage);
					exit();
				}
			}
		}
		else
		{
			//if no lang file try to use default
			$content = file_get_contents($defaultFilePath);
			$langObj = json_decode($content);
			if(is_array($langObj))
			{
				if(array_key_exists($requesterPage, $langObj))
				{
					$lang = (object)$langObj->$requesterPage;
				}
				else
				{
					error_log("No any lang object in file for current page. " . $requesterPage);
					exit();
				}
			}
			else
			{
				error_log("No even default lang object in file for current page. " . $requesterPage);
				exit();
			}
		}
		
		return $lang;
	}
	
	///
	// GetServiceStrs
	///
	public function GetServiceStrs($extRequesterPage)
	{
		$requesterPage = $extRequesterPage;
	
		$filePath = ACTIONS_FILE;
		
		$content = file_get_contents($filePath);
		$srvcStrObj = json_decode($content, true);
	
		if(is_array($srvcStrObj))
		{
			if(array_key_exists($requesterPage, $srvcStrObj))
			{
				$sysStr = (object)$srvcStrObj[$requesterPage];
			}
			else
			{
				echo("No system str in file for current page. " . $requesterPage . ". Language.php");
				error_log("No system str in file for current page. " . $requesterPage . ". Language.php");
				exit();
			}
		}
		else
		{
			error_log("No system str file. " . $requesterPage . ". Language.php");
			exit();
		}
	
		return $sysStr;
	}
}


?>