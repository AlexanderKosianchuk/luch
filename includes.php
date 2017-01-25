<?php

define("SITE_ROOT_DIR", __DIR__);

$CONFIG = json_decode(file_get_contents(@SITE_ROOT_DIR."/config/main.json"));

//service scr
require_once(@SITE_ROOT_DIR."/model/Language.php");

//component
require_once(@SITE_ROOT_DIR."/component/FlightComponent.php");

//controller
require_once(@SITE_ROOT_DIR."/model/DataBaseConnector.php");
require_once(@SITE_ROOT_DIR."/model/Flight.php");
require_once(@SITE_ROOT_DIR."/model/Folder.php");
require_once(@SITE_ROOT_DIR."/model/Bru.php");
require_once(@SITE_ROOT_DIR."/model/Frame.php");
require_once(@SITE_ROOT_DIR."/model/Channel.php");

require_once(@SITE_ROOT_DIR."/model/FlightException.php");
require_once(@SITE_ROOT_DIR."/model/ParamSetTemplate.php");
require_once(@SITE_ROOT_DIR."/model/Slice.php");
require_once(@SITE_ROOT_DIR."/model/Engine.php");
require_once(@SITE_ROOT_DIR."/model/User.php");
require_once(@SITE_ROOT_DIR."/model/Airport.php");
require_once(@SITE_ROOT_DIR."/model/Vocabulary.php");
require_once(@SITE_ROOT_DIR."/model/SearchFlights.php");
require_once(@SITE_ROOT_DIR."/model/UserOptions.php");

require_once(@SITE_ROOT_DIR."/controller/CController.php");

define("UPLOADED_FILES_DIR",  "/fileUploader/files/");
define("UPLOADED_FILES_PATH",
    @$_SERVER['DOCUMENT_ROOT'] . UPLOADED_FILES_DIR);


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
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

define("VERSION", '6.6.12.02');
