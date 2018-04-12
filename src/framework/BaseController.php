<?php

namespace Controller;

use \Framework\Application as App;
use \Component\BaseComponent;

use \Exception\NotFoundException;
use \Exception\ForbiddenException;

use \Exception;
use \ReflectionClass;

class BaseController extends BaseComponent
{
  protected function dic($componentName = null) {
    if ($componentName === null) {
      return App::dic();
    }

    return App::dic()->get($componentName);
  }

  public function callAction($method, $arguments = []) {
    $fullAction = get_class($this).'\\'.$method;
    $userId = $this->user()->getId();
    $rr = $this->dic('responseRegistrator');

    if (isset($_SERVER['HTTP_ORIGIN'])
      && in_array($_SERVER['HTTP_ORIGIN'], $this->params()->front->origins)
    ) {
      header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'], true);
      header('Access-Control-Allow-Credentials: true');
    }

    if (!method_exists($this, $method)) {
      $rr->faultResponse('unknown', 400, 'Unknown action: ' . $fullAction, $userId);

      throw new NotFoundException(
        'action ' . $method . ' not found.'
      );
    }

    if (!$this->rbac()->check($fullAction)) {
      throw new ForbiddenException(
        'action ' . $method . ' execution forbidden for user with current privilege.'
      );
    }

    $fire_args=array();

    $reflection = new \ReflectionMethod($this, $method);
    $fireArgs = [];

    foreach($reflection->getParameters() as $arg) {
      if (isset($arguments[$arg->name])) {
        $fireArgs[$arg->name]=$arguments[$arg->name];
      }
    }

    try {
      $response = call_user_func_array([$this, $method], $fireArgs);
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
