<?php

namespace Component;

use Exception;

class ResponseRegistrator extends BaseComponent
{
    /**
     * @Inject
     * @var \Entity\UserActivity
     */
    private $UserActivity;

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
        /*
        * If fatal error EM closes by Doctrine
        * as far fatal is posible only in development
        * wont reopen EM to write register it
        */
        if (!$this->em()->isOpen()) {
            return;
        }

        $userActivity = new $this->UserActivity;
        $userActivity->setAttributes([
            'action' => $action,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'userId' => intval($this->user()->getId())
        ]);

        $this->em()->persist($userActivity);
        $this->em()->flush();
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
