<?php

namespace Controller;

use Model\User;
use Model\Language;
use Model\Fdr;
use Model\UserOptions;

class UsersController extends CController
{
    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    public function CreateUserByForm($form, $file)
    {
        $login = $form['login'];
        $company = $form['company'];
        $pwd = $form['pwd'];
        $role = $form['role'];
        if(is_array($role)) {
            $role = $role[count($role) - 1];
        }
        $authorId = intval($this->_user->userInfo['id']);
        $permittedBruTypes = isset($form['FDRsAvailable']) ? $form['FDRsAvailable'] : [];
        $file = str_replace("\\", "/", $file);

        $msg = '';

        if (!$this->_user->CheckUserPersonalExist($login)) {
            $this->_user->CreateUserPersonal($login, $pwd, $company, $role, $file, $authorId);
            $createdUserId = intval($this->_user->GetIdByUsername($login));

            foreach($permittedBruTypes as $id) {
                $this->_user->SetFDRavailable($createdUserId, intval($id));
            }
        } else {
            $msg = $this->lang->userAlreadyExist;
        }

        return $msg;
    }

    public function UpdateUserByForm($userIdToUpdate, $form, $file)
    {
        $userIdToUpdate = intval($userIdToUpdate);
        $availableForUpdate = false;
        $author = $this->_user->username;
        $authorId = $this->_user->GetUserIdByName($author);
        $authorInfo = $this->_user->GetUserInfo($authorId);
        $userInfo = $this->_user->GetUserInfo($userIdToUpdate);

        $userId = intval($this->_user->userInfo['id']);
        $userRole = $this->_user->userInfo['role'];
        $availableUsers = $this->_user->GetAvailableUsersList($userId, $userRole);

        if(in_array($userIdToUpdate, $availableUsers)) {
            $availableForUpdate = true;
        }

        $personalData = [];

        if(isset($form['pwd'])) {
            $personalData['pass'] = md5($form['pwd']);
        }

        if(isset($form['company'])) {
            $personalData['company'] = $form['company'];
        }

        if(isset($form['role'])) {
            $personalData['role'] = $form['role'];
        }

        if($file !== null) {
            $personalData['logo'] = str_replace("\\", "/", $file);
        }

        $this->_user->UpdateUserPersonal($userIdToUpdate, $personalData);

        $permittedBruTypes = isset($form['FDRsAvailable']) ? $form['FDRsAvailable'] : [];
        $msg = '';

        foreach($permittedBruTypes as $id) {
            $this->_user->SetFDRavailable($userIdToUpdate, intval($id));
        }

        return $msg;
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */
    public function updateUserOptions($data)
    {
        $form = [];
        parse_str($data, $form);

        $userId = intval($this->_user->userInfo['id']);
        $O = new UserOptions;
        $O->UpdateOptions($form, $userId);
        unset($O);

        $answ = array(
            'status' => 'ok'
        );

        echo json_encode($answ);
    }

    public function userLogout($data)
    {
        if (!isset($data['data']))
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }

        $this->_user->logout($this->_user->username);

        $answ = array(
            'status' => 'ok'
        );

        echo json_encode($answ);
    }

    public function userChangeLanguage($data)
    {
        if (!isset($data['lang'])) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }

        $lang = $data['lang'];

        $L = new Language;
        $L->SetLanguageName($lang);
        unset($L);

        $this->_user->SetUserLanguage($this->_user->username, $lang);

