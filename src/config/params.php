<?php

return [
  'serverName' => (isset($_SERVER) && isset($_SERVER['SERVER_NAME']))
      ? (((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ?
        'https' : 'http') . '://' . $_SERVER['SERVER_NAME'])
      : 'https://rest.luche.com',
  'folders' => (object) [
    'runtimeDirectory' =>  SITE_ROOT_DIR.DIRECTORY_SEPARATOR.'runtime',
    'uploadedFlights' => 'uploaded-flights',
    'uploadingVoice' => 'uploading-voice',
    'uploadingFlightsTables' => 'uploading-flights-tables',
    'storedFlights' => 'flights',
    'uploadingStatus' => 'uploading-status',
    'exported' => 'exported',
    'imported' => 'imported',
  ],
  'interaction' => (object) [
    'path' => '/var/www/luche-interaction',
    'url' => 'http://172.18.0.1:1337'
  ],
  'front' => (object) [
    'origins' => [
      'http://localhost:8082',
      'http://localhost:8081',
      'http://localhost:8080',
      'http://front.luche.com:81'
    ]
  ]
];
