<?php

require_once(@SITE_ROOT_DIR ."/includes.php");

//User privilege
//------------

/*
viewFlight,shareFlight,addFlight,editFlight,delFlight,followFlight,tuneFlight,
viewBruType,shareBruType,addBruType,editBruType,delBruType,
optionsUsers,viewUsers,shareUsers,addUser,delUser,editUser
*/

class User
{
    public $username;
    public $userInfo;
    public $privilege;

    public static $role = [
        'admin' => 'admin',
        'moderator' => 'moderator',
        'user' => 'user'
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

    public static $AVALIABILITY_FLIGHTS = 'flight';
    public static $AVALIABILITY_FDR_TYPES = 'brutype';
    public static $AVALIABILITY_USERS = 'user';

    public static $PRIVILEGE_VIEW_FLIGHTS = 'viewFlight';
    public static $PRIVILEGE_SHARE_FLIGHTS = 'shareFlight';
    public static $PRIVILEGE_ADD_FLIGHTS = 'addFlight';
    public static $PRIVILEGE_EDIT_FLIGHTS = 'editFlight';
    public static $PRIVILEGE_DEL_FLIGHTS = 'delFlight';
    public static $PRIVILEGE_FOLLOW_FLIGHTS = 'followFlight';
    public static $PRIVILEGE_TUNE_FLIGHTS = 'tuneFlight';

    public static $PRIVILEGE_VIEW_BRUTYPES = 'viewBruType';
    public static $PRIVILEGE_SHARE_BRUTYPES = 'shareBruType';
    public static $PRIVILEGE_ADD_BRUTYPES = 'addBruType';
    public static $PRIVILEGE_EDIT_BRUTYPES = 'editBruType';
    public static $PRIVILEGE_DEL_BRUTYPES = 'delBruType';

    public static $PRIVILEGE_OPTIONS_USERS = 'optionsUsers';
    public static $PRIVILEGE_VIEW_USERS = 'viewUsers';
    public static $PRIVILEGE_SHARE_USERS = 'shareUsers';
    public static $PRIVILEGE_ADD_USERS = 'addUser';
    public static $PRIVILEGE_DEL_USERS = 'delUser';
    public static $PRIVILEGE_EDIT_USERS = 'editUser';

    public $allPrivilegeArray;
    public $flightPrivilegeArr;
    public $bruTypesPrivilegeArr;
    public $userPrivilegeArr;

    public function  __construct()
    {
        $this->SetAllPrivilegeArr();
        $this->SetFlightPrivilegeArr();
        $this->SetBruTypesPrivilegeArr();
        $this->SetUserPrivilegeArr();

        //$this->CreateUsersTables();
    }

    private function SetAllPrivilegeArr()
    {
        $this->allPrivilegeArray = array(
            $this::$PRIVILEGE_VIEW_FLIGHTS,
            $this::$PRIVILEGE_SHARE_FLIGHTS,
            $this::$PRIVILEGE_ADD_FLIGHTS,
            $this::$PRIVILEGE_EDIT_FLIGHTS,
            $this::$PRIVILEGE_DEL_FLIGHTS,
            $this::$PRIVILEGE_FOLLOW_FLIGHTS,
            $this::$PRIVILEGE_TUNE_FLIGHTS,

            $this::$PRIVILEGE_VIEW_BRUTYPES,
            $this::$PRIVILEGE_SHARE_BRUTYPES,
            $this::$PRIVILEGE_ADD_BRUTYPES,
            $this::$PRIVILEGE_EDIT_BRUTYPES,
            $this::$PRIVILEGE_DEL_BRUTYPES,

            $this::$PRIVILEGE_OPTIONS_USERS,
            $this::$PRIVILEGE_VIEW_USERS,
            $this::$PRIVILEGE_SHARE_USERS,
            $this::$PRIVILEGE_ADD_USERS,
            $this::$PRIVILEGE_DEL_USERS,
            $this::$PRIVILEGE_EDIT_USERS

            );
    }

    private function SetFlightPrivilegeArr()
    {
        $this->flightPrivilegeArr = array(
                $this::$PRIVILEGE_VIEW_FLIGHTS,
                $this::$PRIVILEGE_SHARE_FLIGHTS,
                $this::$PRIVILEGE_ADD_FLIGHTS,
                $this::$PRIVILEGE_EDIT_FLIGHTS,
                $this::$PRIVILEGE_DEL_FLIGHTS,
                $this::$PRIVILEGE_FOLLOW_FLIGHTS,
                $this::$PRIVILEGE_TUNE_FLIGHTS);
    }

    private function SetBruTypesPrivilegeArr()
    {
        $this->bruTypesPrivilegeArr = array(
                $this::$PRIVILEGE_VIEW_BRUTYPES,
                $this::$PRIVILEGE_SHARE_BRUTYPES,
                $this::$PRIVILEGE_ADD_BRUTYPES,
                $this::$PRIVILEGE_EDIT_BRUTYPES,
                $this::$PRIVILEGE_DEL_BRUTYPES);
    }

    private function SetUserPrivilegeArr()
    {
        $this->userPrivilegeArr = array(
            $this::$PRIVILEGE_OPTIONS_USERS,
            $this::$PRIVILEGE_VIEW_USERS,
            $this::$PRIVILEGE_SHARE_USERS,
            $this::$PRIVILEGE_ADD_USERS,
            $this::$PRIVILEGE_DEL_USERS,
            $this::$PRIVILEGE_EDIT_USERS);
    }

    public function CheckPrivilege($extInputPrivilege)
    {
        $inputPrivilege = $extInputPrivilege;

        $privilegeString = "";
        if(count($inputPrivilege) == count($this->allPrivilegeArr))
        {
            $privilegeString = "AllGrant";
        }
        else
        {
            if(count(array_intersect($inputPrivilege, $this->flightPrivilegeArr)) == count($this->flightPrivilegeArr))
            {
                $privilegeString .= "FlightGrant";
                $inputPrivilege = array_diff($inputPrivilege, $this->flightPrivilegeArr);
            }

            if(count(array_intersect($inputPrivilege, $this->bruTypesPrivilegeArr)) == count($this->bruTypesPrivilegeArr))
            {
                $privilegeString .= "; BruTypesGrant";
                $inputPrivilege = array_diff($inputPrivilege, $this->bruTypesPrivilegeArr);
            }

            if(count(array_intersect($inputPrivilege, $this->userPrivilegeArr)) == count($this->userPrivilegeArr))
            {
                $privilegeString .= "; UsersGrant";
                $inputPrivilege = array_diff($inputPrivilege, $this->userPrivilegeArr);
            }

            if(!empty($inputPrivilege))
            {
                if(strlen($privilegeString) > 0)
                {
                    $privilegeString .= "; ";
                }

                $privilegeString .= implode(", ", $inputPrivilege);
            }
        }

        return $privilegeString;
    }

    public function CreateUsersTables()
    {
        $query = "SHOW TABLES LIKE 'user_personal';";
        $c = new DataBaseConnector();
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `user_personal` (
                `id` BIGINT NOT NULL AUTO_INCREMENT,
                `login` VARCHAR(200),
                `privilege` TINYTEXT,
                `options` TEXT,
                `role` VARCHAR(255),
                `author` VARCHAR(200) DEFAULT ' ',
                `logo` MEDIUMBLOB,
                PRIMARY KEY (`id`));";
            $stmt = $link->prepare($query);
            if (!$stmt->execute())
            {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }

        $query = "SHOW TABLES LIKE 'user_avaliability';";
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `user_avaliability` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(100),
                `userId` INT(11),
                `targetId` INT(11),
                `allowedBy` VARCHER(255),
                PRIMARY KEY (`id`));";
            $stmt = $link->prepare($query);
            if (!$stmt->execute())
            {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }

        $query = "SHOW TABLES LIKE 'user_activity';";
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `user_activity` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `userId` INT(11),
                `acton` VARCHAR(255),
                `target` VARCHAR(255),
                `sender` VARCHAR(255),
                `timestamp` VARCHAR(100),
                `comment` VARCHAR(255),
                PRIMARY KEY (`id`));";
            $stmt = $link->prepare($query);
            if (!$stmt->execute())
            {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }


