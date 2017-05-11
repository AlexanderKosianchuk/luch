<?php

$CONFIG_FILE = __DIR__."/back/config/main.php";
if (!file_exists($CONFIG_FILE)) {
    throw new Exception("Config file (/back/config/main.php) does not exist", 1);
}
$CONFIG = include($CONFIG_FILE);

if (empty($CONFIG)) {
    throw new Exception("Config is not set", 1);
}

if (!isset($CONFIG['dbSphinx'])) {
    throw new Exception("Config file does not contain sphinx dbSphinx config", 1);
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/back/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/back/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => $CONFIG['dbSphinx']['name'],
        'production' => $CONFIG['dbSphinx'],
        'development' => $CONFIG['dbSphinx'],
        'testing' => $CONFIG['dbSphinx'],
    ],
    'version_order' => 'creation',
];
