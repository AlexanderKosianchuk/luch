<?php

require_once(@__DIR__."/includes.php");
require_once(@__DIR__."/controller/IndexController.php");

$c = new IndexController($_POST, $_SESSION);

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

    $c->PutFooter();
    $c->PutScripts();
} else {
    $c->PutCharset();
    $c->PutTitle();
    $c->PutStyleSheets();

    $c->PutHeader();

    $c->ShowLoginForm();

    $c->PutFooter();
}
