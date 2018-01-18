<?php

namespace Exception;

use Exception;

class BadRequestException extends BaseException
{
  public $message = 'Bad input data. Received: %s';
}
