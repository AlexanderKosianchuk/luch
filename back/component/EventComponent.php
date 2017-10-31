<?php

namespace Component;

use Exception;

class EventComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Entity\FdrEventOld
     */
    private $FdrEventOld;

    /**
     * @Inject
     * @var Entity\FlightEventOld
     */
    private $FlightEventOld;

    public function createOldEventsTable($guid)
    {
        $flightExTableName = $guid.$this->FlightEventOld::getPrefix();

        $link = $this->connection()->create('flights');

        $this->connection()->drop($flightExTableName, null, $link);

        $query = "CREATE TABLE `".$flightExTableName."` (`id` INT NOT NULL AUTO_INCREMENT, "
            . " `frameNum` INT,"
            . " `startTime` BIGINT,"
            . " `endFrameNum` INT,"
            . " `endTime` BIGINT,"
            . " `refParam` VARCHAR(255),"
            . " `code` VARCHAR(255),"
            . " `excAditionalInfo` TEXT,"
            . " `falseAlarm` BOOL DEFAULT 0,"
            . " `userComment` TEXT,"
            . " PRIMARY KEY (`id`))"
            . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $this->connection()->destroy($link);

        return $flightExTableName;
    }

    public function isOldProcessingEventsTableExist($code)
    {
        $table = $code.$this->FdrEventOld::getPrefix();

        return $this->connection()->isExist($table, 'fdrs');
    }

    private function setupFdrEventOldEntity($code)
    {
        $link = $this->connection()->create('fdrs');
        $table = $this->FdrEventOld::getTable($link, $code);
        $this->connection()->destroy($link);

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrEventOld')
            ->setTableName($table);
    }

    public function getRefParams($code)
    {

        $this->setupFdrEventOldEntity($code);

        return $this->em('fdrs')
            ->getRepository('Entity\FdrEventOld')
            ->createQueryBuilder('fdrEventOld')
            ->select('DISTINCT fdrEventOld.refParam')
            ->getQuery()
            ->getResult();
    }

    public function getOldEvents($code)
    {
        $this->setupFdrEventOldEntity($code);

        return $this->em('fdrs')
            ->getRepository('Entity\FdrEventOld')
            ->createQueryBuilder('fdrEventOld')
            ->getQuery()
            ->getResult();
    }

}
