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
            'setUserSettingsAction',
            'getFlightEventsAction',
            'getFlightInfoAction',
            'getCycloAction',
            'getFlightTemplatesAction',
            'getTemplateAction',
            'putChartContainerAction',
            'getApParamDataAction',
            'getBpParamDataAction',
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
            'flightProccesAndCheckAction',
            'flightEasyUploadAction'
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
