<?php

namespace Component;

use Component\EntityManagerComponent as EM;
use Component\FdrCycloComponent as Cyclo;
use Component\RealConnectionFactory as LinkFactory;

use Entity\FlightEvent;
use Entity\FlightSettlement;
use Entity\Flight;

use Exception;

class EventProcessingComponent
{
    private static $tableSign = '^';
    private static $eventTimeServiceKeys = [
        'startFrameNum',
        'endFrameNum',
        'startTime',
        'endTime'
    ];

    private static $eventRuntimeServiceKey = 'runtime';

    public static function processEvents($flightId, $emitter)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($flightId), 1);
        }

        $em = EM::get();

        $flight = $em->find('Entity\Flight', $flightId);
        $fdr = $flight->getFdr();
        $fdrCode = $fdr->getCode();
        $flightGuid = $flight->getGuid();
        $eventsToFdr = $fdr->getEventsToFdr();

        $startCopyTime = $flight->getStartCopyTime();
        $stepLength = $fdr->getStepLength();
        $eventRows = [];
        $flightEvents = [];

        $link = LinkFactory::create();
        $flightEventTable = FlightEvent::createTable($link, $flightGuid);
        $flightSettlementTable = FlightSettlement::createTable($link, $flightGuid);
        LinkFactory::destroy($link);

        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
        $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);

        $emitter->emit('EventProcessing:start', [count($eventsToFdr)]);

        $ii = 0;
        foreach ($eventsToFdr as $eventToFdr) {
            $substitution = $eventToFdr->getSubstitution();
            $event = $eventToFdr->getEvent();
            $eventSettlements = $event->getEventSettlements();

            $alg = $event->getAlg();

            $eventAlg = self::substituteParams($alg, $substitution,
                $fdrCode, $flightGuid);

            $eventAlg = self::substituteFlightInfo($eventAlg, $flight->get());
            $eventAlg = self::substituteRuntime($eventAlg, $flightGuid);

            $minLength = $event->getMinLength();
            $eventArray = self::executeEvent($eventAlg, $startCopyTime, $stepLength);
            $sections = self::eventArrayToSection($eventArray, $startCopyTime, $stepLength);
            $checkedSections = self::eventLengthCheck($sections, $minLength);

            foreach ($checkedSections as $section) {
                $flightEvent = new FlightEvent;
                $flightEvent->setAttributes([
                    'eventId' => $event->getId(),
                    'startTime' => $section['startTime'],
                    'endTime' => $section['endTime'],
                ]);
                $em->persist($flightEvent);
                $em->flush();

                foreach ($eventSettlements as $settlement) {
                    $settlementAlg = $settlement->getAlg();

                    $settlementAlg = self::substituteParams($settlementAlg, $substitution,
                        $fdrCode, $flightGuid);

                    $settlementAlg = self::substituteFlightInfo($settlementAlg, $flight->get());
                    $settlementAlg = self::substituteRuntime($settlementAlg, $fdrCode);

                    $settlementAlg = self::substituteEventTime($settlementAlg, $section);
                    $value = self::executeSettlement($settlementAlg);

                    $flightSettlement = new FlightSettlement;
                    $flightSettlement->setAttributes([
                        'eventId' => $event->getId(),
                        'flightEventId' => $flightEvent->getId(),
                        'settlementId' => $settlement->getId(),
                        'value' => $value,
                    ]);

                    $em->persist($flightSettlement);
                    $em->flush();
                }
            }

            $ii ++;
            $emitter->emit('EventProcessing:progress', [
                $ii,
                count($eventsToFdr)
            ]);
        }

        $emitter->emit('EventProcessing:end', []);
    }

    private static function executeEvent($queryAlg, $startCopyTime, $stepLength)
    {
        if (!is_string($queryAlg)) {
            throw new Exception("Incorrect queryAlg passed. String is required. Passed: "
                . json_encode($queryAlg), 1);
        }

        if (!is_float($stepLength)) {
            throw new Exception("Incorrect stepLength passed. Float is required. Passed: "
                . json_encode($stepLength), 1);
        }

        $link = LinkFactory::create();
        $resultArr = [];

        if (!$link->multi_query($queryAlg)) {
            error_log("Impossible to execute multiquery: (" .
                $queryAlg . ") " . $link->error);
        }

        do {
            if ($res = $link->store_result()) {
                while ($row = $res->fetch_array()) {
                    //exception alg can return frameNum or frameNum and time
                    if (isset($row['time'])) {
                        $resultArr[] = array(
                            "frameNum" => $row['frameNum'],
                            "time" => $row['time']);
                    } else {
                        $time = ($startCopyTime + $row['frameNum'] * $stepLength) * 1000; //1000 to convert in microsec
                        $resultArr[] = array(
                            "frameNum" => $row['frameNum'],
                            "time" => $time);
                    }
                }

                $res->free();
            }
        } while ($link->more_results() && $link->next_result());

        LinkFactory::destroy($link);

        return $resultArr;
    }

    private static function executeSettlement($queryAlg)
    {
        if (!is_string($queryAlg)) {
            throw new Exception("Incorrect queryAlg passed. String is required. Passed: "
                . json_encode($queryAlg), 1);
        }

        $link = LinkFactory::create();
        $settlementValue = null;

        if (!$link->multi_query($queryAlg)) {
            error_log("Impossible to execute multiquery: (" .
                $queryAlg . ") " . $link->error);
        }


        do {
            if ($res = $link->store_result()) {
                if($row = $res->fetch_array()) {
                    $settlementValue = isset($row[0]) ? $row[0] : null;
                    $res->free();
                }
            }
        } while ($link->more_results() && $link->next_result());

        LinkFactory::destroy($link);

        return $settlementValue;
    }

    private static function eventArrayToSection($resultArr, $startCopyTime, $stepLength)
    {
        if (!is_array($resultArr)) {
            throw new Exception("Incorrect resultArr passed. Array is required. Passed: "
                . json_encode($resultArr), 1);
        }

        if (!is_float($stepLength)) {
            throw new Exception("Incorrect stepLength passed. Float is required. Passed: "
                . json_encode($stepLength), 1);
        }

        $sections = [];
        if(count($resultArr) > 0) {
            $endFrameNum = $resultArr[0]['frameNum'] + 1;
            $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000; //1000 to convert in microsec
            $sections[] = array(
                    "startFrameNum" => $resultArr[0]["frameNum"],
                    "startTime" => $resultArr[0]["time"],
                    "endFrameNum" => $endFrameNum,
                    "endTime" => $endTime);

            for ($jj = 1; $jj < count($resultArr); $jj++)
            {
                $prevFrameNum = $resultArr[$jj - 1]['frameNum'];
                $curFrameNum = $resultArr[$jj]['frameNum'];

                if(($curFrameNum - $prevFrameNum) > 1) {
                    $endFrameNum =  $curFrameNum + 1;
                    $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000;
                    $sections[] = array(
                        "startFrameNum" => $curFrameNum,
                        "startTime" => $resultArr[$jj]["time"],
                        "endFrameNum" => $endFrameNum,
                        "endTime" => $endTime);
                } else {
                    $endTime = ($startCopyTime + $curFrameNum * $stepLength) * 1000;

                    $sections[count($sections) - 1]["endFrameNum"] = $curFrameNum;
                    $sections[count($sections) - 1]["endTime"] = $endTime;
                }
            }
        }

        return $sections;
    }

    private static function eventLengthCheck($sections, $minLength)
    {
        if (!is_array($sections)) {
            throw new Exception("Incorrect sections passed. Array is required. Passed: "
                . json_encode($sections), 1);
        }

        if (!is_int($minLength)) {
            throw new Exception("Incorrect minLength passed. Int is required. Passed: "
                . json_encode($minLength), 1);
        }

        $checkedSections = [];

        if($minLength === 0) {
            return $sections;
        }

        for($jj = 0; $jj < count($sections); $jj++) {
            if(($sections[$jj]["endTime"] - $sections[$jj]["startTime"]) > $minLength) {
                $checkedSections[] = $sections[$jj];
            }
        }

        return $checkedSections;
    }

    private static function substituteParams($alg, $substitution, $fdrCode, $flightGuid)
    {
        if (!is_string($alg)) {
            throw new Exception("Incorrect alg passed. String is required. Passed: "
                . json_encode($alg), 1);
        }

        if (!is_string($fdrCode)) {
            throw new Exception("Incorrect fdrCode passed. String is required. Passed: "
                . json_encode($fdrCode), 1);
        }

        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
                . json_encode($flightGuid), 1);
        }

        $codeToTable = Cyclo::getCodeToTableArray($fdrCode, $flightGuid);
        if ($substitution !== null) {
            $substitutions = json_decode($substitution, true);

            foreach ($substitutions as $substitution) {
                if (strpos('[' . $substitution['index'] . ']', $alg) !== false) {
                    $alg = str_replace('[' . $substitution['index'] . ']',
                        '[' . $substitution['item'] . ']',
                        $alg);
                } else if (strpos('[^' . $substitution['index'] . ']', $alg) !== false) {
                    $alg = str_replace('[^' . $substitution['index'] . ']',
                        '[^' . $substitution['item'] . ']',
                        $alg);
                }
            }
        }

        preg_match_all("/\[[^\]]*\]/", $alg, $matches);
        for($ii = 0; $ii < count($matches); $ii++) {
            foreach ($matches[$ii] as $match) {
                $param = str_replace(['[', '^', ']'], '', $match);

                if (in_array($param, self::$eventTimeServiceKeys)
                    || ($param === self::$eventRuntimeServiceKey)
                ) {
                    continue;
                }

                // if match contains ^ (table special char)
                if (strpos($match, self::$tableSign) !== false) {
                    if (!isset($codeToTable[$param])) {
                        throw new Exception("Params used in query does not exis in cyclo. "
                            . "Param: " . $param . ". Query: " . $alg, 1);
                    }

                    $alg = str_replace($match, $codeToTable[$param], $alg);
                } else {
                    $alg = str_replace($match, $param, $alg);
                }
            }
        }

        return $alg;
    }

    private static function substituteFlightInfo($alg, $flightInfo)
    {
        foreach ($flightInfo as $flightInfoKey => $flightInfoVal) {
            $alg = str_replace("[".$flightInfoKey."]", $flightInfoVal, $alg);
        }

        return $alg;
    }

    private static function substituteRuntime($alg, $guid)
    {
        return str_replace(self::$eventRuntimeServiceKey, $guid, $alg);
    }

    private static function substituteEventTime($alg, $section)
    {
        foreach (self::$eventTimeServiceKeys as $item) {
            if (!isset($section[$item])) {
                throw new Exception("Event section key does not exist."
                    . " Avaliable section keys: " . json_decode(self::$eventTimeServiceKeys)
                    . " Section: ". json_decode(self::$eventTimeServiceKeys), 1);
            }

            $alg = str_replace("[".$item."]", $section[$item], $alg);
        }

        return $alg;
    }

}
