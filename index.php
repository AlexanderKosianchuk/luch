<?php

require_once "bootstrap.php";

use Controller\IndexController;

$c = new IndexController($_POST, $_SESSION, $_COOKIE);

if ($c->_user && ($c->_user->username !== null)) {
    $c->PutCharset();
    $c->PutTitle();
    $c->PutStyleSheets();

    $c->PutHeader();
    $c->EventHandler();

    $c->PutMessageBox();
    $c->PutHelpDialog();
    $c->PutOptionsDialog();
    $c->PutExportLink();

    $c->PutScripts();
    $c->PutFooter();
} else {
    $c->PutCharset();
    $c->PutTitle();
    $c->PutStyleSheets();

    $c->PutHeader();

    $c->ShowLoginForm();

    $c->PutFooter();
}
