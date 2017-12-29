<?php

return [
    'folders' => (object) [
        'runtimeDirectory' =>  SITE_ROOT_DIR.DIRECTORY_SEPARATOR.'runtime',
        'uploadedFlightsFolder' => 'uploaded-flights',
        'uploadingFlightsTables' => 'uploading-flights-tables',
        'storedFlights' => 'flights',
        'uploadingStatus' => 'uploading-status',
        'exportedFolder' => 'exported',
        'importedFolder' => 'imported'
    ],
    'interaction' => (object) [
        'path' => '/var/www/luche-interaction',
        'url' => 'http://localhost:1337'
    ]
];
