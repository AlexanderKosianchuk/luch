<?php

define("SITE_ROOT_DIR", __DIR__);

//ulogin
require_once(@SITE_ROOT_DIR."/ulogin/config/all.inc.php");
require_once(@SITE_ROOT_DIR."/ulogin/main.inc.php");

//service scr
require_once(@SITE_ROOT_DIR."/controller/Language.php");

//controller
require_once(@SITE_ROOT_DIR."/controller/DataBaseConnector.php");
require_once(@SITE_ROOT_DIR."/controller/Flight.php");
require_once(@SITE_ROOT_DIR."/controller/Folder.php");
require_once(@SITE_ROOT_DIR."/controller/Bru.php");
require_once(@SITE_ROOT_DIR."/controller/Frame.php");
require_once(@SITE_ROOT_DIR."/controller/Channel.php");
//require_once(@"/controller/Cacher.php");
require_once(@SITE_ROOT_DIR."/controller/FlightException.php");
require_once(@SITE_ROOT_DIR."/controller/ParamSetTemplate.php");
require_once(@SITE_ROOT_DIR."/controller/Slice.php");
require_once(@SITE_ROOT_DIR."/controller/Engine.php");
require_once(@SITE_ROOT_DIR."/controller/User.php");
require_once(@SITE_ROOT_DIR."/controller/Airport.php");
require_once(@SITE_ROOT_DIR."/controller/Vocabulary.php");

define("UPLOADED_FILES_DIR",  "/fileUploader/files/");
define("UPLOADED_FILES_PATH", 
    @$_SERVER['DOCUMENT_ROOT'] . UPLOADED_FILES_DIR);

//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set('error_log', @__DIR__.'/php_errors.log');
ini_set('output_buffering', 'On');
ini_set('default_charset', 'utf-8');

define("PARAM_TYPE_AP", "ap");
define("PARAM_TYPE_BP", "bp");
define("POINT_MAX_COUNT", 8500);
define("PARAMS_PAGING", 200);

//dataTable
define("sEcho", 0);
define("iDisplayStart", 3);
define("iDisplayLength", 4);
define("reservDisplayLength", 100);

define("PARAMS_TPL_NAME", 'last');
define("EVENTS_TPL_NAME", 'events');
define("TPL_DEFAULT", 'default');

define("FOLDER_START_ID", 1000000);
