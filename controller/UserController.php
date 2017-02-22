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

    public function BuildUserTable()
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
        return $table;
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

        $form .= sprintf("<input type='text' name='action' value='%s' style='visibility:hidden;'/>", "createUser");
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

        $form .= sprintf("<input type='text' name='action' value='%s' style='visibility:hidden;'/>", $this->userActions["updateUser"]);
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

    public function CreateUser($form, $file)
    {
        $login = $form['login'];
        $company = $form['company'];
        $pwd = $form['pwd'];
        $privilege = $form['privilege'];
        $role = $form['role'];
        if(is_array($role)) {
            $role = $role[count($role) - 1];
        }
        $author = $this->_user->username;
        $permittedFlights = isset($form['flightsAvailable']) ? $form['flightsAvailable'] : [];
        $permittedBruTypes = isset($form['FDRsAvailable']) ? $form['FDRsAvailable'] : [];
        $permittedUsers = isset($form['usersAvailable']) ? $form['usersAvailable'] : [];
        $file = str_replace("\\", "/", $file);

        $msg = '';

        if (!$this->_user->CheckUserPersonalExist($login)) {
            $this->_user->CreateUserPersonal($login, $pwd, $privilege, $author, $company, $role, $file);
            $createdUserId = $this->_user->GetIdByUsername($login);
            $authorId = $this->_user->GetUserIdByName($this->_user->username);

            foreach($permittedBruTypes as $id) {
                $this->_user->SetFDRavailable($createdUserId, $id);
            }
        } else {
            $msg = $this->lang->userAlreadyExist;
        }

        return $msg;
    }

    public function UpdateUser($userIdToUpdate, $form, $file)
    {
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

        $permittedFlights = isset($form['flightsAvailable']) ? $form['flightsAvailable'] : [];
        $permittedBruTypes = isset($form['FDRsAvailable']) ? $form['FDRsAvailable'] : [];
        $permittedUsers = isset($form['usersAvailable']) ? $form['usersAvailable'] : [];

        $msg = '';

        foreach($permittedBruTypes as $id) {
            $this->_user->SetFDRavailable($userIdToUpdate, $id);
        }

        return $msg;
    }

    public function DeleteUser($userIds)
    {
        foreach ($userIds as $userDeleteId) {
            if(is_int(intval($userDeleteId))) {
                $userInfo = $this->_user->GetUserInfo(intval($userDeleteId));
                $login = $userInfo['login'];

                $this->_user->DeleteUserPersonal($login);
                $this->_user->UnsetFDRavailable($userDeleteId);

                /* TODO it is also necessary to clean up flight data and folders*/
            }
        }

        return true;
    }

    public function UpdateUserOptions($form)
    {
        $userInfo = $this->GetUserInfo();
        $userId = $userInfo['id'];
        $O = new UserOptions;
        $O->UpdateOptions($form, $userId);
        unset($O);
        return $form;
    }
}
