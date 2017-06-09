<?php

require_once "back/bootstrap.php";

use Controller\IndexController;

$c = new IndexController($_POST, $_SESSION, $_COOKIE);
?>

<!DOCTYPE html>
<html
    lang='<?= $c->getUserLanguage(); ?>'
    login='<?= $c->getUserLogin(); ?>'
>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>Luch</title>
    <link href='/front/stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
</head>
<body>
    <div id='root'><div>

    <?php $c->PutScripts(); ?>
</body>
</html>
