<?php

require_once(@__DIR__."/includes.php");
require_once(@__DIR__."/controller/EntryController.php");

new EntryController($_POST, $_SESSION, $_COOKIE);
