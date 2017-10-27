<?php

namespace Component;

use Exception;

class FlightComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Component\FdrComponent
     */
    private $fdrComponent;

    /**
     * @Inject
     * @var Entity\FdrAnalogParam
     */
    private $FdrAnalogParam;

    /**
     * @Inject
     * @var Entity\FdrBinaryParam
     */
    private $FdrBinaryParam;

    /**
     * @Inject
     * @var Entity\FlightSettlement
     */
    private $FlightSettlement;

    /**
     * @Inject
     * @var Entity\FlightEvent
     */
    private $FlightEvent;

    /**
     * @Inject
     * @var Entity\CalibrationParam
     */
    private $CalibrationParam;

    public function insert($guid, $flightInfo, $frdId, $userId, $calibrationId)
    {
        $user = $this->em()->find('Entity\User', $userId);
        $fdr = $this->em()->find('Entity\Fdr', $frdId);
        $calibration = $this->em()->find('Entity\Calibration', $calibrationId);

        $copyCreationTime = $flightInfo['copyCreationTime'];
        $copyCreationDate = $flightInfo['copyCreationDate'];

        if (strlen($copyCreationTime) > 5) {
            $flightInfo['startCopyTime'] = strtotime($copyCreationDate . ' ' . $copyCreationTime);
        } else {
            $flightInfo['startCopyTime'] = strtotime($copyCreationDate . ' ' . $copyCreationTime . ':00');
        }

        if ($flightInfo['performer'] === null) {
            $flightInfo['performer'] = $user->getLogin();
        }

        $flight = $this->em()->getRepository('Entity\Flight')
            ->insert($guid, $flightInfo, $fdr, $user, $calibration);

        if ($this->member()->isUser()) {
            $creator = $this->user()->getCreator();

            if ($creator) {
                $this->em()->getRepository('Entity\FlightToFolder')
                    ->insert(0, $creator->getId(), $flight);
            }
        }

        if ($this->member()->isUser()
            || $this->member()->isLocal()
            || $this->member()->isModerator()
        ) {
            $this->em()->getRepository('Entity\FlightToFolder')
                ->insert(0, $this->user()->getId(), $flight);
        }

        $admins = $this->em()->getRepository('Entity\User')->getAdmins();

        foreach ($admins as $user) {
            $this->em()->getRepository('Entity\FlightToFolder')
                ->insert(0, $user->getId(), $flight);
        }

        return $flight;
    }

    public function deleteFlight($flightId, $userId)
    {
        $criteria = ['id' => $flightId];

        if (!$this->dic()->get('user')->isAdmin()) {
            $criteria['userId'] = $userId;
        }

        $flightToFolder = $this->em()
            ->getRepository('Entity\FlightToFolder')
            ->findBy($criteria);

        if (!$flightToFolder) {
            return;
        }

        foreach ($flightToFolder as $item) {
            $em->remove($item);
            $em->flush();
        }

        if (!$this->rbac()->check('deleteFlightIrretrievably')) {
            return;
        }

        $flight = $this->em()
            ->getRepository('Entity\Flight')
            ->findOneBy([
                'id' => $flightId,
                'userId' => $userId
            ]);

        $fdr = $flight->getFdr();
        $guid = $flight->getGuid();

        $analogPrefixes = $fdrComponent->getAnalogPrefixes($fdr->getId());
        $binaryPrefixes = $fdrComponent->getBinaryPrefixes($fdr->getId());

        $tables = [];
        foreach ($analogPrefixes as $num) {
            $tables[] = '_' . $guid . '_' . $this->FdrAnalogParam::$prefix . '_' . $num;
        }

        foreach ($binaryPrefixes as $num) {
            $tables[] = '_' . $guid . '_' . $this->FdrBinaryParam::$prefix . '_' . $num;
        }

        $link = $this->connection()->create('flights');
        $tables[] = $this->FlightSettlement::getTable($link, $guid);
        $tables[] = $this->FlightEvent::getTable($link, $guid);
        $tables[] = $this->CalibrationParam::getTable($link, $guid);

        foreach ($tables as $table) {
            $this->connection()->drop($link, $table);
        }

        $this->connection()->destroy($link);

        $em->remove($flight);
        $em->flush();

        return true;
    }

    public function getFlightEvents($flightId, $flightGuid = '')
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

        if (!isset($flightEventTable)
            || !isset($flightSettlementTable)
        ) {
            return [];
        }

        $em = EM::get();
        $em->getClassMetadata('Entity\FlightEvent')->setTableName($flightEventTable);
        $em->getClassMetadata('Entity\FlightSettlement')->setTableName($flightSettlementTable);
        $events = $em->getRepository('Entity\FlightEvent')->findAll();

        return $events;
    }

    public function getFlightSettlements($flightId, $flightGuid = '')
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flight id passed", 1);
        }

        if ($flightGuid === '') {
            $em = EM::get();
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

    public function createParamTables($flightUid, $paramCyclo, $binaryCyclo)
    {
        $tables = [
            'params' => [],
            'binary' => [],
        ];

        $link = $this->connection()->create('flights');

        foreach ($paramCyclo as $prefix => $cyclo) {
            $table = $flightUid."_ap_".$prefix;
            $tables['params'][] = $table;

            $query = "CREATE TABLE `".$table."` (`frameNum` MEDIUMINT, `time` BIGINT";

            foreach ($cyclo as $param) {
                $query .= ", `".$param["code"]."` FLOAT(7,2)";
            }

            $query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
                    "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

            $stmt = $link->prepare($query);
            $stmt->execute();
            $stmt->close();
        }

        foreach ($binaryCyclo as $prefix => $prefixCyclo) {
            $table = $flightUid."_bp_".$prefix;
            $tables['binary'][] = $table;

            $query = "CREATE TABLE `".$table."` (`frameNum` MEDIUMINT, `time` BIGINT, `code` varchar(255)) " .
                "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            $stmt->execute();
            $stmt->close();
        }

        $this->connection()->destroy($link);

        return $tables;
    }
}
