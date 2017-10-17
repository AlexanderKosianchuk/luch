<?php

require_once 'back/bootstrap.php';

use \Controller\IndexController;
use \Framework\Application as App;

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service) {
    $pathname = trim($request->pathname(), '/');
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
    $fullAction = get_class($c) . '\\' . $method;
    $data = [];

    if (count($exp) === 3) {
        $data = $exp[2];
    }

    if (count($exp) > 3) {
        for ($ii = 2; $ii < count($exp); $ii+=2) {
            $data[$exp[$ii]] = $exp[$ii+1];
        }
    }

    $data = array_merge(
        $data,
        $_POST
    );

    if (!method_exists ($c, $method)) {
        $indexAction();
    }

    echo $c->callAction($method, $data);
    exit;
});

$klein->dispatch();
