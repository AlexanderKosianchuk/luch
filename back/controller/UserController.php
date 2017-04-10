<?php

namespace Controller;

use Model\User;
use Model\Language;
use Model\Fdr;
use Model\UserOptions;

class UserController extends CController
{
    public $curPage = 'userPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    public function PutTopMenu()
    {
        $topMenu = "<div id='topMenuBruType' class='TopMenu'></div>";
        return $topMenu;
    }

    public function Logout()
    {
        $this->_user->logout($this->_user->username);
    }

    public function ChangeLanguage($lang)
    {
        $L = new Language;
        $L->SetLanguageName($lang);
        unset($L);

        $this->_user->SetUserLanguage($this->_user->username, $lang);

        return 'ok';
    }

    public function GetUserList()
    {
        $userId = intval($this->_user->userInfo['id']);
        $userRole = $this->_user->userInfo['role'];
        $availableUsers = $this->_user->GetAvailableUsersList($userId, $userRole);

        return $availableUsers;
    }

    public function BuildTableSegment($extOrderColumn, $extOrderType)
    {
        $orderColumn = $extOrderColumn;
        $orderType = $extOrderType;

        $userList = $this->GetUserList();

        $tableSegment = [];

        foreach($userList as $user)
        {
            $img = '';
            if(!empty($user['logo'])) {
                $img = '<div id="userlist-img-container">
                    <div class="userlist-img-image-container">
                        <img src="data:image/jpeg;base64,' . base64_encode($user['logo']) . '">
                    </div>
                </div>​';
            }

            $tableSegment[] = array(
                    "<input class='ItemsCheck' data-type='user' data-userid='".$user['id']."' type='checkbox'/>",
                    $user['login'],
                    $user['lang'],
                    $user['company'],
                    str_replace(",", ", ", $user['privilege']),
                    $img
            );
        }

        return $tableSegment;
    }

    public function GetUserInfo()
    {
        $uId = $this->_user->GetUserIdByName($this->_user->username);
        $userInfo = $this->_user->GetUserInfo($uId);

        return $userInfo;
    }

