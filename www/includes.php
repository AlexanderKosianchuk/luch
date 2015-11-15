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

define("UPLOADED_FILES_PATH", @$_SERVER['DOCUMENT_ROOT'] . "/fileUploader/files/");

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

// define("TPL_CACHE", 'cache');
// define("TPL_ADD", 'add');
// define("TPL_DEFAULT", 'default');
// define("TPL_DEL", 'del');
// define("TPL_GET_PARAM_MINMAX", 'getminmax');
// define("TPL_SET_PARAM_MINMAX", 'setminmax');

// define("PRINT_COLOR_EVENTS", 'printColorEvents');
// define("PRINT_BLACK_EVENTS", 'printBlackEvents');

// define("FLIGHT_GET_CUR_ID", 'getId');
// define("FLIGHT_CONVERT", 'conv');
// define("FLIGHT_PROC", 'proc');
// define("FLIGHT_COMPARE_TO_ETALON", 'compare');
// define("FLIGHT_DEL_TEMP", 'deltmp');
// define("FLIGHT_EXPORT", 'exp');
// define("FLIGHT_IMPORT", 'imp');

// define("UPLOADER_TO_MAIN", 'toMain');
// define("UPLOADER_TO_TUNER", 'toTuner');
// define("UPLOADER_TO_CHART", 'toChart');
// define("UPLOADER_TO_DIAGNOSTIC", 'toDiagnostic');

// define("UPLOADER_PREVIEW", 'preview');
// define("UPLOADER_SLICE", 'slice');

// define("FLIGHT_CREATE", 'create');
// define("FLIGHT_APPEND_FRAME", 'append');

// define("FILE_UPLOAD", 'upload');
// define("FILE_UPLOAD_AND_PROC", 'uploadAndProc');
// define("FILE_DELETE", 'delete');

// define("ETALON_DO_NOT_COMPARE", 'donotcompare');
// define("SLICE_CREALE", 'create');
// define("SLICE_SHOW", 'show');
// define("SLICE_APPEND", 'append');
// define("SLICE_ETALON", 'etalon');
// define("SLICE_COMPARE", 'compare');
// define("SLICE_DEL", 'del');

// define("CHART_PARAM_INFO_RECEIVE_LEGENT", 'rcvLegend');
// define("CHART_PARAM_INFO_SET_PARAM_COLOR", 'setColor');
// define("CHART_PARAM_INFO_GET_PARAM_COLOR", 'getColor');

// define("ENGINE_DIAGNOSTIC", 'engineDiagnostic');
// define("ENGINE_DEL", 'engineDel');

// define("USER_LOGOUT", 'logout');
// define("USER_VIEW", 'view');
// define("USER_CREATE", 'create');
// define("USER_EDIT", 'edit');
// define("USER_DELETE", 'delete');

// define("BRUTYPE_VIEW", 'view');
// define("BRUTYPE_ADD", 'add');
// define("BRUTYPE_EDIT", 'edit');
// define("BRUTYPE_DELETE", 'delete');

// define("BRUTYPE_PARAM_LIST", 'apParamList');
// define("BRUTYPE_PARAM_CREATE", 'apParamCreate');
// define("BRUTYPE_PARAM_UPDATE", 'apParamUpdate');
// define("BRUTYPE_PARAM_DELETE", 'apParamDelete');

// define("BRUTYPE_GRADI_LIST", 'gradiList');
// define("BRUTYPE_GRADI_CREATE", 'gradiCreate');
// define("BRUTYPE_GRADI_UPDATE", 'gradiUpdate');
// define("BRUTYPE_GRADI_DELETE", 'gradiDelete');

// define("BRUTYPE_SRC_LIST", 'srcList');
// define("BRUTYPE_SRC_UPDATE", 'srcUpdate');

// define("MAIN_CONTENT_FLIGHTS", 'flights');
// define("MAIN_CONTENT_SLICES", 'slices');
// define("MAIN_CONTENT_ENGINES", 'engines');
// define("MAIN_CONTENT_BRU_TYPES", 'brutypes');
// define("MAIN_CONTENT_USERS", 'users');
// define("MAIN_CONTENT_DOCS", 'docs');

// define("GET_ETALON_ENGINES", 'getEtalonEngines');
// define("GET_ENGINE_SLICES", 'getEngineSlices');
// define("GET_ENGINE_DISCREP", 'getEngineDiscrep');
// define("GET_DISCREP_VALS", 'getDiscrepVals');
// define("GET_DISCREP_LIMITS", 'getDiscrepLimits');
// define("GET_DISCREP_REPORT", 'getReport');

// define("DIAGNOSTIC_ACTION", 'action');
// define("DIAGNOSTIC_ETALON_ID", 'etalonId');
// define("DIAGNOSTIC_ENGINE_SERIAL", 'engineSerial');
// define("DIAGNOSTIC_SLICE", 'slice');
// define("DIAGNOSTIC_ABSCISSA", 'abscissa');
// define("DIAGNOSTIC_ORDINATE", 'ordinate');
// define("DIAGNOSTIC_DISCREP", 'discrep');
// define("DIAGNOSTIC_FROM_DATE", 'fromDate');
// define("DIAGNOSTIC_TO_DATE", 'toDate');
// define("DIAGNOSTIC_DISCREP_TYPE", 'type');
// define("DIAGNOSTIC_ABSCISSA_FLIGHTS", 'flights');
// define("DIAGNOSTIC_IGNORE_ETALON", 'ignore');

// define("COORDINATES_ACTION_GET_COORD", 'coord');
// define("COORDINATES_ACTION_GET_PARAMS", 'params');

// define("GPS_LONG_MIN", 'LONG_MIN');
// define("GPS_LONG_SEC", 'LONG_SEC');
// define("GPS_LAT_MIN", 'LAT_MIN');
// define("GPS_LAT_SEC", 'LAT_SEC');

// define("COORD_TG", 'TG');
// define("COORD_KM", 'KM');
// define("COORD_KR", 'KR');

// define("DISCREP_Y_ARR", serialize(array("Tt*_pr", "Nvd_pr/sqrt(Tt*_pr)", "Gt_pr", "Gt_pr/Tt*_pr", "Pt*_1k_pr", "Gt_pr/Pt*_1k_pr")));
// define("DISCREP_X", serialize("S"));

/*$protectSrc = @SITE_ROOT_DIR."/protect/protect.php";

if(file_exists($protectSrc)){
	require_once($protectSrc);
} else {
	die("Copyright rules were brocken. Please contact Luch service privider.");
}*/

?>
