<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Airport
{
    public function getAirportByLatAndLong($extLat, $extLong)
    {
        $lat = $extLat;
        $long = $extLong;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `ICAO`,`name` FROM `airports` where
            `runwayStartLat` > ({$lat} - 0.02) and
            `runwayStartLat` < ({$lat} + 0.02) and
            `runwayStartLong` > ({$long} - 0.02) and
            `runwayStartLong` < ({$long} + 0.02) LIMIT 1;";
        $result = $link->query($query);

        $airport = array();
        if($row = $result->fetch_array())
        {
            $airport = array(
                'ICAO' => $row['ICAO'],
                'name' => $row['name']
            );
        }

        $result->free();
        $c->Disconnect();

        unset($c);
        return $airport;
    }
}
