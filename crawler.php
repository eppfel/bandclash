<?php
require_once("debugger.php");
require_once("dbhelper.php");

final class Crawler extends DBHelper
{
	private $_responseString;

	function __construct()
	{
		//nothing
	}

	public function crawl($uri)
	{
		//SameAsLinks
		$fringe = array();
		$fringe = $this->fetchSameAs($uri);

		//Aggregation (temporary request All where URI is subject)
		if (count($fringe) > 1)
		{
			$triples = array();
			foreach ($fringe as $uri)
			{
				$storeTriples = $this->fetchAll($uri);
				if (is_array($storeTriples)) {
					$triples = array_merge($triples, $storeTriples);	
				}
			}
		}
		return $triples; 
	}

	public function fetchSameAs($uri, $depth = 3, $fringe = array())
	{	
		array_push($fringe, $uri);

		if ($depth > 0)
		{
			//echo "URI: " . $uri . " , Depth: " . $depth . PHP_EOL;
			/* Query */
			$q = '
			PREFIX owl: <http://www.w3.org/2002/07/owl#>
			SELECT ?o
			WHERE { <'.$uri.'> owl:sameAs ?o . }';
		
			$store = $this->_getStore($uri);
		
			if (!is_null($store)) {
				if ($rows = $store->query($q, 'rows')) {
					foreach ($rows as $row) {
						if (!in_array($row['o'], $fringe)) {
							$fringe = $this->fetchSameAs($row['o'], $depth-1, $fringe);
						}
						else
						{
							echo "sameAs doubled";
						}
					}
				}
			}
		}
		return $fringe;
	}

	public function fetchAll($uri)
	{
		/* Query */
		$q = 'SELECT ?p ?o WHERE { <'. $uri .'> ?p ?o . }';

		$store = $this->_getStore($uri);

		if (!is_null($store))
		{
			if ($rows = $store->query($q, 'rows'))
			{
				$rowsn = array();
				foreach ($rows as $row) {
					$row['s'] = $uri;
					$row['s type'] = "uri";
					array_push($rowsn, $row);
				}
				return $rowsn;	
			}
			else if ($errs = $store->getErrors()) {
				var_dump($errs);
			}
		}
		return;
	}

	public function getUnhandledURIs()
	{
		return var_export($this->_unhandledURIs, true);
	}
}

?>