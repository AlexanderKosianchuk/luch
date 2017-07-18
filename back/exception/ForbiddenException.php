<?php

namespace Exception;

use Exception;

class ForbiddenException extends BaseException
{
    public $message = 'Prohibited action. Details: %s';
}
