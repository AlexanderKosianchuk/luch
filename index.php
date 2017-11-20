<?php

require_once 'back/bootstrap.php';

use \Controller\IndexController;

$pathname = trim($_SERVER['REQUEST_URI'], '/');
$exp = explode('/', $pathname);

$indexAction = function () {
    $c = new IndexController();
    echo $c->callAction('indexAction');
    exit;
};

if (count($exp) <= 1) {
    $indexAction();
}

$controller = ucfirst($exp[0] . 'Controller');
if (!file_exists(SITE_ROOT_DIR."/back/controller/".$controller.'.php')) {
    $indexAction();
}

require_once(SITE_ROOT_DIR."/back/controller/".$controller.'.php');

$controller = 'Controller\\' . $controller;
$method = $exp[1] . 'Action';
$c = new $controller;
if (!method_exists ($c, $method)) {
    $indexAction();
}

$data = [];

if (count($exp) === 3) {
    $data = $exp[2];
}

if (count($exp) > 3) {
    for ($ii = 2; $ii < count($exp); $ii+=2) {
        $data[$exp[$ii]] = $exp[$ii+1] ?? '';
    }
}

$data = array_merge(
    $data,
    $_POST,
    $_GET
);

$safeData = [];

$stripSlashes = function($itemValue) {
    $itemValue = htmlspecialchars($itemValue, ENT_IGNORE, 'utf-8');
    $itemValue = strip_tags($itemValue);
    return stripslashes($itemValue);
};

$recursivePush = function($array) use ($stripSlashes, &$recursivePush) {
    if (is_array($array)) {
        $input = [];
        foreach ($array as $itemKey => $itemValue) {
            $input[$itemKey] = $recursivePush($itemValue);
        }

        return $input;
    } else {
        return $stripSlashes($array);
    }
};

foreach (array_keys($data) as $key) {
    $input = $recursivePush($data[$key]);
    $safeData[$key] = $input;
}

echo $c->callAction($method, $safeData);
