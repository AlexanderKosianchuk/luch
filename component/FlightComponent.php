<?php

namespace Component;

use Model\User;
use Model\Flight;
use Model\Fdr;
use Model\Folder;

use Exception;

class FlightComponent
{
    public function DeleteFlight($flightId, $userId)
    {
        if(!is_int($flightId)) {
            throw new Exception("Incorrect flight id passed", 1);
        }

        if(!is_int($userId)) {
            throw new Exception("Incorrect user id passed", 1);
        }

        $U = new User;
        $userInfo = $U->GetUserInfo($userId);
        $role = $userInfo['role'];
        if (User::isAdmin($role)) {
            $Fl = new Flight;
            $flightInfo = $Fl->GetFlightInfo($flightId);
            $bruType = $flightInfo["bruType"];

            $Bru = new Fdr;
            $fdrInfo = $Bru->GetBruInfo($bruType);
            $prefixApArr = $Bru->GetBruApCycloPrefixes($bruType);
            $prefixBpArr = $Bru->GetBruBpCycloPrefixes($bruType);

            $Fl->DeleteFlight($flightId, $prefixApArr, $prefixBpArr);

            $Fd = new Folder;
            $Fd->DeleteFlightFromFolders($flightId);
            unset($Fd);
        } else {
            $Fd = new Folder;
            $Fd->DeleteFlightFromFolderForUser($flightId, $userId);
            unset($Fd);
        }

        unset($U);
        return true;
    }
}
