<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/UserController.php");

$c = new UserController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action == 'userLogout') {
        if(isset($c->data['data']))
        {
            $action = $c->action;

            $c->Logout();

            $answ = array(
                    'status' => 'ok'
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
            $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    } else if($c->action == $c->userActions["userChangeLanguage"]) {
        if(in_array(User::$PRIVILEGE_OPTIONS_USERS, $c->_user->privilege))
        {
            if(isset($c->data['lang']))
            {
                $action = $c->action;
                $lang = $c->data['lang'];

                $c->ChangeLanguage($lang);

                $answ = array(
                        'status' => 'ok'
                );

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page user.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == $c->userActions["updateUserOptions"]) {
        if(in_array(User::$PRIVILEGE_OPTIONS_USERS, $c->_user->privilege))
        {
            $action = $c->action;
            $form = [];
            parse_str($c->data, $form);

            $c->UpdateUserOptions($form);

            $answ = array(
                'status' => 'ok'
            );

            echo json_encode($answ);
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == "buildUserTable") {
        if(in_array(User::$PRIVILEGE_OPTIONS_USERS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $table = $c->BuildUserTable();
                $c->RegisterActionExecution($c->action, "executed", 0, 'getUserList', '', '');

                $answ = [
                    "status" => "ok",
                    "data" => $table,
                    "sortCol" => 2, // id
                    "sortType" => 'desc'
                ];

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page user.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == "segmentTable") {
        if(in_array(User::$PRIVILEGE_VIEW_USERS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $aoData = $c->data['data'];
                $sEcho = $aoData[sEcho]['value'];
                $iDisplayStart = $aoData[iDisplayStart]['value'];
                $iDisplayLength = $aoData[iDisplayLength]['value'];

                $sortValue = count($aoData) - 3;
                $sortColumnName = 'id';
                $sortColumnNum = $aoData[$sortValue]['value'];
                $sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);

                switch ($sortColumnNum){
                    case(1):
                        {
                            $sortColumnName = 'login';
                            break;
                        }
                    case(2):
                        {
                            $sortColumnName = 'lang';
                            break;
                        }
                    case(3):
                        {
                            $sortColumnName = 'company';
                            break;
                        }
                }

                $totalRecords = -1;
                $aaData["sEcho"] = $sEcho;
                $aaData["iTotalRecords"] = $totalRecords;
                $aaData["iTotalDisplayRecords"] = $totalRecords;

                $c->RegisterActionExecution($c->action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);

                $tableSegment = $c->BuildTableSegment($sortColumnName, $sortColumnType);
                $aaData["aaData"] = $tableSegment;

                echo(json_encode($aaData));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page user.php";
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                        echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == $c->userActions["createUserForm"]) {
        if(in_array(User::$PRIVILEGE_ADD_USERS, $c->_user->privilege))
        {
            $modal = $c->BuildCreateUserModal();
            $action = $c->action;
            $c->RegisterActionExecution($action, "executed");
            echo(json_encode($modal));
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == $c->userActions["updateUserForm"]) {
        if(in_array(User::$PRIVILEGE_EDIT_USERS, $c->_user->privilege))
        {
            if(isset($c->data) && isset($c->data['userid']))
            {
                $userid = intval($c->data['userid']);
                $modal = $c->BuildUpdateUserModal($userid);
                $action = $c->action;
                $c->RegisterActionExecution($action, "executed");
                echo(json_encode($modal));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page user.php";
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                        echo(json_encode($answ));
                        exit();
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    } else if($c->action == $c->userActions["createUser"]) {
        if(in_array(User::$PRIVILEGE_ADD_USERS, $c->_user->privilege))
        {
            if(isset($c->data) &&
                    isset($_FILES['logo']) &&
                    isset($_FILES['logo']['tmp_name']))
            {
                $form = $_POST;
                $file = $_FILES['logo']['tmp_name'];
                $action = $c->action;

                $answ = [
                    'status' => 'ok'
                ];

                if(!isset($form['login'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseInputUserLogin
                    ];
                }

                if(!isset($form['company'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseInputUserCompany
                    ];
                }

                if(!isset($form['pwd']) || !isset($form['pwd2'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseInputPass
                    ];
                }

                if($form['pwd'] != $form['pwd2']) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->passwordRepeatingIncorrect
                    ];
                }

                if(!isset($form['privilege'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseChoosePrivilege
                    ];
                }

                if(!isset($form['role'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseChooseRole
                    ];
                }

                if($answ['status'] == 'ok') {
                    $resMsg = $c->CreateUser($form, $file);

                    if($resMsg != '') {
                        $answ = [
                                'status' => 'err',
                                'error' => $resMsg
                        ];
                    }
                }

                $c->RegisterActionExecution($action, "executed");
                echo(json_encode($answ));
                exit();
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
                exit();
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else if($c->action == $c->userActions["updateUser"]) {
        if(in_array(User::$PRIVILEGE_ADD_USERS, $c->_user->privilege))
        {
            if(isset($c->data) && isset($_POST['useridtoupdate']))
            {
                $form = $_POST;
                $c->_userserIdToUpdate = $form['useridtoupdate'];
                $file = null;
                if(isset($_FILES) &&
                        isset($_FILES['logo']) &&
                        isset($_FILES['logo']['tmp_name']))
                {
                    $file = $_FILES['logo']['tmp_name'];
                }

                $action = $c->action;

                $answ = [
                    'status' => 'ok'
                ];

                if($form['pwd'] != $form['pwd2']) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->passwordRepeatingIncorrect
                    ];
                }

                if(!isset($form['privilege'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseChoosePrivilege
                    ];
                }

                if(!isset($form['role'])) {
                    $answ = [
                        'status' => 'err',
                        'error' => $c->lang->pleaseChooseRole
                    ];
                }

                if($answ['status'] == 'ok') {
                    $resMsg = $c->UpdateUser($c->_userserIdToUpdate, $form, $file);

                    if($resMsg != '') {
                        $answ = [
                                'status' => 'err',
                                'error' => $resMsg
                        ];
                    }
                }

                $c->RegisterActionExecution($action, "executed");
                echo(json_encode($answ));
                exit();
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
                exit();
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else if($c->action == $c->userActions["deleteUser"]) {
        if(in_array(User::$PRIVILEGE_DEL_USERS, $c->_user->privilege))
        {
            if(isset($c->data) && isset($c->data['userIds']))
            {
                $c->_userserIds = $c->data['userIds'];
                $action = $c->action;

                $answ = [
                    'status' => 'ok'
                ];

                if(!$c->DeleteUser($c->_userserIds)) {
                    $answ["status"] = "err";
                    $answ["error"] = $c->lang->errorDuringUserDeletion;
                }

                $c->RegisterActionExecution($action, "executed");
                echo(json_encode($answ));
                exit();
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
                exit();
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
            exit();
        }
    } else {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
        error_log($msg);
        echo($msg);
        exit();
    }
}
else
{
    $msg = "Authorization error. Page: " . $c->curPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
    exit();
}
