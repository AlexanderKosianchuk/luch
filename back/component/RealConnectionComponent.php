<?php

namespace Component;

use Exception;

class RealConnectionFactory
{
    public static function create($db = "default")
    {
        global $CONFIG;
        $dbConfig = $CONFIG["db"]["default"];

        if (isset($CONFIG["db"][$db])) {
            $dbConfig = $CONFIG["db"][$db];
        }

        $link = mysqli_init();
        mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);
        mysqli_real_connect($link,
            $dbConfig["host"],
            $dbConfig["user"],
            $dbConfig["password"],
            $dbConfig["dbname"]
        );

        $link->select_db($dbConfig["dbname"]);
        $link->set_charset($dbConfig["charset"]);

        if (mysqli_connect_errno()) {
            throw new Exception("Mysqli connection error " . mysqli_connect_error(), 1);
        }

        return $link;
    }

    public static function destroy($link)
    {
        if (method_exists ($link, "close")) {
            $link->close();
        }
    }

}
