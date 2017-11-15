<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class UsersController extends BaseController
{
    public function loginAction ($login, $pass = '')
    {
        $lang = 'en';

        $userId = $this->member()->signIn(
            $login,
            $pass
        );

        if ($userId === null) {
            return json_encode([
                'status' => 'fail',
                'message' => 'userUnexist',
                'messageText' => 'User unexist'
            ]);
        }

        $user = $this->em()->find('Entity\User', $userId);

        return json_encode([
            'status' => 'ok',
            'login' => $user->getLogin(),
            'lang' => strtolower($user->getLang())
        ]);
    }

    public function logoutAction()
    {
        $this->member()->logout();

        return json_encode('ok');
    }

    public function getUserSettingsAction()
    {
        $userId = $this->user()->getId();

        return json_encode(
            $this->dic()->get('userSettings')->getSettings($userId)
        );
    }

    public function setUserSettingsAction($settings)
    {
        $this->dic()->get('userSettings')->updateSettings($settings);

        return json_encode('ok');
    }

    public function userChangeLanguageAction($lang)
    {
        $this->user()->setLanguage($lang);
        return json_encode('ok');
    }

    public function getUsersAction()
    {
        return json_encode(
            $this->dic()
                ->get('userManager')
                ->getUsers()
        );
    }

    public function getLogoAction($id)
    {
        $user = $this->em()->find('Entity\User', $id);

        if (!$user) {
            throw new NotFoundException("requested user not found. Id: ".$id);
        }

        $creatorId = intval($this->user()->getId());

        if (!$this->em()->getRepository('Entity\User')->isAvaliable($user->getId(), $creatorId)) {
            throw new ForbiddenException('user is not authorized');
        }

        header('Content-type:image/png');

        return stream_get_contents($user->getLogo());
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

    public function create($args)
    {
        if (!isset($args['login'])
            || empty($args['login'])
            || !isset($args['pass'])
            || empty($args['pass'])
            || !isset($args['company'])
            || empty($args['company'])
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
            'company' => $args['company'],
            'creatorId' => $authorId,
            'logo' => $fileForInserting,
        ]));

        foreach($avaliableFdrs as $id) {
            $this->_user->SetFDRavailable($createdUserId, intval($id));
        }

        RuntimeManager::unlinkRuntimeFile($fileForInserting);

        $em = EM::get();
        $user = $em->find('Entity\User', $createdUserId);

        return json_encode([
            'id' => $createdUserId,
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['company'],
            'creatorId' => $authorId,
            'logo' => $em->getRepository('Entity\User')::getLogoUrl($createdUserId)
        ]);
    }

    public function update($args)
    {
        if (!isset($args['id'])
            || empty($args['id'])
            || !isset($args['pass'])
            || empty($args['pass'])
            || !isset($args['avaliableFdrs'])
            || empty($args['avaliableFdrs'])
        ) {
            throw new BadRequestException([json_encode($args), 'notAllNecessarySent']);
        }

        if (!isset($this->_user->userInfo)) {
            throw new ForbiddenException('user is not authorized');
        }

        $userId = intval($this->_user->userInfo['id']);
        $userIdToUpdate = intval($args['id']);

        $em = EM::get();

        if (!$em->getRepository('Entity\User')->isAvaliable($userIdToUpdate, $userId)) {
            throw new ForbiddenException('current user not able to update this');
        }

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForUpdating = RuntimeManager::storeFile($filePath, 'user-logo');
        $login = $args['login'];
        $avaliableFdrs = $args['avaliableFdrs'];

        $user = $em->find('Entity\User', $userIdToUpdate);

        if (!$user) {
            throw new NotFoundException('user with id '.$userIdToUpdate.' not found');
        }

        $this->_user->UpdateUserPersonal(
            $userIdToUpdate,
            [
                'login' => $login,
                'pass' => $args['pass'],
                'name' => $args['name'],
                'email' => $args['email'],
                'phone' => $args['phone'],
                'role' => $args['role'],
                'company' => $args['company'],
                'id_creator' => $userId,
                'logo' => $fileForUpdating
            ]
        );

        RuntimeManager::unlinkRuntimeFile($fileForUpdating);

        $fdrsToUser = $em->getRepository('Entity\FdrToUser')->findBy(['userId' => $userIdToUpdate]);
        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $em->remove($fdrToUser);
            }
        }

        foreach($avaliableFdrs as $id) {
            $this->_user->SetFDRavailable($userIdToUpdate, intval($id));
        }

        $em->flush();

        return json_encode([
            'id' => $userIdToUpdate,
            'login' => $login,
            'pass' => $args['pass'],
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'],
            'role' => $args['role'],
            'company' => $args['company'],
            'creatorId' => $userId,
            'logo' => $em->getRepository('Entity\User')::getLogoUrl($userIdToUpdate)
        ]);
    }


    public function delete($args)
    {
        if (!isset($args['userId'])
            || empty($args['userId'])
            || !is_int(intval($args['userId']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $userId = intval($this->_user->userInfo['id']);
        $userIdToDelete = intval($args['userId']);

        $em = EM::get();

        if (!$em->getRepository('Entity\User')->isAvaliable($userIdToDelete, $userId)) {
            throw new ForbiddenException('current user not able to delete this');
        }

        $user = $em->find('Entity\User', $userIdToDelete);
        if (isset($user)) {
            $em->remove($user);
        }

        $fdrsToUser = $em->getRepository('Entity\FdrToUser')->findBy(['userId' => $userIdToDelete]);
        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $em->remove($fdrToUser);
            }
        }

        $flightsToFolders = $em->getRepository('Entity\FlightToFolder')->findBy(['userId' => $userIdToDelete]);
        if (isset($flightToFolder)) {
            foreach ($flightsToFolders as $flightToFolder) {
                $em->remove($flightToFolder);
            }
        }

        $flights = $em->getRepository('Entity\Flight')->findBy(['id_user' => $userIdToDelete]);
        $FC = new FlightComponent;
        foreach ($flights as $flightId) {
            $FC->DeleteFlight(intval($flightId), $userIdToDelete);
        }

        $em->flush();

        return json_encode('ok');
    }

    public function getUserActivity($args)
    {
        if (!isset($args['userId'])
            || empty($args['userId'])
            || !is_int(intval($args['userId']))
            || !isset($args['page'])
            || empty($args['page'])
            || !is_int(intval($args['page']))
            || !isset($args['pageSize'])
            || empty($args['pageSize'])
            || !is_int(intval($args['pageSize']))
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $userId = $args['userId'];
        $page = $args['page'];
        $pageSize = $args['pageSize'];

        $em = EM::get();

        $userActivityResult = $em->getRepository('Entity\UserActivity')
            ->findBy(
                ['userId' => $userId],
                ['date' => 'DESC'],
                $pageSize,
                ($page - 1) * $pageSize
            );

        $activity = [];
        foreach($userActivityResult as $item) {
            $activity[] = $item->get();
        }

        $total = $em->getRepository('Entity\UserActivity')
            ->createQueryBuilder('userActivity')
            ->select('count(userActivity.id)')
            ->where('userActivity.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        echo json_encode([
            'rows' => $activity,
            'pages' => round($total / $pageSize)
        ]);
    }
}
