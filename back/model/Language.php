<?php

namespace Model;

use Exception;

define("LANG_FILES_PATH", $_SERVER['DOCUMENT_ROOT'] . "/back/lang/");
define("LANG_FILE_DEFAULT", $_SERVER['DOCUMENT_ROOT'] . "/back/lang/EN.lang");

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

        if(is_array($langObj)) {
            if(array_key_exists($requesterPage, $langObj)) {
                $lang = (object)$langObj[$requesterPage];
            } else {
                //if no cur page in lang file try to use default
                $content = file_get_contents($defaultFilePath);
                $langObj = json_decode($content);
                if(is_array($langObj)) {
                    if(array_key_exists($requesterPage, $langObj)) {
                        $lang = (object)$langObj[$requesterPage];
                    } else {
                        echo("No language object in file for current page. " . $requesterPage);
                        error_log("No language object in file for current page. " . $requesterPage);
                        exit();
                    }
                } else {
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
            if(is_array($langObj)) {
                if(array_key_exists($requesterPage, $langObj)) {
                    $lang = (object)$langObj->$requesterPage;
                }  else {
                    $msg = "No any lang object in file for current page. " . $requesterPage;
                    throw new Exception($msg);
                    error_log($msg);
                    exit();
                }
            } else {
                $msg = "No any lang object in file for current page. " . $requesterPage;
                throw new Exception("No even default lang object in file for current page. " . $requesterPage);
                error_log($msg);
                exit();
            }
        }

        return $lang;
    }
}
