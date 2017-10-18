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

    public static function isAdmin($userRole) {
        if($userRole == self::$role['admin']) {
            return true;
        }

        return false;
    }

    public static function isModerator($userRole) {
        if($userRole == self::$role['moderator']) {
            return true;
        }

        return false;
    }

    public static function isUser($userRole) {
        if($userRole == self::$role['user']) {
            return true;
        }

        return false;
    }

    public static function isLocal($userRole) {
        if($userRole == self::$role['local']) {
            return true;
        }

        return false;
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

        $this->clearAuthTokens($id);

        $autologin = isset($cookie['token'])
            ? true
            : false;

        $this->setToken($id, $autologin);

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
            setcookie('token', $token);
        }

        return $token;
    }

    public function GetUserIdByName($requester)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $result = $link->query("SELECT `id` FROM `user_personal` WHERE `login`='".$requester."' LIMIT 1;");

        $row = $result->fetch_array();
        $userId = intval($row['id']);

        $c->Disconnect();
        unset($c);

        return $userId;
    }

    public function GetUserNameById($requester)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $result = $link->query("SELECT `login` FROM `user_personal` WHERE `id`='".$requester."' LIMIT 1;");

        $row = $result->fetch_array();
        $userLogin = $row['login'];

        $c->Disconnect();
        unset($c);

        return $userLogin;
    }

    public function GetUserInfo($userIdentity)
    {
        $userId = $userIdentity;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $result = $link->query("SELECT * FROM `user_personal` WHERE `id`='".$userId."' LIMIT 1;");

        $userInfo = array();
        if ($row = $result->fetch_array()) {
            foreach ($row as $key => $val) {
                $userInfo[$key] = $val;
            }
        }

        $c->Disconnect();
        unset($c);

        return $userInfo;
    }

    public function GetUsersInfo($username)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $userInfo = [];
        $result = $link->query("SELECT * FROM `user_personal` WHERE
                `login` = '".$username."';");

        if ($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                $userInfo[$key] = $value;
            }
        }

        $c->Disconnect();
        unset($c);

        return $userInfo;
    }

    public function CreateUserPersonal($data)
    {
        $loadFile = '';
        if (strlen($data['logo']) > 0) {
            $loadFile = "LOAD_FILE('".$data['logo']."'),";
        }

        $query = "INSERT INTO `user_personal` ("
            ."`login`,"
            ."`pass`,"
            ."`name`,"
            ."`email`,"
            ."`phone`,"
            ."`lang`,"
            ."`role`,"
            ."`company`,"
            ."`logo`,"
            ."`id_creator`"
        .") VALUES ("
            ."'".$data['login']."',"
            ."'".md5($data['pass'])."',"
            ."'".$data['name']."',"
            ."'".$data['email']."',"
            ."'".$data['phone']."',"
            ."'en',"
            ."'".$data['role']."',"
            ."'".$data['company']."',"
            .$loadFile
            .$data['id_creator'].");";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        if ($stmt = $link->prepare($query)) {
            $stmt->execute();
            $stmt->close();

            $query = "SELECT LAST_INSERT_ID() AS 'id';";
            $result = $link->query($query);
            $row = $result->fetch_array();
            return intval($row["id"]);
        }

        return null;
    }

    public function UpdateUserPersonal($uId, $data)
    {
        if(!is_array($data)) {
            return false;
        }

        $set = '';
        foreach ($data as $key => $val) {
            if($key == 'logo') {
                $set .= "`".$key."` = LOAD_FILE('".$val."'), ";
            } else {
                $set .= "`".$key."` = '".$val."', ";
            }
        }
        $set = substr($set, 0, -2);
        $query = "UPDATE `user_personal` SET ".$set." WHERE `id` = ".$uId.";";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $stmt = $link->prepare($query);
        $stmt->execute();
        $msg = $stmt;

        $stmt->close();

        return $msg;
    }

    public function DeleteUserPersonal($extLogin)
    {
        $login = $extLogin;

        $query = "DELETE FROM `user_personal` WHERE `login` = '".$login."';";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $stmt = $link->prepare($query);
        $stmt->execute();
        $msg = $stmt;

        $stmt->close();

        return $msg;
    }

    public function CheckUserPersonalExist($login)
    {
        $query = "SELECT `login` FROM `user_personal` WHERE `login` = '".$login."';";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $exist = false;
        $result = $link->query($query);
        if($row = $result->fetch_array())
        {
            $exist = true;
        }

        $c->Disconnect();
        unset($c);

        return $exist;
    }

    public function GetUsersByAuthor($authorId)
    {
        if (!is_int($authorId)) {
            throw new Exception("Incorrect authorId passed. Integer is required. Passed: "
                . json_encode($authorId), 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id` FROM `user_options` WHERE `id_user` = '".$authorId."';";
        $mySqliResult = $link->query($query);

        $list = [];
        while($row = $mySqliResult->fetch_array()) {
            $list[] = $row['id'];
        }
        $mySqliResult->free();
        $c->Disconnect();

        unset($c);

        return $list;
    }

    /** ------------------------
     *  GET AVALIABLE
     */

    public function getAvailableFdrs($userId)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $availableItems = [];

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $q = "SELECT `id_fdr` FROM `fdr_to_user`"
                ." WHERE `id_user`=?;";

        $stmt = $link->prepare($q);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while (($result !== null) && ($row = $result->fetch_array())) {
            $availableItems[] = $row['id_fdr'];
        }

        $c->Disconnect();
        unset($c);

        return $availableItems;
    }

    public function checkFdrAvailable($fdrId, $userId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int expected. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Int expected. Passed: "
                . json_encode($userId), 1);
        }

        $isAvaliable = false;

        $q = "SELECT `id_fdr` FROM `fdr_to_user` "
            ."WHERE `id_fdr` = ? AND `id_user` = ?;";

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $stmt = $link->prepare($q);
        $stmt->bind_param("ii", $fdrId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_array()) {
            $isAvaliable = true;
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $isAvaliable;
    }

    public function GetAvailableUsers($userId, $role = null)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Integer is required. Passed: "
                . json_encode($userId) , 1);
        }

        if(empty($role)) {
            $userInfo = $this->GetUserInfo($userId);
            $role = $userInfo['role'];
        }

        $availableUsers = [];
        if (self::isAdmin($role)) {
            $availableUsers = $this->GetUsersByRole([
                self::$role['moderator'],
                self::$role['user'],
                self::$role['local']
            ]);
        } else if (self::isModerator($role)) {
            $availableUsers = $this->GetUsersByAuthor($userId);
        }

        return $availableUsers;
    }

    public function GetAvailableUsersList($userId, $role = null)
    {
        $availableUserIds = $this->GetAvailableUsers($userId, $role);
        $users = [];

        foreach ($availableUserIds as $id) {
            $users[] = $this->GetUserInfo($id);
        }

        return $users;
    }

    public function SetFDRavailable($userId, $fdrId)
    {
        if (!is_int($userId)) {
            throw new Exception("Incorrect userId passed. Integer is required. Passed: "
                . json_encode($userId) , 1);
        }

        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Integer is required. Passed: "
                . json_encode($fdrId) , 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "INSERT INTO `fdr_to_user` (`id_fdr`, `id_user`)
                VALUES ('".$fdrId."', '".$userId."');";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function UnsetFDRavailable($userId = null, $fdrId = null)
    {
        /* Cant be both empty*/
        if (!is_int($userId) && !is_int($fdrId)) {
            throw new Exception("Incorrect userId passed. Integer is required. Passed: "
                . json_encode($userId) , 1);
        }

        $c = new DataBaseConnector;
        $link = $c->Connect();

        if (is_int($userId) && !is_int($fdrId)) {
            /* maybe user deleting so remove */
            $query = "DELETE FROM `fdr_to_user` WHERE `id_user` = '".$userId."';";
        } else if (!is_int($userId) && is_int($fdrId)) {
            $query = "DELETE FROM `fdr_to_user` WHERE `id_fdr` = '".$fdrId."';";
        } else {
            $query = "DELETE FROM `fdr_to_user` WHERE `id_user` = '".$userId."' "
                . "AND `id_fdr` = '".$fdrId."';";
        }

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    /** ------------------------ **/

    public function GetUserIdsByAuthor($username)
    {
        $availableUserIds = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $result = $link->query("SELECT `id` FROM `user_personal` ".
                "WHERE `author`='".$username."';");

        while($row = $result->fetch_array())
        {
            $availableUserIds[] = $row['id'];
        }

        $c->Disconnect();
        unset($c);

        return $avaliabeUsers;
    }

    public function SetUserLanguage($login, $lang)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "UPDATE `user_personal` SET `lang` = '".$lang."'
                WHERE `login` = '".$login."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function GetObservers($userId)
    {
        $observerIds = [];

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $userInfo = $this->GetUserInfo(intval($userId));
        $authorId = $userInfo['id_creator'];

        /* in case current user is admin role it will not have id_user (authorId) */
        if (isset($authorId)) {
            $authorId = intval($authorId);
            $authorInfo = $this->GetUserInfo($authorId);
            if (self::isModerator($authorInfo['role'])) {
                $observerIds[] = $authorId;
            }
        }

        $admins = $this->GetUsersByRole(self::$role['admin']);
        $observerIds = array_merge($observerIds, $admins);

        $observerIds = array_unique($observerIds);

        $c->Disconnect();
        unset($c);

        return $observerIds;
    }

    public function GetUsersByRole($role)
    {
        $userIds = [];

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = '';
        if (is_array($role)) {
            $query = "SELECT `id` FROM `user_personal` WHERE ";

            for($ii = 0; $ii < count($role); $ii++) {
                $query .= ($ii === 0)
                    ? "`role`='".$role[$ii]."' "
                    : "OR `role`='".$role[$ii]."' ";
            }
            $query .= ";";
        } else if (is_string($role)) {
            $query = "SELECT `id` FROM `user_personal` "
                . "WHERE `role`='".$role."';";
        }

        $result = $link->query($query);

        while($row = $result->fetch_array()) {
            $userIds[] = intval($row['id']);
        }

        $c->Disconnect();
        unset($c);

        return $userIds;
    }
}
