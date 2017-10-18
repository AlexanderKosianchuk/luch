<?php

return [
    'user' => [
        'actions' => [
            'getFdrsAction',
            'getCalibrationsListAction',
            'getFoldersAction',
            'getFlightsAction',
            'getUserSettingsAction',
            'logoutAction'
        ]
    ],
    'moderator' => [
        'parent' => 'user',
        'actions' => []
    ],
    'admin' => [
        'parent' => 'moderator',
        'actions' => []
    ],
    'local' => [
        'parent' => 'admin',
        'actions' => []
    ],
];
