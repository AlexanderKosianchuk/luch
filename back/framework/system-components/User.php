<?php

namespace Component;

use \Entity\UserAuth;

use Exception;

class User extends BaseComponent
{
  public static $role = [
    'admin' => 'admin',
    'moderator' => 'moderator',
    'user' => 'user',
    'local' => 'local'
  ];

  public function isA($roleToCompareWith, $userRole = null)
  {
    if ($userRole == null) {
      $userRole = $this->user()->getRole();
    }

    if (!isset(self::$role[$roleToCompareWith])) {
      throw new Exception("Checking role unexist. Role: ".$roleToCompareWith, 1);
    }

    if ($userRole === self::$role[$roleToCompareWith]) {
      return true;
    }

    return false;
  }

  public function isAdmin($userRole = null)
  {
    return $this->isA('admin', $userRole);
  }

  public function isModerator($userRole = null)
  {
    return $this->isA('moderator', $userRole);
  }

  public function isUser($userRole = null)
  {
    return $this->isA('user', $userRole);
  }

  public function isLocal($userRole = null)
  {
    $this->isA('local', $userRole);
  }

  public function signIn($login, $pass, $autologin = true)
  {
    $user = $this->em()->getRepository('Entity\User')->findOneBy([
      'login' => $login,
      'pass' => md5($pass)
    ]);

    if (!isset($user)) {
      return null;
    }

    $userId = $user->getId();
    $this->setToken($userId, $autologin);

    return $userId;
  }

  public function tryAuth($session, $cookie)
  {
    $token = isset($session['token'])
      ? $session['token']
      : (
        isset($cookie['token'])
        ? $cookie['token']
        : null
      );

    if (!isset($token)) {
       return null;
    }

    $id = $this->getIdByToken($token);

    if ($id === null) {
      return null;
    }

    $user = $this->em()->find('Entity\User', $id);

    if (session_status() == PHP_SESSION_NONE) session_start();
    $_SESSION['uid'] = $id;
    $_SESSION['login'] = $user->getLogin();
    $_SESSION['lang'] = $user->getLang();
    session_write_close();

    return $id;
  }

  private function getIdByToken($token)
  {
    $userAuth = $this->em()->getRepository('Entity\UserAuth')->findOneBy([
      'token' => $token
    ]);

    if (!$userAuth) {
      return null;
    }

    $id = $userAuth->getUserId();

    $diff = date_diff(new \DateTime(), $userAuth->getExp());

    // token expired
    if (intval($diff->format('%R%a')) < 0) {
      $this->logout($id);
      return null;
    }

    // half expiration, lets update
    if (intval($diff->format('%R%a')) > 15) {
      $userAuth->setExp(new \DateTime("+ 1 month"));
      $this->em()->merge($userAuth);
      $this->em()->flush();
    }

    return $id;
  }

  public function logout($id = null)
  {
    if ($id === null) {
      $id = $this->user()->getId();
    }

    if (session_status() == PHP_SESSION_NONE) session_start();
    unset($_SESSION['uid']);
    unset($_SESSION['login']);
    unset($_SESSION['token']);
    session_write_close();

    setcookie('token', null, -1, '/');

    $this->clearAuthTokens($id);
  }

  private function clearAuthTokens($id)
  {
    $userAuthArr = $this->em()->getRepository('Entity\UserAuth')->findAll([
      'id_user' => $id
    ]);

    foreach ($userAuthArr as $item) {
      $this->em()->remove($item);
      $this->em()->flush();
    }
  }

  private function setToken($id, $autologin = true)
  {
    $token = uniqid();

    $userAuth = new UserAuth();
    $userAuth->setUserId($id);
    $userAuth->setToken($token);
    $userAuth->setExp(new \DateTime("+ 1 month"));

    $this->em()->persist($userAuth);
    $this->em()->flush();

    if (session_status() == PHP_SESSION_NONE) session_start();
    $_SESSION['token'] = $token;
    session_write_close();

    if ($autologin) {
      setcookie('token', $token, time()+60*60*24*30, '/');
    }

    return $token;
  }

  private function setLanguage($lang, $id = null)
  {
    $user = $this->user();
    if ($id !== null) {
      $user = $this->em()->find('Entity\User', $id);
    }

    if (!$user) {
      return null;
    }

    $user->setLanguage($lang);
    $this->em()->merge($user);
    $this->em()->flush();

    return $id;
  }
}