    private function printTableAvaliability($cellNames, $availableRows, $rowKeys, $dataKey, $available = []) {
        $form = '';
        //if more than 30 rows make table scrollable
        if(count($availableRows) > 30)
        {
            $form .= sprintf("<div class='items-avaliability-table-wrap'>");
        }

        $form .= sprintf("<table class='items-avaliability-table'>");
        $form .= sprintf("<tr class='items-avaliability-table-header'>");
        for($ii = 0; $ii < count($cellNames) - 1; $ii++) {
            $form .= sprintf("<td class='items-avaliability-table-cell'>%s</td>", $cellNames[$ii]);
        }
        $form .= sprintf("<td class='items-avaliability-table-cell' width='50px'>%s</td>", $cellNames[count($cellNames) - 1]);
        $form .= sprintf("</tr>");

        foreach ($availableRows as $rowInfo) {
            $form .= sprintf("<tr class='table-stripe'>");
            for($ii = 1; $ii < count($rowKeys); $ii++) {
                $form .= sprintf("<td class='items-avaliability-table-cell'>%s</td>", $rowInfo[$rowKeys[$ii]]);
            }

            $checked = '';
            if(in_array($rowInfo[$rowKeys[0]], $available)) {
                $checked = " checked='checked' ";
            }

            $form .= sprintf("<td class='items-avaliability-table-cell' align='center'>
                            <input name='".$dataKey."Available[]' value='%s' type='checkbox' ".$checked."/>
                        </td>", $rowInfo[$rowKeys[0]]); // always id should be
            $form .= sprintf("</tr>");
        }
        $form .= sprintf("</table>");

        if(count($availableRows) > 30) {
            $form .= sprintf("</div>");
        }

        return $form;
    }

    public function BuildCreateUserModal()
    {
        $privilege = $this->_user->allPrivilegeArray;
        $uId = $this->_user->GetUserIdByName($this->_user->username);
        $authorInfo = $this->_user->GetUserInfo($uId);
        $role = $authorInfo['role'];
        $authorPrivilege = explode(',', $authorInfo['privilege']);

        $form = sprintf("<div id='user-cru-modal'><form id='user-cru-form' enctype='multipart/form-data'>");

        $privilegeOptions = "<tr><td>".$this->lang->userPrivilege."</td><td align='center'>";
        $privilegeOptions .= "<select id='privilege' name='privilege[]' multiple size='10' style='width: 335px'>";

        foreach ($authorPrivilege as $val)
        {
            $selected = '';
            if(in_array($val, $privilege)) {
                $selected = " selected='selected' ";
            }
            $privilegeOptions .= "<option ".$selected.">".$val."</option>";
        }

        $roleOptions = '';
        if(User::isAdmin($role)) {
            $roleOptions .= "<tr><td>".$this->lang->userRole."</td><td align='center'>";
            $roleOptions .= "<select name='role[]' size='3' style='width: 335px'>";
            foreach (User::$role as $val)
            {
                $roleOptions .= "<option selected='selected'>".$val."</option>";
            }
            $roleOptions .= "</select></td></tr>";
        } else {
            $roleOptions .= "<input type='hidden' name='role' size='50' value='user'>";
        }

        $form .= sprintf("<table align='center'>
            <p class='Label'>%s</p>
            <div class='user-creation-info'><p>%s</p></div>
            <tr><td>%s</td><td>
                <input type='text' name='login' size='50'>
            </td></tr>
            <tr><td>%s</td><td>
                <input type='text' name='company' size='50'>
            </td></tr>
            <tr><td>%s</td><td>
                <input class='user-pwd' type='password' name='pwd' size='50'>
            </td></tr>
            <tr><td>%s</td><td>
                <input class='user-pwd' type='password' name='pwd2' size='50'>
            </td></tr>
                %s
                %s
            <tr><td>%s</td><td align='center'>
                <input type='file' name='logo'>
            </td></tr>
        </table>",
                $this->lang->userCreationForm,
                '',
                $this->lang->userName,
                $this->lang->company,
                $this->lang->pass,
                $this->lang->repeatPass,
                $privilegeOptions,
                $roleOptions,
                $this->lang->userLogo);

        $form .= sprintf("<input type='text' name='action' value='%s' style='visibility:hidden;'/>", "user/createUser");
        $form .= sprintf("<input type='text' name='data' value='dummy' style='visibility:hidden;'/>");

        //==========================================
        //access to brutypes
        //==========================================
        if(in_array(User::$PRIVILEGE_SHARE_BRUTYPES, $this->_user->privilege))
        {
            $form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForBruTypes);

            $Bru = new Fdr;
            $availableIds = $this->_user->GetAvailableBruTypes($this->_user->username);
            $availableBruTypes = $Bru->GetBruList($availableIds);

            if(count($availableBruTypes) > 0) {
                $headerLables = [
                    $this->lang->bruTypesName,
                    $this->lang->bruTypesStepLenth,
                    $this->lang->bruTypesFrameLength,
                    $this->lang->bruTypesWordLength,
                    $this->lang->bruTypesAuthor,
                    $this->lang->access
                ];

                $rowsInfoKeys = [
                    'id',
                    'name',
                    'stepLength',
                    'frameLength',
                    'wordLength',
                    'author'
                ];

                $form .= $this->printTableAvaliability($headerLables, $availableBruTypes, $rowsInfoKeys, 'FDRs');
            } else {
                $form .= sprintf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
                        $this->lang->noDataToOpenAccess);
            }
            $form .= sprintf("</div>");
            unset($Bru);
        }

        $form .= '</form></div>';

