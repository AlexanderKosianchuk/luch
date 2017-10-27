<?php

return [
    'folders' => (object) [
        'runtimeDirectory' =>  SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime',
        'uploadedFlightsFolder' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'uploaded-flights',
        'uploadingFlightsTables' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'uploading-flights-tables',
        'storedFlights' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'flights',
        'uploadingStatus' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'uploading-status',
        'exportedFolder' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'exported',
        'importedFolder' => SITE_ROOT_DIR
            .DIRECTORY_SEPARATOR.'runtime'
            .DIRECTORY_SEPARATOR.'imported',
    ]
];
