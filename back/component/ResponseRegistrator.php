<?php

namespace Component;

use Entity\UserActivity;

use Component\EntityManagerComponent as EM;

use Exception;

class ResponseRegistrator
{
    private static $codes = [
        '200' => 'OK',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '500' => 'Internal Server Error'
    ];

    public static function register($userId, $action, $message = 'ok', $status = 'executed', $code = 200)
    {
        $em = EM::get();

        $userActivity = new UserActivity;
        $userActivity->setAttributes([
            'action' => $action,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'userId' => $userId
        ]);

        $em->persist($userActivity);
        $em->flush();
    }

    public static function faultResponse($userId, $action, $code, $message)
    {
        $code = strval($code);
        self::register($userId, $action, $message, 'rejected', $code);
        http_response_code($code);
        header($code . ' ' . self::$codes[strval($code)]);
        echo(json_encode($message));
        exit;
    }
}
