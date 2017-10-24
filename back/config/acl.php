<?php

return [
    'user' => [
        'actions' => [
            'getFdrsAction',
            'getCalibrationsListAction',
            'getFoldersAction',
            'toggleFolderExpandingAction',
            'createFolderAction',
            'getFlightsAction',
            'getUserSettingsAction',
            'getUserSettingsAction',
            'logoutAction'
        ]
    ],
    'moderator' => [
        'parent' => 'user',
        'actions' => [
            'deleteFolderAction',
            'storeFlightFileAction',
            'flightUploadingOptionsAction',
            'flightUploaderPreviewAction',
            'flightProccesAndCheckAction'
        ]
    ],
    'admin' => [
        'parent' => 'moderator',
        'actions' => [
            'deleteFlightAction',
            'deleteFlightIrretrievably',
            'deleteFolderContent'
        ]
    ],
    'local' => [
        'parent' => 'admin',
        'actions' => []
    ],
];
