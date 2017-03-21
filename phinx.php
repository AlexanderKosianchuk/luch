<?php

$CONFIG_FILE = __DIR__."/config/main.json";
if (!file_exists($CONFIG_FILE)) {
    throw new Exception("Config file (config/main.json) does not exist", 1);
}
$CONFIG = json_decode(file_get_contents($CONFIG_FILE), true);

if (empty($CONFIG)) {
    throw new Exception("Config is not set", 1);
}

if (!isset($CONFIG['dbSphinx'])) {
    throw new Exception("Config file does not contain sphinx dbSphinx config", 1);
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'production',
        'production' => $CONFIG['dbDoctrine'],
        'development' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'development_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'testing_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation',
];
