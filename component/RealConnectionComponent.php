<?php

namespace Component;

use Exception;

class RealConnectionFactory
{
    public static function create($dbName = null)
    {
        global $CONFIG;
        $dbConfig = $CONFIG['db'];

        if ($dbName === null) {
            $dbName = $dbConfig['dbName'];
        }

        $link = mysqli_init();
        mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);
        mysqli_real_connect($link,
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['pass'],
            $dbConfig['dbName']
        );

        $link->select_db($dbName);
        $link->set_charset("utf8");

        if (mysqli_connect_errno()) {
            throw new Exception("Mysqli connection error " . mysqli_connect_error(), 1);
        }

        return $link;
    }

    public static function destroy($link)
    {
        if (method_exists ($link, 'close')) {
            $link->close();
        }
    }

}
