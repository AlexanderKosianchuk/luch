<?php

return [
    'user' => [
        'actions' => [
            'getFdrsAction',
            'getCalibrationsListAction',
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
