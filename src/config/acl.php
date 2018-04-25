<?php

return [
  'user' => [
    'actions' => [
      'Controller\UsersController\changeLanguageAction',
      'Controller\UsersController\logoutAction',
      'Controller\UsersController\getSettingsAction',
      'Controller\UsersController\setSettingsAction',
      'Controller\UsersController\getLogoAction',

      'Controller\CalibrationController\getAction',
      'Controller\CalibrationController\getAllAction',
      'Controller\CalibrationController\saveAction',
      'Controller\CalibrationController\getPageAction',
      'Controller\CalibrationController\getParamsAction',
      'Controller\CalibrationController\exportAction',

      'Controller\FdrController\getAllAction',
      'Controller\FdrController\getCycloAction',
      'Controller\FdrController\getCycloByFdrIdAction',
      'Controller\FdrController\setParamColorAction',
      'Controller\FdrController\getParamInfoAction',

      'Controller\FdrTemplateController\getAllAction',

      'Controller\FlightCommentController\getAction',

      'Controller\FlightEventsController\getAction',
      'Controller\FlightEventsController\getByCode',
      'Controller\FlightEventsController\printBlankAction',

      'Controller\FlightsController\getAllAction',
      'Controller\FlightsController\getInfoAction',
      'Controller\FlightsController\coordinatesAction',
      'Controller\FlightsController\processAction',
      'Controller\FlightsController\changePathAction',

      'Controller\FlightTemplateController\getAllAction',
      'Controller\FlightTemplateController\getAction',
      'Controller\FlightTemplateController\setAction',
      'Controller\FlightTemplateController\deleteAction',
      'Controller\FlightTemplateController\mergeAction',
      'Controller\FlightTemplateController\setParamMinMaxAction',
      'Controller\FlightTemplateController\getParamMinMaxAction',

      'Controller\FlightDataController\getApParamDataAction',
      'Controller\FlightDataController\getBpParamDataAction',

      'Controller\FolderController\getAllAction',
      'Controller\FolderController\toggleExpandingAction',
      'Controller\FolderController\createAction',
      'Controller\FolderController\renameAction',
      'Controller\FolderController\changePathAction',

      'Controller\ChartController\figurePrintAction',
      'Controller\ChartController\getLegendAction',
    ]
  ],
  'moderator' => [
    'parent' => 'user',
    'actions' => [
      'Controller\UsersController\getActivityAction',
      'Controller\CalibrationController\deleteAction',

      'Controller\FlightEventsController\changeReliabilityAction',

      'Controller\FlightsController\deleteAction',
      'Controller\FlightsController\exportAction',

      'Controller\FolderController\deleteAction',

      'Controller\UploaderController\flightUploadingOptionsAction',
      'Controller\UploaderController\flightUploaderPreviewAction',
      'Controller\UploaderController\flightProccesAndCheckAction',
      'Controller\UploaderController\flightProccesAction',
      'Controller\UploaderController\flightEasyUploadAction',
      'Controller\UploaderController\itemImportAction',
      'Controller\UploaderController\syncAction',
      'Controller\UploaderController\storeFlightFileAction',
      'Controller\UploaderController\getSegmentAction',

      'Controller\ResultsController\getReportAction',

      'deleteFlight', // is not controller action

      // TODO: /*'Controller\UploaderController\processFrameAction',*/
      // TODO: /*'Controller\UploaderController\breakFramesProcessAction',*/
    ]
  ],
  'admin' => [
    'parent' => 'moderator',
    'actions' => [
      'Controller\UsersController\getAllAction',
      'Controller\UsersController\createAction',
      'Controller\UsersController\updateAction',
      'Controller\UsersController\deleteAction',

      'Controller\FlightCommentController\setAction',

      'deleteFlightIrretrievably', // is not controller action
      'deleteFolderContent', //  is not controller action
    ]
  ],
  'local' => [
    'parent' => 'admin',
    'actions' => []
  ],
];
