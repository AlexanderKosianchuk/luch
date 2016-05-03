<?php

require_once(@SITE_ROOT_DIR ."/includes.php"); 


class SearchFlights
{	
	public function SearchFlightsTables()
	{			
		$query = "SHOW TABLES LIKE 'search_flights_queries';";
		$c = new DataBaseConnector();
		$link = $c->Connect();
		$result = $link->query($query);
		if(!$result->fetch_array())
		{
			$query = "CREATE TABLE `search_flights_queries` (
				`id` BIGINT NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(200),
				`fdr` INT,
				`alg` TEXT,
				`authorId` INT,
				PRIMARY KEY (`id`));";
			$stmt = $link->prepare($query);
			if (!$stmt->execute()) 
			{
				echo('Error during query execution ' . $query);
				error_log('Error during query execution ' . $query);
			}
		}
				
		$c->Disconnect();
		unset($c);
	}
	
	public function GetSearchAlgorithmes($fdrId)
	{
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT * FROM `search_flights_queries` WHERE `fdr`=".$fdrId.";");
		
		$searchFlightsQueries = array();
		while($row = $result->fetch_array())
		{
			$item = [];
			foreach ($row as $key => $val)
			{
				$item[$key] = $val;
			}	
			$searchFlightsQueries[] = $item;
		}
	
		$c->Disconnect();
		unset($c);
	
		return $searchFlightsQueries;
	}
	
	public function GetSearchAlgorithById($id)
	{
		$c = new DataBaseConnector();
		$link = $c->Connect();
	
		$result = $link->query("SELECT * FROM `search_flights_queries` WHERE `id`=".$id.";");
	
		$alg = null;
		if($row = $result->fetch_array())
		{
			foreach($row as $key => $val) {
				$alg[$key] = $val;
			}
		}
	
		$c->Disconnect();
		unset($c);
	
		return $alg;
	}
}
