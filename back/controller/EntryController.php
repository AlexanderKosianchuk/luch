<?php

namespace Controller;

use Component\ResponseRegistrator;
use Exception\UnknownActionException;
use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Doctrine\DBAL\Exception\DriverException;

use Exception;
use ReflectionClass;

class EntryController extends CController
{
    function __construct()
    {
        $this->setAttributes();

        if (!$this->IsAppLoggedIn()
            && ($this->action !== 'users/login')
        ) {
            echo (json_encode('Auth failed'));
            exit;
        }

        if (strpos($this->action, '/') !== false) {
            $exp = explode('/', $this->action);
            $controller = ucfirst($exp[0] . 'Controller');
            $method = $exp[1];

            if (file_exists(@SITE_ROOT_DIR."/controller/".$controller.'.php')) {
                require_once(@SITE_ROOT_DIR."/controller/".$controller.'.php');

                $controller = 'Controller\\' . $controller;
                $C = new $controller;
                $C->action = $this->action;
                $userId = isset($this->_user->userInfo['id']) ? intval($this->_user->userInfo['id']) : -1;
                $fullAction = get_class($C).'\\'.$method;

                if (method_exists ($C, $method)) {
                    $C->IsAppLoggedIn();
                    try {
                        $response = $C->$method($this->data);
                        ResponseRegistrator::register($userId, $fullAction);
                        echo($response);
                        exit;
                    } catch (BadRequestException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 400, $exception->message);
                    } catch (UnauthorizedException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 401, $exception->message);
                    } catch (NotFoundException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 404, $exception->message);
                    } catch (ForbiddenException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 403, $exception->message);
                    } catch (DriverException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 500, $exception->getMessage());
                    } catch (BadMethodCallException $exception) {
                        ResponseRegistrator::faultResponse($userId, $fullAction, 500, $exception->getMessage());
                    } catch (Exception $exception) {
                        $message = 'Unknown error';

                        $class = new ReflectionClass($exception);
                        $property = $class->getProperty('message');

                        if (property_exists($exception, 'message')
                        && $property->isPublic()) {
                            $message = $exception->message;
                        } else if (method_exists($exception, 'getMessage')) {
                            $message = $exception->getMessage();
                        }

                        ResponseRegistrator::faultResponse($userId, $fullAction, 500, $message);
                    }
                } else {
                    ResponseRegistrator::faultResponse('unknown', 400, 'Unknown action: ' . $fullAction, $userId);
                }
            }
        }

        exit (0);
    }
}
