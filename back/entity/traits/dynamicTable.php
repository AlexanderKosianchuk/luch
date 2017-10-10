<?php

namespace EntityTraits;

trait dynamicTable {
    public static function getTable($link, $base)
    {
        if (!is_string($base)) {
            throw new Exception("Incorrect base passed. String is required. Passed: "
                . json_encode($code), 1);
        }

        $dynamicTableName = $base . self::$_prefix;
        $query = "SHOW TABLES LIKE '".$dynamicTableName."';";
        $result = $link->query($query);
        if (!$result->fetch_array()) {
            return null;
        }

        return $dynamicTableName;
    }
}
