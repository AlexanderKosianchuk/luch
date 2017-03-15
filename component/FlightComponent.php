<?php

namespace Component;

use Model\User;
use Model\Flight;
use Model\Fdr;
use Model\Folder;

use Entity\FlightEvent;
use Entity\FlightSettlement;

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

            $prefixes = [];
            foreach ($prefixApArr as $num) {
                $prefixes[] = '_ap_'.$num;
            }
            foreach ($prefixBpArr as $num) {
                $prefixes[] = '_bp_'.$num;
            }
            $prefixes[] = FlightSettlement::getPrefix();
            $prefixes[] = FlightEvent::getPrefix();
            $prefixes[] = '_ex';

            $Fl->DeleteFlight($flightId, $prefixes);

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
