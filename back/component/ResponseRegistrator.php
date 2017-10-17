<?php

namespace Component;

use \Framework\Application as App;
use \Entity\UserActivity;

use Exception;

class ResponseRegistrator extends BaseComponent
{
    private static $codes = [
        '200' => 'OK',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '500' => 'Internal Server Error'
    ];

    public function register($action, $message = 'ok', $status = 'executed', $code = 200)
    {
        $em = App::em();

        $userActivity = new UserActivity;
        $userActivity->setAttributes([
            'action' => $action,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'userId' => intval(App::user()->getId())
        ]);

        $em->persist($userActivity);
        $em->flush();
    }

    public function faultResponse($action, $code, $message, $forwardingDescription = null)
    {
        $code = strval($code);
        $this->register($action, $message, 'rejected', $code);
        http_response_code($code);
        header($code . ' ' . self::$codes[strval($code)]);
        if (!isset($forwardingDescription)) {
            echo(json_encode($message));
            exit;
        }

        echo(json_encode([
            'message' => $message,
            'forwardingDescription' => $forwardingDescription
        ]));
        exit;
    }
}
