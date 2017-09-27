<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

require_once "includes.php";

define('SITE_ROOT_DIR', __DIR__);

// for $_SESSION init
if (session_status() == PHP_SESSION_NONE) session_start();
session_write_close();

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$anotationConfig = Setup::createAnnotationMetadataConfiguration(
    [@__DIR__."/entity"],
    $isDevMode
);

$CONFIG_FILE = __DIR__."/config/main.php";
if (!file_exists($CONFIG_FILE)) {
    throw new Exception("Config file (config/main.php) does not exist", 1);
}
$CONFIG = require_once($CONFIG_FILE);

if (empty($CONFIG)) {
    throw new Exception("Config is not set", 1);
}

if (!isset($CONFIG['dbDoctrine'])) {
    throw new Exception("Config file does not contain doctrine dbDoctrine config", 1);
}

// obtaining the entity manager
$EM = EntityManager::create(
    $CONFIG['dbDoctrine'],
    $anotationConfig
);

$EM->getConfiguration()->addEntityNamespace('Entity', 'Entity');
