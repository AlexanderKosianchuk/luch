<?php

namespace Component;

use Exception;

class Rbac extends BaseComponent
{
  private $_allActions;
  private $_acl;
  private $_role;

  public function init($acl, $userRole)
  {
    $this->_allActions = $acl['actions'];
    $this->_acl = $acl['tree'];
    $this->_role = $userRole;
  }

  public function check($action, $role = null)
  {
    $acl = $this->_acl;
    $allActions = $this->_allActions;

    /*
     * If action not in all action list - it's public
     */
    if (!in_array($action, $allActions)) {
      return true;
    }

    if ($role === null) {
      $role = $this->_role;
    }

    if (!isset($acl[$this->_role])) {
      return false;
    }

    $allowedActions = $acl[$this->_role];
    if (!in_array($action, $allowedActions)) {
      return false;
    }

    return true;
  }
}
