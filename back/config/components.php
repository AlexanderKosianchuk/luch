<?php

use function DI\object;
use function DI\get;

return [
    'TemplateEngine' => DI\object('Component\TemplateEngine'),
    'Rbac' => DI\object('Component\Rbac'),
    'RealConnection' => DI\object('Component\RealConnection'),

    'responseRegistrator' => DI\object('Component\ResponseRegistrator'),
    'user' => DI\object('Component\User'),
    'fdr' => DI\object('Component\FdrComponent'),
    'flight' => DI\object('Component\FlightComponent'),
    'event' => DI\object('Component\EventComponent'),
    'userSettings' => DI\object('Component\UserSettingsComponent'),
    'folder' => DI\object('Component\FolderComponent'),
    'runtimeManager' => DI\object('Component\RuntimeManager'),
    'calibration' => DI\object('Component\CalibrationComponent'),
    'flightProcessor' => DI\object('Component\FlightProcessingComponent'),
    'eventProcessor' => DI\object('Component\EventProcessingComponent'),
];