        return $form;
    }

    public function BuildUpdateUserModal($updatedUsersId)
    {
        $privilege = $this->_user->allPrivilegeArray;
        $authorId = $this->_user->GetUserIdByName($this->_user->username);
        $authorInfo = $this->_user->GetUserInfo($authorId);
        $userInfo = $this->_user->GetUserInfo($updatedUsersId);
        $role = $userInfo['role'];
        $privilege = explode(",", $userInfo['privilege']);

        $form = sprintf("<div id='user-cru-modal'><form id='user-cru-form' enctype='multipart/form-data'>");

        $privilegeOptions = "<tr><td>".$this->lang->userPrivilege."</td><td align='center'>";
        $privilegeOptions .= "<select id='privilege' name='privilege[]' multiple size='10' style='width: 335px'>";

        $authorPrivilege = explode(',', $authorInfo['privilege']);
        foreach ($authorPrivilege as $val)
        {
            $selected = '';
            if(in_array($val, $privilege)) {
                $selected = " selected='selected' ";
            }
            $privilegeOptions .= "<option ".$selected.">".$val."</option>";
        }
        $privilegeOptions .= "</select></td></tr>";

        $roleOptions = '';
        if(User::isAdmin($role)) {
            $roleOptions .= "<tr><td>".$this->lang->userRole."</td><td align='center'>";
            $roleOptions .= "<select name='role[]' size='3' style='width: 335px'>";
            foreach (User::$role as $val)
            {
                $selected = '';
                if($val == $role) {
                    $selected = " selected='selected' ";
                }
                $roleOptions .= "<option ".$selected.">".$val."</option>";
            }
            $roleOptions .= "</select></td></tr>";
        } else {
            $roleOptions .= "<input type='hidden' name='role' size='50' value='user'>";
        }

        $form .= sprintf("<table align='center'>
            <p class='Label'>%s</p>
            <div class='user-creation-info'><p>%s</p></div>
            <tr><td>%s</td><td>
                <input type='text' name='login' size='50' value='%s' disabled='disabled'>
            </td></tr>
            <tr><td>%s</td><td>
                <input type='text' name='company' size='50' value='%s'>
            </td></tr>
            <tr><td>%s</td><td>
                <input class='user-pwd' type='password' name='pwd' size='50'>
            </td></tr>
            <tr><td>%s</td><td>
                <input class='user-pwd' type='password' name='pwd2' size='50'>
            </td></tr>
                %s
                %s
            <tr><td>%s</td><td align='center'>
                <input type='file' name='logo'>
            </td></tr>
        </table>",
                $this->lang->userCreationForm,
                '',
                $this->lang->userName,
                $userInfo['login'],
                $this->lang->company,
                $userInfo['company'],
                $this->lang->pass,
                $this->lang->repeatPass,
                $privilegeOptions,
                $roleOptions,
                $this->lang->userLogo);

        $form .= sprintf("<input type='text' name='action' value='user/updateUser' style='visibility:hidden;'/>");
        $form .= sprintf("<input type='text' name='data' value='dummy' style='visibility:hidden;'/>");
        $form .= sprintf("<input type='text' name='useridtoupdate' value='%s' style='visibility:hidden;'/>", $updatedUsersId);

        //==========================================
        //access to brutypes
        //==========================================
        if(in_array(User::$PRIVILEGE_SHARE_BRUTYPES, $this->_user->privilege))
        {
            $form .= sprintf("<div><p class='Label'>%s</p></br>", $this->lang->openAccessForBruTypes);

            $Bru = new Fdr;
            $availableIds = $this->_user->GetAvailableBruTypes($this->_user->username);
            $availableBruTypes = $Bru->GetBruList($availableIds);
            $attachedfdrIds = $this->_user->GetAvailableBruTypes($userInfo['login']);

            if(count($availableBruTypes) > 0) {
                $headerLables = [
                    $this->lang->bruTypesName,
                    $this->lang->bruTypesStepLenth,
                    $this->lang->bruTypesFrameLength,
                    $this->lang->bruTypesWordLength,
                    $this->lang->bruTypesAuthor,
                    $this->lang->access
                ];

                $rowsInfoKeys = [
                    'id',
                    'name',
                    'stepLength',
                    'frameLength',
                    'wordLength',
                    'author'
                ];

                $form .= $this->printTableAvaliability(
                    $headerLables,
                    $availableBruTypes,
                    $rowsInfoKeys,
                    'FDRs',
                    $attachedfdrIds
                );
            } else {
                $form .= sprintf("<div align='center'><p class='SmallLabel' style='color:darkred;'>%s</p></br>",
                        $this->lang->noDataToOpenAccess);
            }
            $form .= sprintf("</div>");
            unset($Bru);
        }

        $form .= '</form></div>';

        return $form;
    }

    public function CreateUserByForm($form, $file)
    {
        $login = $form['login'];
        $company = $form['company'];
        $pwd = $form['pwd'];
        $privilege = $form['privilege'];
        $role = $form['role'];
        if(is_array($role)) {
            $role = $role[count($role) - 1];
        }
        $authorId = intval($this->_user->userInfo['id']);
        $permittedBruTypes = isset($form['FDRsAvailable']) ? $form['FDRsAvailable'] : [];
        $file = str_replace("\\", "/", $file);

        $msg = '';

        if (!$this->_user->CheckUserPersonalExist($login)) {
            $this->_user->CreateUserPersonal($login, $pwd, $privilege, $author, $company, $role, $file, $authorId);
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

        if(isset($form['privilege'])) {
            $personalData['privilege'] = implode(",", $form['privilege']);
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

        $userInfo = $this->GetUserInfo();
        $userId = $userInfo['id'];
        $O = new UserOptions;
        $O->UpdateOptions($form, $userId);
        unset($O);

        $answ = array(
            'status' => 'ok'
        );

        echo json_encode($answ);
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */

    public function userLogout($data)
    {
        if(isset($data['data']))
        {
            $this->Logout();

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
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function userChangeLanguage($data)
    {
        if(isset($data['lang']))
        {
            $lang = $data['lang'];

            $this->ChangeLanguage($lang);

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
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function buildUserTable($data)
    {
        if(isset($data['data']))
        {
            $table = sprintf("<table id='userTable' cellpadding='0' cellspacing='0' border='0'>
                    <thead><tr>");

            $table .= sprintf("<th name='checkbox' style='width:%s;'>%s</th>", "1%", "<input id='tableCheckAllItems' type='checkbox'/>");
            $table .= sprintf("<th name='login'>%s</th>", $this->lang->userLogin);
            $table .= sprintf("<th name='lang'>%s</th>", $this->lang->userLang);
            $table .= sprintf("<th name='company'>%s</th>", $this->lang->userCompany);
            $table .= sprintf("<th name='privilege'>%s</th>", $this->lang->userPrivilege);
            $table .= sprintf("<th name='logo'>%s</th>", $this->lang->userLogo);

            $table .= sprintf("</tr></thead><tfoot style='display: none;'><tr>");

            for($i = 0; $i < 6; $i++) {
                $table .= sprintf("<th></th>");
            }

            $table .= sprintf("</tr></tfoot><tbody></tbody></table>");

            $this->RegisterActionExecution($this->action, "executed", 0, 'getUserList', '', '');

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
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function segmentTable($data)
    {
        if(isset($data['data']))
        {
            $aoData = $data['data'];
            $sEcho = $aoData[sEcho]['value'];
            $iDisplayStart = $aoData[iDisplayStart]['value'];
            $iDisplayLength = $aoData[iDisplayLength]['value'];

            $sortValue = count($aoData) - 3;
            $sortColumnName = 'id';
            $sortColumnNum = $aoData[$sortValue]['value'];
            $sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);

            switch ($sortColumnNum){
                case(1): {
                        $sortColumnName = 'login';
                        break;
                }
                case(2): {
                        $sortColumnName = 'lang';
                        break;
                }
                case(3): {
                        $sortColumnName = 'company';
                        break;
                }
            }

            $totalRecords = -1;
            $aaData["sEcho"] = $sEcho;
            $aaData["iTotalRecords"] = $totalRecords;
            $aaData["iTotalDisplayRecords"] = $totalRecords;

            $this->RegisterActionExecution($this->action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);

            $tableSegment = $this->BuildTableSegment($sortColumnName, $sortColumnType);
            $aaData["aaData"] = $tableSegment;

            echo(json_encode($aaData));
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page user.php";
                    $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
                    echo(json_encode($answ));
        }
    }

    public function createUserForm($data)
    {
        $modal = $this->BuildCreateUserModal();
        $this->RegisterActionExecution($this->action, "executed");
        echo(json_encode($modal));
    }

    public function updateUserForm($data)
    {
        if(isset($data) && isset($data['userid']))
        {
            $userid = intval($data['userid']);
            $modal = $this->BuildUpdateUserModal($userid);
            $this->RegisterActionExecution($this->action, "executed");
            echo(json_encode($modal));
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

            if(!isset($form['privilege'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseChoosePrivilege
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

            if(!isset($form['privilege'])) {
                $answ = [
                    'status' => 'err',
                    'error' => $this->lang->pleaseChoosePrivilege
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
        if(isset($data) && isset($data['userIds']))
        {
            $userIds = $data['userIds'];

            foreach ($userIds as $userDeleteId) {
                if(is_int(intval($userDeleteId))) {
                    $userInfo = $this->_user->GetUserInfo(intval($userDeleteId));
                    $login = $userInfo['login'];

                    $this->_user->DeleteUserPersonal($login);
                    $this->_user->UnsetFDRavailable($userDeleteId);

                    /* TODO it is also necessary to clean up flight data and folders*/
                }
            }

            $answ = [
                'status' => 'ok'
            ];

            $c->RegisterActionExecution($c->action, "executed");
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

}
