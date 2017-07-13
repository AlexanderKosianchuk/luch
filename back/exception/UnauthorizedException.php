<?php

namespace Exception;

use Exception;

class UnauthorizedException extends BaseException
{
    public $message = 'Unauthorized user. Auth data: %s';
}
