<?php

return [
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
    'url' => 'http://localhost:1337'
  ]
];
