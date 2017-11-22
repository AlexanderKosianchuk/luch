<?php

namespace EntityTraits;

trait dynamicTable {
    public static function getTable($link, $base)
    {
        if (!is_string($base)) {
            throw new Exception("Incorrect base passed. String is required. Passed: "
                . json_encode($base), 1);
        }

        $query = "SHOW TABLES LIKE '". $base . self::$_prefix."';";
        $result = $link->query($query);

        if (!$result->fetch_array()) {
            return null;
        }

        return $base . self::$_prefix;
    }
}
