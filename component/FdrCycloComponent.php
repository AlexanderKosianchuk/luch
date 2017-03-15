<?php

namespace Component;

use Component\EntityManagerComponent as EM;
use Doctrine\ORM\AbstractQuery as AbstractQuery;

use Exception;

class FdrCycloComponent
{
    private static $_apPrefix = 'ap';
    private static $_bpPrefix = 'bp';

    private static $_codeToTable = [];

    public static function get($fdrCode)
    {
        if (!is_string($fdrCode)) {
            throw new Exception("Incorrect fdrCode passed. String is required. Passed: "
                . json_encode($fdrCode), 1);
        }

        if (!self::checkTableExist($fdrCode, self::$_apPrefix)) {
            throw new Exception("Analog params cyclo table " . $fdrCode . '_' . self::$_apPrefix
                . " is not exist.");
        }

        if (!self::checkTableExist($fdrCode, self::$_bpPrefix)) {
            throw new Exception("Analog params cyclo table " . $fdrCode . '_' . self::$_bpPrefix
                . " is not exist.");
        }

        $conn = EM::get()->getConnection();
        $sql = "SELECT `code`, `prefix`, '".self::$_apPrefix."' as `type` FROM " . $fdrCode . '_' . self::$_apPrefix;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        $sql = "SELECT `code`, `prefix`, '".self::$_bpPrefix."' as `type` FROM " . $fdrCode . '_' . self::$_bpPrefix;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = array_merge($res, $stmt->fetchAll());

        return $res;
    }

    public static function getCodeToTableArray($fdrCode, $flightTable)
    {
        if (!is_string($fdrCode)) {
            throw new Exception("Incorrect fdrCode passed. String is required. Passed: "
                . json_encode($fdrCode), 1);
        }

        if (count(self::$_codeToTable) > 0) {
            return self::$_codeToTable;
        }

        $cyclo = self::get($fdrCode);

        foreach ($cyclo as $param) {
            self::$_codeToTable[$param['code']] = $flightTable . '_' . $param['type'] . '_' . $param['prefix'];
        }

        return self::$_codeToTable;
    }

    private static function checkTableExist($fdrCode, $prefix)
    {
        $em = EM::get();
        $schemaManager = $em->getConnection()->getSchemaManager();
        return $schemaManager->tablesExist([$fdrCode . '_' . $prefix]);
    }

}
