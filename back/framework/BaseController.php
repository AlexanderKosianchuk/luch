<?php

namespace Controller;

use \Framework\Application as App;

use \Exception\NotFoundException;
use \Exception\ForbiddenException;

use \Exception;
use \ReflectionClass;

class BaseController
{
    public function callAction($method, $arguments = []) {
        $fullAction = get_class($this).'\\'.$method;
        $userId = App::user()->getId();
        $rr = App::dic()->get('responseRegistrator');

        if (!method_exists($this, $method)) {
            $rr->faultResponse('unknown', 400, 'Unknown action: ' . $fullAction, $userId);

            throw new NotFoundException(
                'action ' . $method . ' not found.'
            );
        }

        error_log(json_encode(App::rbac()->check($method)));
        error_log(json_encode($userId));

        if (!App::rbac()->check($method)) {
            throw new ForbiddenException(
                'action ' . $method . ' execution forbidden for user with current privilege.'
            );
        }

        try {
            $response = call_user_func_array([$this, $method], $arguments);
            $rr->register($userId, $fullAction);
        } catch (BadRequestException $exception) {
            $rr->faultResponse($method, 400, $exception->message, $exception->forwardingDescription);
        } catch (UnauthorizedException $exception) {
            $rr->faultResponse($method, 401, $exception->message, $exception->forwardingDescription);
        } catch (NotFoundException $exception) {
            $rr->faultResponse($method, 404, $exception->message, $exception->forwardingDescription);
        } catch (ForbiddenException $exception) {
            $rr->faultResponse($method, 403, $exception->message, $exception->forwardingDescription);
        } catch (DriverException $exception) {
            $rr->faultResponse($method, 500, $exception->getMessage());
        } catch (BadMethodCallException $exception) {
            $rr->faultResponse($method, 500, $exception->getMessage());
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

            $rr->faultResponse($method, 500, $message);
        }

        return $response;
    }
}
