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
            'getCalibrationsPageAction',
            'getCalibrationParamsAction',
            'saveCalibrationAction',
            'getCalibrationByIdAction',
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
            'changeReliabilityAction',
            'deleteCalibrationAction',
            'getUserActivityAction'
        ]
    ],
    'admin' => [
        'parent' => 'moderator',
        'actions' => [
            'deleteFlightAction',
            'deleteFlightIrretrievably',
            'deleteFolderContent',
            'getUsersAction',
            'createUserAction',
            'updateUserAction',
            'deleteUserAction'
        ]
    ],
    'local' => [
        'parent' => 'admin',
        'actions' => []
    ],
];
