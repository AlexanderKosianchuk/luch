<?php

namespace Component;

use Exception;

class EventProcessingComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Entity\FlightEventOld
     */
    private $FlightEventOld;

    /**
     * @Inject
     * @var Entity\FlightEvent
     */
    private $FlightEvent;

    /**
     * @Inject
     * @var Entity\FlightSettlement
     */
    private $FlightSettlement;

    /**
     * @Inject
     * @var Component\RuntimeManager
     */
    private $runtimeManager;

    /**
     * @Inject
     * @var Component\EventComponent
     */
    private $eventComponent;

    /**
     * @Inject
     * @var Component\FdrComponent
     */
    private $fdrComponent;

    /**
     * @Inject
     * @var Evenement\EventEmitter
     */
    private $EventEmitter;

    private Static $tableSign = '^';
    private Static $eventTimeServiceKeys = [
        'startFrameNum',
        'endFrameNum',
        'startTime',
        'endTime'
    ];

    private static $eventRuntimeServiceKey = 'runtime';

    private function reportProgress($progress, $guid)
    {
        $this->runtimeManager->writeToRuntimeTemporaryFile(
            $this->params()->folders->uploadingStatus,
            $guid,
            $progress, // % of total progress (100%)
            'json',
            true
        );
    }

    public function analyze(\Entity\Flight $flight)
    {
        $tmpStatus = 50;
        $emitter = new $this->EventEmitter();
        $that = $this;

        $emitter->on('EventProcessing:progress',
            function ($progress) use ($that, $flight, $tmpStatus)
        {
            if ($progress > $tmpStatus) {
                $tmpStatus = $progress;
                $that->reportProgress($progress, $flight->getGuid());
            }
        });

        $this->reportProgress(50, $flight->getGuid());
        $this->processEventsOld($flight, $emitter);
        $this->reportProgress(75, $flight->getGuid());
        $this->processEvents($flight, $emitter);
        $this->reportProgress(100, $flight->getGuid());
    }

    public function processEventsOld(\Entity\Flight $flight, $emitter)
    {
        $fdr = $flight->getFdr();
        $oldProcessingEventsNeed = $this->eventComponent->isOldProcessingEventsTableExist($fdr->getCode());

        if (!$oldProcessingEventsNeed) {
            return;
        }

        $table = $this->eventComponent->createOldEventsTable($flight->getGuid());
        $events = $this->eventComponent->getOldEvents($fdr->getCode());

        for ($ii = 0; $ii < count($events); $ii++) {
            $status = round(50 + (25 / count($events) * $ii));
            $emitter->emit('EventProcessing:progress', [$status]);

            $this->performProcessingByOldEvents(
                $events[$ii]->get(true),
                $flight->get(true),
                $table,
                $this->fdrComponent->getAnalogTable($flight->getGuid()),
                $this->fdrComponent->getBinaryTable($flight->getGuid()),
                $fdr->getStepLength()
            );
        }
    }

    public function processEvents(\Entity\Flight $flight, $emitter)
    {
        $fdr = $flight->getFdr();
        $fdrCode = $fdr->getCode();
        $flightGuid = $flight->getGuid();
        $eventsToFdr = $fdr->getEventsToFdr();

        $startCopyTime = $flight->getStartCopyTime();
        $stepLength = $fdr->getStepLength();
        $eventRows = [];
        $flightEvents = [];

        $link = $this->connection()->create('flights');
        $flightEventTable = $this->FlightEvent::createTable($link, $flightGuid);
        $flightSettlementTable = $this->FlightSettlement::createTable($link, $flightGuid);
        $this->connection()->destroy($link);

        $this->em()->getClassMetadata('Entity\FlightEvent')->setTableName('`'.$flightEventTable.'`');
        $this->em()->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);

        $ii = 0;
        foreach ($eventsToFdr as $eventToFdr) {
            $substitution = $eventToFdr->getSubstitution();
            $event = $eventToFdr->getEvent();
            $eventSettlements = $event->getEventSettlements();

            $alg = $event->getAlg();

            $eventAlg = $this->substituteParams(
                $alg,
                $substitution,
                $fdr->getId(),
                $flightGuid
            );

            $eventAlg = $this->substituteFlightInfo($eventAlg, $flight->get());
            $eventAlg = $this->substituteRuntime($eventAlg, $flightGuid);

            $minLength = $event->getMinLength();
            $eventArray = $this->executeEvent($eventAlg, $startCopyTime, $stepLength);
            $sections = $this->eventArrayToSection($eventArray, $startCopyTime, $stepLength);
            $checkedSections = $this->eventLengthCheck($sections, $minLength);

            foreach ($checkedSections as $section) {
                $flightEvent = new $this->FlightEvent;
                $flightEvent->setAttributes([
                    'eventId' => $event->getId(),
                    'startTime' => $section['startTime'],
                    'endTime' => $section['endTime'],
                ]);

                $this->em('flights')->persist($flightEvent);
                $this->em('flights')->flush();

                foreach ($eventSettlements as $settlement) {
                    $settlementAlg = $settlement->getAlg();

                    $settlementAlg = $this->substituteParams(
                        $settlementAlg,
                        $substitution,
                        $fdr->getId(),
                        $flightGuid
                    );

                    $settlementAlg = $this->substituteFlightInfo($settlementAlg, $flight->get());
                    $settlementAlg = $this->substituteRuntime($settlementAlg, $fdrCode);

                    $settlementAlg = $this->substituteEventTime($settlementAlg, $section);
                    $value = $this->executeSettlement($settlementAlg);

                    $flightSettlement = new $this->FlightSettlement;
                    $flightSettlement->setAttributes([
                        'eventId' => $event->getId(),
                        'flightEventId' => $flightEvent->getId(),
                        'settlementId' => $settlement->getId(),
                        'value' => $value,
                        'flightEvent' => $flightEvent,
                    ]);

                    $this->em('flights')->persist($flightSettlement);
                    $this->em('flights')->flush();
                }
            }

            $ii ++;
            $status = round(75 + (25 / count($eventsToFdr) * $ii));
            $emitter->emit('EventProcessing:progress', [$status]);
        }
    }

    private function executeEvent($queryAlg, $startCopyTime, $stepLength)
    {
        if (!is_string($queryAlg)) {
            throw new Exception("Incorrect queryAlg passed. String is required. Passed: "
                . json_encode($queryAlg), 1);
        }

        if (!is_float($stepLength)) {
            throw new Exception("Incorrect stepLength passed. Float is required. Passed: "
                . json_encode($stepLength), 1);
        }

        $link = $this->connection()->create('flights');
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

        $this->connection()->destroy($link);

        return $resultArr;
    }

    private function executeSettlement($queryAlg)
    {
        if (!is_string($queryAlg)) {
            throw new Exception("Incorrect queryAlg passed. String is required. Passed: "
                . json_encode($queryAlg), 1);
        }

        $link = $this->connection()->create('flights');
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

        $this->connection()->destroy($link);

        return $settlementValue;
    }

    private function eventArrayToSection($resultArr, $startCopyTime, $stepLength)
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

    private function eventLengthCheck($sections, $minLength)
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

    private function substituteParams($alg, $substitution, $fdrId, $flightGuid)
    {
        if (!is_string($alg)) {
            throw new Exception("Incorrect alg passed. String is required. Passed: "
                . json_encode($alg), 1);
        }

        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
                . json_encode($fdrId), 1);
        }

        if (!is_string($flightGuid)) {
            throw new Exception("Incorrect flightGuid passed. String is required. Passed: "
                . json_encode($flightGuid), 1);
        }

        $codeToTable = $this->fdrComponent
            ->getCodeToTableArray($fdrId, $flightGuid);

        if ($substitution !== null) {
            $substitutions = json_decode($substitution, true);

            foreach ($substitutions as $key => $substitution) {
                $alg = str_replace('[' . $key . ']',
                    '[' . $substitution . ']',
                    $alg);

                $alg = str_replace('[^' . $key . ']',
                    '[^' . $substitution . ']',
                    $alg);
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

    private function substituteFlightInfo($alg, $flightInfo)
    {
        foreach ($flightInfo as $flightInfoKey => $flightInfoVal) {
            if (is_string($flightInfoVal)) {
                $alg = str_replace("[".$flightInfoKey."]", $flightInfoVal, $alg);
            }
        }

        return $alg;
    }

    private function substituteRuntime($alg, $guid)
    {
        return str_replace(self::$eventRuntimeServiceKey, $guid, $alg);
    }

    private function substituteEventTime($alg, $section)
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

    public function performProcessingByOldEvents(
        $event,
        $flightInfo,
        $tableName,
        $apTableName,
        $bpTableName,
        $stepLength
    ) {
        $eventsList = array();
        $link = $this->connection()->create('flights');
        $link2 = $this->connection()->create('flights');
        $link3 = $this->connection()->create('flights');

        $startCopyTime = $flightInfo['startCopyTime'];

        $query = $event['alg'];

        $query = str_replace("[ap]", $apTableName, $query);
        $query = str_replace("[bp]", $bpTableName, $query);
        $query = str_replace("[ex]", $tableName, $query);

        foreach ($flightInfo as $key => $val) {
            if (is_string($val)) {
                $query = str_replace("[".$key."]", $val, $query);
            }

            if (is_array($val)) {
                foreach ($val as $aditionalKey => $aditionalValue) {
                    $query = str_replace("[".$aditionalKey."]", $aditionalValue, $query);
                }
            }
        }

        $aditionalQueries = [];

        //check delimiter exist in string
        if (strpos($query,'#') !== false) {
            $query = explode("#", $query);
            $queryAlg = $query[0];

            if ((count($query) - 1) % 2 == 0) {
                for ($i = 1; $i < count($query); $i+=2) {
                    $aditionalQueries[] = array(
                        "aditionalInfoComment" => $query[$i],
                        "aditionalInfoAlg" => $query[$i + 1]);
                }
            }
            //else no aditional info
        } else {
            $queryAlg = $query;
        }


        if (!$link->multi_query($queryAlg)) {
            //err log
            error_log("Impossible to execute multiquery: (" .
                $queryAlg . ") " . $link->error);
        }

        do {
            if ($res = $link->store_result()) {
                $resultArr = array();
                while($row = $res->fetch_array()) {
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

                //reorganize arr to simplify inserting
                $normalizedResultArr = array();
                if (count($resultArr) > 0) {
                    $endFrameNum = $resultArr[0]['frameNum'] + 1;
                    $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000; //1000 to convert in microsec
                    $normalizedResultArr[] = array(
                            "frameNum" => $resultArr[0]["frameNum"],
                            "startTime" => $resultArr[0]["time"],
                            "endFrameNum" => $endFrameNum,
                            "endTime" => $endTime);

                    for($j = 1; $j < count($resultArr); $j++) {
                        $prevFrameNum = $resultArr[$j - 1]['frameNum'];
                        $curFrameNum = $resultArr[$j]['frameNum'];

                        if (($curFrameNum - $prevFrameNum) > 1) {
                            $endFrameNum =  $curFrameNum + 1;
                            $endTime = ($startCopyTime + $endFrameNum * $stepLength) * 1000;
                            $normalizedResultArr[] = array(
                                "frameNum" => $curFrameNum,
                                "startTime" => $resultArr[$j]["time"],
                                "endFrameNum" => $endFrameNum,
                                "endTime" => $endTime);
                        } else {
                            $endTime = ($startCopyTime + $curFrameNum * $stepLength) * 1000;

                            $normalizedResultArr[count($normalizedResultArr) - 1]["endFrameNum"] = $curFrameNum;
                            $normalizedResultArr[count($normalizedResultArr) - 1]["endTime"] = $endTime;
                        }
                    }
                }

                //remove events less minLength
                $checkedNormalizedResultArr = array();
                if($event["minLength"] != 0) {
                    for($j = 0; $j < count($normalizedResultArr); $j++) {
                        if (($normalizedResultArr[$j]["endTime"] - $normalizedResultArr[$j]["startTime"]) > $event["minLength"]) {
                            $checkedNormalizedResultArr[] = $normalizedResultArr[$j];
                        }
                    }
                    $normalizedResultArr = $checkedNormalizedResultArr;
                }

                for ($j = 0; $j < count($normalizedResultArr); $j++) {
                    $aditionalInfoStr = "";

                    for ($k = 0; $k < count($aditionalQueries); $k++) {
                        $aditionalInfoStr .= $aditionalQueries[$k]["aditionalInfoComment"] . " ";
                        $query = $aditionalQueries[$k]["aditionalInfoAlg"];

                        //in aditionalQueries we can use this variables
                        //startFrameNum
                        //endFrameNum
                        //startTime
                        //endTime

                        $query = str_replace("[startFrameNum]", $normalizedResultArr[$j]['frameNum'], $query);
                        $query = str_replace("[endFrameNum]", $normalizedResultArr[$j]['endFrameNum'], $query);
                        $query = str_replace("[startTime]", $normalizedResultArr[$j]['startTime'], $query);
                        $query = str_replace("[endTime]", $normalizedResultArr[$j]['endTime'], $query);

                        $result = $link3->query($query);

                        $excRefParamsList = array();
                        $row = $result->fetch_array();

                        $aditionalInfoStr .= $row[0] . "; ";
                    }

                    $query = "INSERT INTO `".$tableName."` (`frameNum`,".
                            "`startTime`, ".
                            "`endFrameNum`, ".
                            "`endTime`, ".
                            "`refParam`, ".
                            "`code`, ".
                            "`excAditionalInfo`) ".
                            "VALUES (".$normalizedResultArr[$j]['frameNum'].",".
                            $normalizedResultArr[$j]['startTime'].",".
                            $normalizedResultArr[$j]['endFrameNum'].",".
                            $normalizedResultArr[$j]['endTime'].",'".
                            $event['refParam']."','".
                            $event['code']."','".
                            $aditionalInfoStr."');";
                    $stmt = $link2->prepare($query);
                    $stmt->execute();
                }

                $res->free();
            }
        } while ($link->more_results() && $link->next_result());

        $this->connection()->destroy($link);
        $this->connection()->destroy($link2);
        $this->connection()->destroy($link3);

        return $eventsList;
    }
}
