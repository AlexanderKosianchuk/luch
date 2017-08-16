<?php

namespace Controller;

use Model\User;
use Model\Language;
use Model\Fdr;
use Model\UserOptions;

use Repository\UserRepository;

use Component\EntityManagerComponent as EM;
use Component\RuntimeManager;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class UsersController extends CController
{
    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    public function login ($args)
    {
        if (empty($args)
            || !isset($args['login'])
            || !isset($args['pass'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $U = new User();
        $data = [
            'user' => $args['login'],
            'pwd' => $args['pass']
        ];

        $success = false;
        $lang = 'en';

        $isAuthorized = $U->tryAuth($data, $_SESSION, $_COOKIE);

        if (!$isAuthorized
            || !isset($U->username)
            || empty($U->username)
        ) {
            return json_encode([
                'status' => 'fail',
                'message' => 'userUnexist',
                'messageText' => 'User unexist'
            ]);
        }

        $usrInfo = $U->GetUsersInfo($U->username);
        $lang = strtolower($usrInfo['lang']);

        return json_encode([
            'status' => 'ok',
            'login' => $args['login'],
            'lang' => $lang
        ]);
    }

    public function userLogout()
    {
        if (!isset($this->_user->username)
            || ($this->_user->username === '')
        ) {
            throw new ForbiddenException('user is not authorized');
        }

        $this->_user->logout($this->_user->username);

        return json_encode('ok');
    }

    public function getUserSettings()
    {
        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $settings = $O->GetOptions($userId);
        unset($O);

        return json_encode($settings);
    }

    public function setUserSettings($settings)
    {
        if (!isset($settings)
            || empty($settings)
            || !is_array($settings)
        ) {
            throw new BadRequestException(json_encode($settings));
        }

        $O = new UserOptions();
        $userId = intval($this->_user->userInfo['id']);
        $O->UpdateOptions($settings, $userId);
        unset($O);

        return json_encode('ok');
    }

    public function userChangeLanguage($data)
    {
        if (!isset($data)
            || !isset($data['lang'])
            || empty($data['lang'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $lang = $data['lang'];

        $L = new Language;
        $L->SetLanguageName($lang);
        unset($L);

        $this->_user->SetUserLanguage($this->_user->username, $lang);

        return json_encode('ok');
    }

    public function getUsers()
    {
        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $userId = intval($this->_user->userInfo['id']);

        $em = EM::get();
        $users = $em->getRepository('Entity\User')->getUsers($userId);

        return json_encode($users);
    }

    public function getUser($args)
    {
        if (!isset($args['id'])) {
            throw new BadRequestException(json_encode($args));
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $requestedUserId = $args['id'];
        $userId = intval($this->_user->userInfo['id']);
        $role = strval($this->_user->userInfo['role']);

        if (UserRepository::isUser($role) && $requestedUserId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        $em = EM::get();

        $user = $em->find('Entity\User', $requestedUserId);
        $creatorId = $user->getCreatorId();

        if (UserRepository::isModerator($role) && $creatorId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        return json_encode($user->get());
    }

    public function getLogo($args)
    {
        if (!isset($args)
            || !isset($args['id'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $creatorId = intval($this->_user->userInfo['id']);
        $userId = intval($args['id']);

        $em = EM::get();

        $user = $em->getRepository('Entity\User')->findOneBy(['id' => $userId]);
        if (!$user) {
            throw new NotFoundException("requested user not found. Id: ". $userId);
        }

        if (!$em->getRepository('Entity\User')->isAvaliable($userId, $creatorId)) {
            throw new ForbiddenException('user is not authorized');
        }

        header('Content-type:image/png');

        return stream_get_contents($user->getLogo());
    }

    public function create($args)
    {
        if (!isset($args['login'])
            || empty($args['login'])
            || !isset($args['pass'])
            || empty($args['pass'])
            || !isset($args['organization'])
            || empty($args['organization'])
            || !isset($args['avaliableFdrs'])
            || empty($args['avaliableFdrs'])
        ) {
            throw new BadRequestException([json_encode($args), 'notAllNecessarySent']);
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $authorId = intval($this->_user->userInfo['id']);

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForInserting = RuntimeManager::storeFile($filePath, 'user-logo');
        $login = $args['login'];
        $avaliableFdrs = $args['avaliableFdrs'];

        if ($this->_user->CheckUserPersonalExist($login)) {
            throw new ForbiddenException(['user already exist', 'alreadyExist']);
        }

        $createdUserId = intval($this->_user->CreateUserPersonal([
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['organization'],
            'logo' => $fileForInserting,
            'id_creator' => $authorId
        ]));

        foreach($avaliableFdrs as $id) {
            $this->_user->SetFDRavailable($createdUserId, intval($id));
        }

        //RuntimeManager::unlinkRuntimeFile($fileForInserting);

        return json_encode('ok');
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

    public function updateUser($data)
    {
        $resMsg = $this->UpdateUserByForm($userIdToUpdate, $form, $file);

        if($resMsg != '') {
            $answ = [
                'status' => 'err',
                'error' => $resMsg
            ];
        }

        return json_encode($answ);
    }

    public function deleteUser($data)
    {
        if (!isset($data) || !isset($data['userIds'])) {
            throw new BadRequestException(json_encode($data));
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

        return json_encode('ok');
    }
}
