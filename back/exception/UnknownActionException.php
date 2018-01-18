<?php

namespace Exception;

use Exception;

class UnknownActionException extends BaseException
{
  public $message = 'Unknown action: %s';
}