        $query = "SHOW TABLES LIKE 'user_auth';";
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `user_auth` (
                  `id` int(11) NOT NULL,
                  `id_user` int(11) NOT NULL,
                  `token` varchar(45) NOT NULL,
                  `exp` datetime NOT NULL,
                  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $stmt = $link->prepare($query);
            if (!$stmt->execute())
            {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }

        $c->Disconnect();
        unset($c);
    }

    public function tryAuth($post, $session)
    {
        $token = $session['token'] ?? null;
        $userId = null;
        $authSuccess = false;

        if (isset($token)) {
             $userId = $this->getUserIdByToken($token);
        }

        $username = $post['user'] ?? null;
        $pass = $post['pwd'] ?? null;

        if (!$userId
            && isset($username)
            && isset($pass)
        ) {
            $userId = $this->checkUserPass($username, $pass);
        }

        if ($userId) {
            $this->userInfo = $this->GetUserInfo(intval($userId));

            $_SESSION['uid'] = $this->userInfo['id'];
            $_SESSION['username'] = $this->userInfo['login'];

            $this->username = $this->userInfo['login'];
            $this->privilege = $this->GetUserPrivilege($this->username);
        }

        if (!$userId
            && (isset($username) || isset($pass))
        ) {
            $this->loginMsg = 'Fail to login. Check username and password and try again';
        }

        return $authSuccess;
    }

