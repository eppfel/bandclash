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
		/*/testing
		$this->fetchAll($uri);
		return;//*/

		//SameAsLinks
		$fringe = array();
		$fringe = $this->fetchSameAs($uri);

		//Aggregation
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
			return $triples;
		}
		return;
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
		
		if ($store = $this->_getStore($uri)) {
			
			/* construct query inclduing songs of the band */
			if (parse_url($uri, PHP_URL_HOST) != 'dbpedia.org')
			{
				/**
				 * Safe/simple query
				 * !Activate completion of triples with URI before return statement
				 *
				$q = 'SELECT ?p ?o WHERE { <'. $uri .'> ?p ?o . }';

				$this->_queries[] = $q;

				$rows = $store->query($q, 'rows');

				if ($errs = $store->getErrors()) {
					var_dump($errs);
				}
				else if ($rows)
				{
					 //TO FIX
					$rowsn = array();
					foreach ($rows as $row) {
						$row['s'] = $uri;
						$row['s type'] = "uri";
						array_push($rowsn, $row);
					
					}
					
					return $rowsn;	
				}
				else 
				{
					echo "No errors, but query returned empty response at" . $uri . " : " . $store->getErrors() . "/n" ;
				}
				*/
			}
			else //dbpedia
			{
				/*/complex sparql request
				$q = '
					PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
					PREFIX foaf: <http://xmlns.com/foaf/0.1/>
					CONSTRUCT {
						<'. $uri .'> ?p ?o . 
						?s ?p2 <'. $uri .'> . 
						?r <http://dbpedia.org/ontology/artist> <'. $uri .'> .
						?r rdf:type ?rtype .
						?r foaf:name ?rname .
					}
					WHERE { 
						{
							<'. $uri .'> ?p ?o .
						}
						UNION {
							?s ?p2 <'. $uri .'> .
						}
						UNION {
							?r <http://dbpedia.org/ontology/artist> <'. $uri .'> .
							?r rdf:type ?rtype .
							?r foaf:name ?rname .
						}
					}';	

				//* Query with construct statement without songs*/
				$q = 'CONSTRUCT { <'. $uri .'> ?p ?o . ?s2 ?p2 <'. $uri .'> } WHERE { {<'. $uri .'> ?p ?o } UNION { ?s2 ?p2 <'. $uri .'> . } }';

				$this->_queries[] = $q;

				$index = $store->query($q);

				if ($errs = $store->getErrors()) {
					var_dump($errs);
				}
				else if (isset($index['result']) && $index['result'])
				{
					 //TO FIX

					//$ser = ARC2::getNTriplesSerializer();
					//$triples = $ser->getSerializedIndex($index['result']);

					$triples = ARC2::getTriplesFromIndex($index['result']);
					$result = $this->_getDefaultLocalStore()->insert($triples, "http://bandclash.net/ontology");

					//var_dump($index);
					//var_dump($triples);
					//var_dump($result);

					if ($errs = $this->_getDefaultLocalStore()->getErrors()) {
						var_dump($errs);
					}
					return $result;//$triples;	
				}
				else 
				{
					echo "No errors, but query returned empty response at" . $uri . " : " . $store->getErrors() . "/n" ;
				}
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