<?php

use function DI\object;
use function DI\get;

return [
    'TemplateEngine' => DI\object('Component\TemplateEngine'),
    'Rbac' => DI\object('Component\Rbac'),
    'RealConnection' => DI\object('Component\RealConnection'),

    'responseRegistrator' => DI\object('Component\ResponseRegistrator'),
    'user' => DI\object('Component\User'),
    'userManager' => DI\object('Component\UserManagementComponent'),
    'fdr' => DI\object('Component\FdrComponent'),
    'fdrTemplate' => DI\object('Component\FdrTemplateComponent'),
    'flight' => DI\object('Component\FlightComponent'),
    'event' => DI\object('Component\EventComponent'),
    'userSettings' => DI\object('Component\UserSettingsComponent'),
    'folder' => DI\object('Component\FolderComponent'),
    'runtimeManager' => DI\object('Component\RuntimeManager'),
    'calibration' => DI\object('Component\CalibrationComponent'),
    'flightProcessor' => DI\object('Component\FlightProcessingComponent'),
    'eventProcessor' => DI\object('Component\EventProcessingComponent'),
    'channel' => DI\object('Component\ChannelComponent'),
    'osInfo' => DI\object('Component\OsInfoComponent')
];
