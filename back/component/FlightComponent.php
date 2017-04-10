<?php

namespace Component;

use Model\User;
use Model\Flight;
use Model\Fdr;
use Model\Folder;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Component\EntityManagerComponent as EM;
use Component\RealConnectionFactory as LinkFactory;

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
            $fdrId = intval($flightInfo["id_fdr"]);

            $fdr = new Fdr;
            $fdrInfo = $fdr->getFdrInfo($fdrId);
            $prefixApArr = $fdr->GetBruApCycloPrefixes($fdrId);
            $prefixBpArr = $fdr->GetBruBpCycloPrefixes($fdrId);
            unset($fdr);

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

    public static function getFlightEvents($flightId, $flightGuid = '')
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($sections), 1);
        }

        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. Integer is required. Passed: "
                . json_encode($sections), 1);
        }

        if ($flightGuid === '') {
            $flight = $em->find('Entity\Flight', $flightId);
            $flightGuid = $flight->getGuid();
        }

        $link = LinkFactory::create();
        $flightEventTable = FlightEvent::getTable($link, $flightGuid);
        $flightSettlementTable = FlightSettlement::getTable($link, $flightGuid);
        LinkFactory::destroy($link);

        $em = EM::get();
        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
        $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);
        $events = $em->getRepository('Entity\FlightEvent')->findAll();

        return $events;
    }

    public static function getFlightSettlements($flightId, $flightGuid = '')
    {
        if(!is_int($flightId)) {
            throw new Exception("Incorrect flight id passed", 1);
        }

        if ($flightGuid === '') {
            $flight = $em->find('Entity\Flight', $flightId);
            $flightGuid = $flight->getGuid();
        }

        $flightEvents = self::getFlightEvents($flightId, $flightGuid);

        $allFlightSettlements = [];
        foreach ($flightEvents as $flightEvent) {
            $flightSettlements = $flightEvent->getFlightSettlements();
            foreach ($flightSettlements as $flightSettlement) {
                $allFlightSettlements[] = $flightSettlement;
            }
        }

        return $allFlightSettlements;
    }
}
