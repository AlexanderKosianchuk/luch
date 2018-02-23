<?php

namespace Exception;

use Exception;

class NotFoundException extends BaseException
{
  public $message = 'Requested data not found. Details: %s';
}
