<?php

use function DI\object;
use function DI\get;

return [
    'User' => DI\object('Component\\User'),
    'TemplateEngine' => DI\object('Component\\TemplateEngine'),
    'Rbac' => DI\object('Component\\Rbac'),
    'ResponseRegistrator' => DI\object('Component\\ResponseRegistrator'),
    'FdrComponent' => DI\object('Component\\FdrComponent')
];
