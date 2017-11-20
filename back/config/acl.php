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
            'getParamInfoAction',
            'getParamMinMaxAction',
            'getFlightExceptionsAction',
            'getLegendAction',
            'setParamMinMaxAction',
            'getLogoAction',
            'figurePrintAction',
            'printBlankAction',
            'setTemplateAction',
            'mergeTemplatesAction',
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
            'flightEasyUploadAction',
            'removeTemplateAction',
            'itemExportAction',
            'changeReliabilityAction'
        ]
    ],
    'admin' => [
        'parent' => 'moderator',
        'actions' => [
            'deleteFlightAction',
            'deleteFlightIrretrievably',
            'deleteFolderContent',
            'getUsersAction'
        ]
    ],
    'local' => [
        'parent' => 'admin',
        'actions' => []
    ],
];