    private function getUserIdByToken($token)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `id_user` FROM `user_auth` WHERE `token`='".$token."' "
            . "AND `exp` > NOW() LIMIT 1;");

        $row = $result->fetch_array();

        $userId = null;
        if ($row['id_user']) {
            $userId = intval($row['id_user']);
        }

        $query = "DELETE FROM `user_auth` WHERE `id_user`='".$userId."' "
            . "AND `exp` < NOW();";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $msg = $stmt;

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $userId;
    }

    public function logout($username)
    {
        $userId = $this->GetUserIdByName($username);

        unset($_SESSION['uid']);
        unset($_SESSION['username']);
        unset($_SESSION['token']);

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `user_auth` WHERE `id_user`=".$userId.";";

        $stmt = $link->prepare($query);
        $stmt->execute();

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return;
    }

    private function checkUserPass($username, $pass)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `id` FROM `user_personal` WHERE `login`='".$username."' "
            . " AND `pass` = '".md5($pass)."' LIMIT 1;";

        $result = $link->query($query);

        $row = $result->fetch_array();

        $userId = null;
        if(isset($row['id'])) {
            $userId = $row['id'];
            $this->setToken($userId);
        }

        $c->Disconnect();
        unset($c);

        return $userId;
    }

    private function setToken($userId)
    {
        $token = uniqid();
        $query = "INSERT INTO `user_auth` (`id_user`, `token`, `exp`) VALUES (".$userId.", '".$token."', NOW()+INTERVAL 30 DAY);";

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $_SESSION['token'] = $token;

        return $token;
    }

    public function GetUserIdByName($requester)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `id` FROM `user_personal` WHERE `login`='".$requester."' LIMIT 1;");

        $row = $result->fetch_array();
        $userId = intval($row['id']);

        $c->Disconnect();
        unset($c);

        return $userId;
    }

    public function GetIdByUsername($username)
    {
        return $this->GetUserIdByName($username);
    }

    public function GetUserNameById($extRequester)
    {
        $requester = $extRequester;
        $c = new DataBaseConnector();
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
        if(is_string($userIdentity)) {
            $userId = $this->GetIdByUsername($userIdentity);
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT * FROM `user_personal` WHERE `id`='".$userId."' LIMIT 1;");

        $userInfo = array();
        if($row = $result->fetch_array())
        {
            foreach ($row as $key => $val)
            {
                $userInfo[$key] = $val;
            }
        }

        $c->Disconnect();
        unset($c);

        return $userInfo;
    }

    public function GetLastActionByActionName($extUserId, $extActionName)
    {
        $userId = $extUserId;
        $actionName = $extActionName;
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `*` FROM `user_activity` WHERE `userId`='".$userId."' AND `acton` = '".$actionName."' ORDER BY `id` ASC LIMIT 1;");

        $answer = array();
        $row = $result->fetch_array();
        foreach($row as $key => $val)
        {
            $answer[$key] = $val;
        }

        $c->Disconnect();
        unset($c);

        return $answer;
    }

    public function GetUsersList($userId)
    {
        $userName = $this->GetUserNameById($userId);
        $avalibleUsers = $this->GetAvaliableUsers($userName);
        return $this->GetUsersListByAvaliableIds($avalibleUsers);
    }

    public function GetUsersListByAvaliableIds($extAvaliableUsersIds)
    {
        $avaliableUsersIds = $extAvaliableUsersIds;

        $userInfoArr = array();
        if(count($avaliableUsersIds) > 0)
        {
            $inString = "";
            foreach($avaliableUsersIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $c = new DataBaseConnector();
            $link = $c->Connect();

            $result = $link->query("SELECT * FROM `user_personal` WHERE
                    `id` IN (".$inString.") ORDER BY `id`;");

            while($row = $result->fetch_array())
            {
                $userInfoArr[] = $row;
            }

            $c->Disconnect();
            unset($c);
        }

        return $userInfoArr;
    }

    public function GetUsersInfo($username)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT * FROM `user_personal` WHERE
                `login` = '".$username."';");

        if($row = $result->fetch_array())
        {
            foreach ($row as $key => $value)
            {
                $userInfo[$key] = $value;

                /*array("id"=>$row['id'],
                    "login"=>$row['login'],
                    "company"=>$row['company'],
                    "privilege"=>$row['privilege'],
                    "options"=>$row['options'],
                    "author"=>$row['author']);*/
            }
        }

        $c->Disconnect();
        unset($c);

        return $userInfo;
    }

    public function GetUserPrivilege($extUsername)
    {
        $username = $extUsername;

        if($username != '') {
            $c = new DataBaseConnector();
            $link = $c->Connect();

            $result = $link->query("SELECT `privilege` FROM `user_personal` WHERE `login`='".$username."' LIMIT 1;");

            $userInfo = array();
            $row = $result->fetch_array();
            $privilege = $row['privilege'];
            $privilege = explode(',', $privilege);

            $c->Disconnect();
            unset($c);

            return $privilege;
        } else {
            return [];
        }
    }

    public function CreateUserPersonal($login, $privilege, $author, $company, $role, $logo)
    {
        if(is_array($privilege)) {
            $privilege = implode(',', $privilege);
        }

        $query = "INSERT INTO `user_personal` (`login`,
                `privilege`,
                `author`,
                `company`,
                `lang`,
                `role`,
                `logo`)
                VALUES ('".$login."',
                        '".$privilege."',
                        '".$author."',
                        '".$company."',
                        'en',
                        '".$role."',
                        LOAD_FILE('".$logo."'));";

        $execInfo['query'] = $query;
        $execInfo['status'] = 0;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $execInfo['link'] = $link;
        if($stmt = $link->prepare($query))
        {
            $execInfo['status'] = 1;
        }
        else
        {
            $execInfo['status'] = -1;
        }

        $stmt->execute();
        $stmt->close();

        return $execInfo;
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
        $query = "UPDATE `user_personal` SET ".$set." WHERE `id` = '".$uId."';";

        $c = new DataBaseConnector();
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

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $stmt = $link->prepare($query);
        $stmt->execute();
        $msg = $stmt;

        $stmt->close();

        $userId = $this->GetUserIdByName($login);
        $this->DeleteUserAvaliableData($userId);

        return $msg;
    }

    public function CheckUserPersonalExist($login)
    {
        $query = "SELECT `login` FROM `user_personal` WHERE `login` = '".$login."';";

        $c = new DataBaseConnector();
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

    public function GetUsersByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `id` FROM `user_options` WHERE `author` = '".$author."';";
        $mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

        $list = array();
        while($row = $mySqliResult->fetch_array())
        {
            $item = $this->GetUserPrivilege($this->GetUserNameById($row['id']));
            array_push($list, $item);
        }
        $mySqliResult->free();
        $c->Disconnect();

        unset($c);

        return $list;
    }

    public function UpdateUsersBecauseAuthorDeleting($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "UPDATE `user_personal` SET `author` = 'admin' WHERE `author` = '".$author."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    /** ------------------------
     *  GET AVALIABLE
     */

    public function GetAvaliable($userIdentity, $type)
    {
        $userId = $userIdentity;
        if(is_string($userIdentity)) {
            $userId = $this->GetIdByUsername($userIdentity);
        }

        $userInfo = $this->GetUserInfo($userId);
        $role = $userInfo['role'];

        $avaliableItems = [];

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = null;
        if(self::isAdmin($role)) {
            $result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
                    "WHERE `type`='".$type."';");
        } else if(self::isModerator($role)) {
            $userIds = $this->GetUserIdsByAuthor($username);
            $userIds = implode("','", $userIds);
            $result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
                    "WHERE `userId` IN('".$userIds."' AND `type`='".$type."';");
        } else {
            $result = $link->query("SELECT `targetId` FROM `user_avaliability` ".
                    "WHERE `userId`='".$userId."' AND `type`='".$type."';");
        }

        while(($result !== null) && ($row = $result->fetch_array())) {
            $avaliableItems[] = $row['targetId'];
        }

        $c->Disconnect();
        unset($c);

        return $avaliableItems;
    }

    public function GetAvaliableFlights($username)
    {
        return $this->GetAvaliable($username, $this::$AVALIABILITY_FLIGHTS);
    }

    public function GetAvaliableUsers($username)
    {
        return $this->GetAvaliable($username, $this::$AVALIABILITY_USERS);
    }

    public function GetAvaliableBruTypes($username)
    {
        return $this->GetAvaliable($username, $this::$AVALIABILITY_FDR_TYPES);
    }

    /** ------------------------
     *  SET AVALIABLE
     */

    public function SetAvaliable($allowedBy, $id, $type, $userId = null)
    {
        if($userId === null) { //user allowed by him self (upload flight for ex)
            $userId = $this->GetIdByUsername($allowedBy);
        }

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "INSERT INTO `user_avaliability` (`type`, `userId`, `targetId`, `allowedBy`)
                VALUES ('".$type."', '".$userId."', '".$id."', '".$allowedBy."');";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function SetFlightAvaliable($allowedBy, $flightId, $userIdentity = null)
    {
        $this->SetAvaliable($allowedBy, $flightId, $this::$AVALIABILITY_FLIGHTS, $userIdentity);
    }

    public function SetBruTypeAvaliable($allowedBy, $FDRid, $userIdentity = null)
    {
        $this->SetAvaliable($allowedBy, $FDRid, $this::$AVALIABILITY_FDR_TYPES, $userIdentity);
    }

    public function SetUsersAvaliable($allowedBy, $userId, $userIdentity = null)
    {
        $this->SetAvaliable($allowedBy, $userId, $this::$AVALIABILITY_USERS, $userIdentity);
    }

    /** ------------------------
     *  UNSET AVALIABLE
     */

    public function UnsetAvaliable($userIdentity, $itemId, $type)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `user_avaliability` WHERE `targetId` = '".$itemId."' AND " .
                "`type`='".$type."';";

        if($userIdentity !== null) {
            $userId = $userIdentity;
            if(is_string($userIdentity)) {
                $userId = $this->GetIdByUsername($userIdentity);
            }

            $query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."'    AND
                `targetId` = '".$itemId."' AND `type`='".$type."';";
        }

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function UnsetFlightAvaliableForUser($userIdentity, $flightId)
    {
        $this->UnsetAvaliable($userIdentity, $flightId, $this::$AVALIABILITY_FLIGHTS);
    }

    public function UnsetFlightAvaliable($flightId)
    {
        $this->UnsetAvaliable(null, $flightId, $this::$AVALIABILITY_FLIGHTS);
    }

    public function UnsetBruTypesAvaliableForUser($userIdentity, $FDRid)
    {
        $this->UnsetAvaliable($userIdentity, $FDRid, $this::$AVALIABILITY_FDR_TYPES);
    }

    public function UnsetBruTypesAvaliable($FDRid)
    {
        $this->UnsetAvaliable(null, $FDRid, $this::$AVALIABILITY_FDR_TYPES);
    }

    public function UnsetUsersAvaliableForUser($userIdentity, $userId)
    {
        $this->UnsetAvaliable($userIdentity, $userId, $this::$AVALIABILITY_USERS);
    }

    public function UnsetUsersAvaliable($userId)
    {
        $this->UnsetAvaliable(null, $userId, $this::$AVALIABILITY_USERS);
    }

    /** ------------------------ **/

    public function GetUserIdsByAuthor($extUsername)
    {
        $username = $extUsername;

        $avaliableUserIds = array();

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $result = $link->query("SELECT `id` FROM `user_personal` ".
                "WHERE `author`='".$username."';");

        while($row = $result->fetch_array())
        {
            $avaliableUserIds[] = $row['id'];
        }

        $c->Disconnect();
        unset($c);

        return $avaliabeUsers;
    }

    public function DeleteUserAvaliableData($extUserId)
    {
        $userId = $extUserId;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `user_avaliability` WHERE `userId` = '".$userId."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function DeleteUserAvaliabilityForUsers($userId)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "DELETE FROM `user_avaliability` WHERE " .
                "`targetId` = '".$userId."' AND `type`='".$this::$AVALIABILITY_USERS."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function SetUserLanguage($login, $lang)
    {
        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "UPDATE `user_personal` SET `lang` = '".$lang."'
                WHERE `login` = '".$login."';";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);
    }

    public function GetLastAction($extUserId, $extAction)
    {
        $userId = $extUserId;
        $action= $extAction;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `user_activity` WHERE `action` = '".$action."' AND `userId` = '".$userId."' ORDER BY `id` DESC LIMIT 1;";
        $result = $link->query($query);

        $lastAction = null;

        if($row = $result->fetch_array())
        {
            $lastAction = array();
            foreach ($row as $key => $val)
            {
                $lastAction[$key] = $val;
            }
        }

        $c->Disconnect();
        unset($c);

        return $lastAction;
    }

    public function GetLastActionFromRange($extUserId, $extActionsRange)
    {
        $userId = $extUserId;
        $actionsRange = implode("','", $extActionsRange);

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM `user_activity` WHERE `action` IN ('".$actionsRange."') AND `userId` = '".$userId."' ORDER BY `id` DESC LIMIT 1;";
        $result = $link->query($query);

        $lastAction = null;

        if($row = $result->fetch_array())
        {
            $lastAction = array();
            foreach ($row as $key => $val)
            {
                $lastAction[$key] = $val;
            }
        }

        $c->Disconnect();
        unset($c);

        return $lastAction;
    }

    public function RegisterUserAction($extAction, $extStatus, $extUserId,
            $extSenderId, $extSenderName, $extTargetId, $extTargetName)
    {
        $action = $extAction;
        $status = $extStatus;
        $userId = $extUserId;
        $senderId = $extSenderId;
        $senderName = $extSenderName;
        $targetId = $extTargetId;
        $targetName = $extTargetName;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "INSERT INTO `user_activity` (`action`,`status`, `userId`, `senderId`, `senderName`, `targetId`, `targetName`)
                VALUES ('".$action."', '".$status."', '".$userId."', " .
                        "'".$senderId."','".$senderName."','".$targetId."', '".$targetName."');";

        $stmt = $link->prepare($query);
        $executionStatus = $stmt->execute();
        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $executionStatus;
    }
}
