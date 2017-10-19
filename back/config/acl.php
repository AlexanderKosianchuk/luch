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
            'logoutAction'
        ]
    ],
    'moderator' => [
        'parent' => 'user',
        'actions' => [
            'deleteFolderAction',
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
