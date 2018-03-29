<?php
require_once 'bootstrap.php';

use \Framework\Application as App;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet(App::em('default'));
