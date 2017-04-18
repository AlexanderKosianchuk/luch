<?php

//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set('output_buffering', 'On');
ini_set('default_charset', 'utf-8');

define("SITE_ROOT_DIR", __DIR__);

define("UPLOADED_FILES_DIR",  "/fileUploader/files/");
define("UPLOADED_FILES_PATH", @$_SERVER['DOCUMENT_ROOT'] . UPLOADED_FILES_DIR);

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
