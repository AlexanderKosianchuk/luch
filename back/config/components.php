<?php

use function DI\object;
use function DI\get;

return [
    'User' => DI\object('Component\\User'),
    'TemplateEngine' => DI\object('Component\\TemplateEngine'),
    'Rbac' => DI\object('Component\\Rbac'),
    'RealConnection' => DI\object('Component\\RealConnection'),
    'ResponseRegistrator' => DI\object('Component\\ResponseRegistrator'),

    'FdrComponent' => DI\object('Component\\FdrComponent'),
];
