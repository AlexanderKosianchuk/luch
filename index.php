<?php

require_once "back/bootstrap.php";

use Controller\IndexController;

$c = new IndexController($_POST, $_SESSION, $_COOKIE);
?>

<!DOCTYPE html>
<html
    lang='<?= $c->getUserLanguage(); ?>'
    login='<?= $c->getUserLogin(); ?>'
    avaliable-languages='<?= $c->getAvaliableLanguages(); ?>'
>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title><?= $c->lang->title ?></title>
    <link href='/front/stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
</head>
<body>
    <div id='root'><div>

    <?php
        $c->EventHandler();

        $c->PutMessageBox();
        $c->PutHelpDialog();
        $c->PutExportLink();

        $c->PutScripts();
    ?>
</body>
</html>
