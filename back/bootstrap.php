<?php

require_once 'vendor/autoload.php';

define('SITE_ROOT_DIR', dirname(__DIR__));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV',
    (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production')
);

if ((APPLICATION_ENV === 'dev')
  || (isset($_COOKIE['debug']) && ($_COOKIE['debug'] === '1'))
) {
    error_reporting(E_ALL);
    ini_set('display_errors','On');
    ini_set('error_log','php_errors.log');
}

// for $_SESSION init
if (session_status() == PHP_SESSION_NONE) session_start();
session_write_close();

$cfgFile = SITE_ROOT_DIR.'/back/config/main.php';
if (!file_exists($cfgFile)) {
    throw new Exception('Config file (config/main.php) does not exist', 1);
}

\Framework\Application::config(require_once($cfgFile));
