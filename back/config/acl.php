<?php

return [
    'user' => [
        'actions' => [
            'getFdrsAction',
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
