<?php

require_once "back/bootstrap.php";

new \Controller\EntryController($_POST, $_SESSION, $_COOKIE);
