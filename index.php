<?php

require_once "back/bootstrap.php";

use Controller\IndexController;

$c = new IndexController($_POST, $_SESSION, $_COOKIE);
?>

<!DOCTYPE html>
<html lang='<?= $c->getUserLanguage(); ?>' login='<?= $c->getUserLogin(); ?>'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title><?= $c->lang->title ?></title>
    <link href='/front/stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
</head>
<body>
<?php if ($c->_user && ($c->_user->username !== null)): ?>

    <div id='root'><div>

    <?php
        $c->EventHandler();

        $c->PutMessageBox();
        $c->PutHelpDialog();
        $c->PutOptionsDialog();
        $c->PutExportLink();

        $c->PutScripts();
    ?>
<?php else: ?>
    <?php $c->ShowLoginForm(); ?>
<?php endif; ?>

</body>
</html>
