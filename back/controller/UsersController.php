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
            'role' => $user->getRole(),
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

    public function getUserAction($id)
    {
        $requestedUserId = intval($id);
        $userId = $this->user()->getId();
        $role = strval($this->user()->getRole());

        if (UserRepository::isUser($role) && $requestedUserId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        $user = $this->em()->find('Entity\User', $requestedUserId);
        $creatorId = $user->getCreatorId();

        if ($this->member()->isModerator($role) && $creatorId !== $userId) {
            throw new ForbiddenException('forbidden info for current user');
        }

        return json_encode($user->get());
    }

    public function createUserAction(
        $login,
        $pass,
        $company,
        $role,
        $avaliableFdrs,
        $email = '',
        $name = '',
        $phone = ''
    ) {
        $authorId = $this->user()->getId();

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForInserting = $this->dic()
            ->get('runtimeManager')
            ->storeFile($filePath, 'user-logo');

        if ($this->em()->getRepository('Entity\User')->findBy(['login' => $login])) {
            throw new ForbiddenException(['user already exist', 'alreadyExist']);
        }

        $createdUser = new \Entity\User;
        $createdUser->set([
            'login' => $login,
            'pass' => $pass,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'lang' => 'en',
            'role' => $role,
            'company' => $company,
            'creator' => $this->user(),
            'logo' => $fileForInserting,
        ]);

        $this->em()->persist($createdUser);
        $this->em()->flush();

        foreach($avaliableFdrs as $id) {
            $fdr = $this->em()->find('Entity\Fdr', $id);

            if ($fdr) {
                $this->dic()->get('userManager')
                    ->setFdrAvailable($createdUser->getId(), $fdr);
            }
        }

        $this->dic()->get('runtimeManager')->unlinkRuntimeFile($fileForInserting);

        return json_encode([
            'id' => $createdUser->getId(),
            'login' => $login,
            'pass' => $pass,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'company' => $company,
            'creatorId' => $authorId,
            'logo' => 'users/getLogo/id/'.$createdUser->getId()
        ]);
    }

    public function updateUserAction(
        $id,
        $login,
        $pass,
        $company,
        $role,
        $avaliableFdrs,
        $email = '',
        $name = '',
        $phone = ''
    ) {
        $userId = $this->user()->getId();
        $userIdToUpdate = intval($id);

        if (!$this->em()->getRepository('Entity\User')->isAvaliable($userIdToUpdate, $userId)) {
            throw new ForbiddenException('current user not able to update this');
        }

        $filePath = strval($_FILES['userLogo']['tmp_name']);
        $fileForUpdating = $this->dic()->get('runtimeManager')
            ->storeFile($filePath, 'user-logo');

        $updatedUser = $this->em()->find('Entity\User', $userIdToUpdate);

        if (!$updatedUser) {
            throw new NotFoundException('user with id '.$userIdToUpdate.' not found');
        }

        $updatedUser->set([
            'login' => $login,
            'pass' => $pass,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'company' => $company,
            'creator' => $this->user(),
            'logo' => $fileForUpdating,
        ]);

        $this->em()->merge($updatedUser);
        $this->em()->flush();

        $this->dic()->get('runtimeManager')->unlinkRuntimeFile($fileForUpdating);

        $fdrsToUser = $this->em()->getRepository('Entity\FdrToUser')->findBy(['userId' => $userIdToUpdate]);
        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $this->em()->remove($fdrToUser);
            }
        }

        foreach($avaliableFdrs as $id) {
            $fdr = $this->em()->find('Entity\Fdr', $id);

            if ($fdr) {
                $this->dic()->get('userManager')
                    ->setFdrAvailable($updatedUser->getId(), $fdr);
            }
        }

        return json_encode([
            'id' => $userIdToUpdate,
            'login' => $login,
            'pass' => $pass,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'company' => $company,
            'creatorId' => $userId,
            'logo' => 'users/getLogo/id/'.$userIdToUpdate
        ]);
    }


    public function deleteUserAction($userId)
    {
        $userIdToDelete = intval($userId);

        if (!$this->em()->getRepository('Entity\User')
                ->isAvaliable($userIdToDelete, $this->user()->getId())
        ) {
            throw new ForbiddenException('current user not able to delete this');
        }

        $user = $this->em()->find('Entity\User', $userIdToDelete);
        if (isset($user)) {
            $this->em()->remove($user);
        }

        $fdrsToUser = $this->em()->getRepository('Entity\FdrToUser')->findBy(
            ['userId' => $userIdToDelete]
        );

        if (isset($fdrToUser)) {
            foreach ($fdrsToUser as $fdrToUser) {
                $this->em()->remove($fdrToUser);
            }
        }

        $flightsToFolders = $this->em()->getRepository('Entity\FlightToFolder')->findBy(['userId' => $userIdToDelete]);
        if (isset($flightToFolder)) {
            foreach ($flightsToFolders as $flightToFolder) {
                $this->em()->remove($flightToFolder);
            }
        }

        $flights = $this->em()->getRepository('Entity\Flight')
            ->findBy(['userId' => $userIdToDelete]);

        foreach ($flights as $flightId) {
            $this->dic()->get('flight')->deleteFlight(intval($flightId), $userIdToDelete);
        }

        $this->em()->flush();

        return json_encode('ok');
    }

    public function getUserActivityAction($userId, $page, $pageSize)
    {
        $userActivityResult = $this->em()->getRepository('Entity\UserActivity')
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

        $total = $this->em()->getRepository('Entity\UserActivity')
            ->createQueryBuilder('userActivity')
            ->select('count(userActivity.id)')
            ->where('userActivity.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return json_encode([
            'rows' => $activity,
            'pages' => round($total / $pageSize)
        ]);
    }
}
