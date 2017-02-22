<?php

require_once "bootstrap.php";

new \Controller\EntryController($_POST, $_SESSION, $_COOKIE);