        echo json_encode([
            'status' => 'ok'
        ]);
    }

    public function createUser($data)
    {
        if (isset($data) &&
            isset($_FILES['logo']) &&
            isset($_FILES['logo']['tmp_name'])
        ) {
            $form = $_POST;
            $file = $_FILES['logo']['tmp_name'];

            $answ = [
                'status' => 'ok'
            ];

            if(!isset($form['login'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseInputUserLogin
                ];
            }

            if(!isset($form['company'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseInputUserCompany
                ];
            }

            if(!isset($form['pwd']) || !isset($form['pwd2'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseInputPass
                ];
            }

            if($form['pwd'] != $form['pwd2']) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->passwordRepeatingIncorrect
                ];
            }

            if(!isset($form['role'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseChooseRole
                ];
            }

            if($answ['status'] == 'ok') {
                $resMsg = $this->CreateUserByForm($form, $file);

                if($resMsg != '') {
                    $answ = [
                        'status' => 'err',
                        'error' => $resMsg
                    ];
                }
            }

            $this->RegisterActionExecution($this->action, "executed");
            echo(json_encode($answ));
            exit();
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page user.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit();
        }
    }

    public function updateUser($data)
    {
        if(isset($data) && isset($_POST['useridtoupdate']))
        {
            $form = $_POST;
            $userIdToUpdate = $form['useridtoupdate'];
            $file = null;
            if(isset($_FILES) &&
                    isset($_FILES['logo']) &&
                    isset($_FILES['logo']['tmp_name']))
            {
                $file = $_FILES['logo']['tmp_name'];
            }

            $answ = [
                'status' => 'ok'
            ];

            if($form['pwd'] != $form['pwd2']) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->passwordRepeatingIncorrect
                ];
            }

            if(!isset($form['role'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseChooseRole
                ];
            }

            if($answ['status'] == 'ok') {
                $resMsg = $this->UpdateUserByForm($userIdToUpdate, $form, $file);

                if($resMsg != '') {
                    $answ = [
                            'status' => 'err',
                            'error' => $resMsg
                    ];
                }
            }

            $this->RegisterActionExecution($this->action, "executed");
            echo(json_encode($answ));
            exit();
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page user.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit();
        }
    }

    public function deleteUser($data)
    {
        if (!isset($data) || !isset($data['userIds'])) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page user.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit();
        }

        $userIds = $data['userIds'];

        foreach ($userIds as $userDeleteId) {
            if (is_int(intval($userDeleteId))) {
                $userInfo = $this->_user->GetUserInfo(intval($userDeleteId));

                if (!empty($userInfo)) {
                    $login = $userInfo['login'];
                    $this->_user->DeleteUserPersonal($login);
                    $this->_user->UnsetFDRavailable($userDeleteId);

                    /* TODO it is also necessary to clean up flight data and folders*/
                }
            }
        }

        $answ = [
            'status' => 'ok'
        ];

        $this->RegisterActionExecution($this->action, "executed");
        echo(json_encode($answ));
        exit();
    }

    public function getUserSettings()
    {
        if (!isset($this->_user->userInfo)) {
            http_response_code(403);
            header('HTTP/1.0 403 Forbidden');
            echo 'User is not authorized.';
            exit;
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $settings = $O->GetOptions($userId);
        unset($O);

        echo json_encode($settings);
        exit;
    }

    public function setUserSettings($settings)
    {
        if (!isset($settings)) {
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ = "Not all nessesary params sent. Post: ".
                json_encode($data) . ". " . get_class($this);
            $this->RegisterActionReject($this->action, "rejected", 0, $answ);
            echo(json_encode($answ));
            exit;
        }

        if (!is_array($settings) || empty($settings)) {
            http_response_code(400);
            header("Status: 400 Bad Request");
            $answ["status"] = "err";
            $answ["error"] = "Input params incorrect types. Post: ".
                    json_encode($_POST) . ". " . get_class($this);
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
            exit;
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $O->UpdateOptions($settings, $userId);
        unset($O);

        echo json_encode(['status' => 'ok']);
        exit;
    }

    public function login ($args)
    {
        if (empty($args)
            || !(isset($args['login']))
            || !(isset($args['pass']))
        ) {
            http_response_code(401);
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode([
                'error' => 'Not all necessary fields passed',
                'code' => 0
            ]);
            exit;
        }

        $U = new User();
        $data = [
            'user' => $args['login'],
            'pwd' => $args['pass']
        ];

        $success = false;
        $lang = 'en';

        if ($U->tryAuth($data, $_SESSION, $_COOKIE)) {
            if (isset($U->username) && ($U->username != '')) {
                $usrInfo = $U->GetUsersInfo($U->username);
                $lang = strtolower($usrInfo['lang']);

                echo json_encode([
                    'status' => 'ok',
                    'login' => $args['login'],
                    'lang' => $lang
                ]);
                exit;
            } else {
                http_response_code(401);
                header('HTTP/1.0 401 Unauthorized');
                echo json_encode([
                    'error' => 'Incorrect login or password',
                    'code' => 1
                ]);
                exit;
            }
        } else {
            http_response_code(401);
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode([
                'error' => 'Incorrect login or password',
                'code' => 1
            ]);
            exit;
        }
    }
}
