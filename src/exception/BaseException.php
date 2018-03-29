<?php

namespace Exception;

use Exception;

class BaseException extends Exception
{
  public $message = 'General exception: %s';
  public $forwardingDescription = '';

  public function __construct($args, Exception $previous = null) {
    if (is_array($args) && count($args) > 1) {
      $this->forwardingDescription = $args[1];
      $args = $args[0];
    }

    $this->message = sprintf($this->message, strval($args));
    parent::__construct($this->message, 1, $previous);
  }
}
