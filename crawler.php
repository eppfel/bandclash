<?php
require_once("debugger.php");
require_once("dbhelper.php");

final class Crawler extends DBHelper
{
	private $_responseString;
	public $_queries;

	function __construct()
	{
		//nothing
		$this->_queries = array();
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
							echo "sameAs URI duplicate: " . $row['o'];
						}
					}
				}
			}
		}
		return $fringe;
	}

	public function fetchAll($uri)
	{
		/**
		 * Safe/simple query
		 * !Activate completion of triples with URI before return statement
		 */
		$q = 'SELECT ?p ?o WHERE { <'. $uri .'> ?p ?o . }';

		/* Query with construct statement/
		if (parse_url($uri, PHP_URL_HOST) == 'dbpedia.org') {
			$q = 'CONSTRUCT { <'. $uri .'> ?p ?o . ?s2 ?p2 <'. $uri .'> } WHERE { {<'. $uri .'> ?p ?o } UNION { ?s2 ?p2 <'. $uri .'> . } }';
		}

		/* construct query inclduing songs of the band
		if (FALSE && parse_url($uri, PHP_URL_HOST) == 'dbpedia.org') {
	
		$q = 'CONSTRUCT {
			<'. $uri .'> ?p ?o . 
			?s ?p2 <'. $uri .'> . 
			?song ?psong ?osong .
    		?ssong ?p2song ?song . }
			WHERE { 
				{ <'. $uri .'> ?p ?o . 
				?s ?p2 <'. $uri .'> }
				UNION {
					?song <http://dbpedia.org/ontology/artist> <'. $uri .'> .
					?song ?psong ?osong .
        			?ssong ?p2song ?song .
				}
			}';	
		}*/

		$store = $this->_getStore($uri);

		if (!is_null($store))
		{
			array_push($this->_queries, $q);

			if ($rows = $store->query($q, 'rows'))
			{
				if (FALSE && parse_url($uri, PHP_URL_HOST) == 'dbpedia.org') {
					$rowsn = array();
					foreach ($rows as $row) {
						$row['s'] = $uri;
						$row['s type'] = "uri";
						array_push($rowsn, $row);
					}
				}
				else
				{
					$rowsn = $rows;
				}
				
				return $rowsn;	
			}
			else if ($errs = $store->getErrors()) {
				var_dump($errs);
			}
			else
			{
				//echo "No errors, but query return empty response at" . $uri . " : " . $store->getErrors() . "/n" ;
			}
		}
		else
		{
			//echo "Store at " . $uri . " not available!/n";
		}
		return;
	}

	public function getUnhandledURIs()
	{
		return var_export($this->_unhandledURIs, true);
	}
}

?>