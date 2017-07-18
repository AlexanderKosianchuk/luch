<?php

namespace Exception;

use Exception;

class BaseException extends Exception
{
    public $message = 'General exception: %s';

    public function __construct($arg, Exception $previous = null) {
        $this->message = sprintf($this->message, strval($arg));
        parent::__construct($this->message, 1, $previous);
    }
}
