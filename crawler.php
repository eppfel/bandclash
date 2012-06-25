<?php
require_once("debugger.php");
require_once("dbhelper.php");

final class Crawler extends DBHelper
{
	private $_unhandledURIs;
	private $_responseString;

	function __construct()
	{
		$this->_unhandledURIs = array();
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
	
			//insert everthing into db
			if (count($triples) > 1)
			{
				$bc_store = $this->_getLocalStore('arc_bc');
				$bc_store->reset();
				$bc_store->insert($triples, 'bandclash.example.com');
			}
		}
		return $triples; 
	}

	private function _getStore($uri) {
		//Notice
		if (FALSE)
		{
			Throw new Error("dump: " . $uri . ": " . $domain . "\n");
		}
		
		//switch method by host
		$domain = parse_url($uri, PHP_URL_HOST);
		switch($domain)
		{
			case 'data.nytimes.com':
				$store = $this->_getLocalStore('arc_nyt');
				$store->reset();
				
				//$uri = substr_replace($uri,'.',strripos($uri,'/'),1);
				echo "LOAD: " . $uri . "<br />\n";
				$store->query("LOAD <" . $uri . ">");
				
				return $store;
			break;
			case 'rdf.freebase.com':
				$store = $this->_getLocalStore('arc_fb');
				$store->reset();
				
				//$uri = substr_replace($uri,'.',strripos($uri,'/'),1);
				//echo "LOAD: " . $uri . "<br />\n";
				$store->query("LOAD <" . $uri . ">");
				
				return $store;
			break;
			case 'www.bbc.co.uk':
				$store = $this->_getLocalStore('arc_bbc');
				$store->reset();

				$uri = substr($uri,0,strcspn($uri,'#')) . ".rdf";
				//echo "LOAD: " . $uri . ".rdf<br />\n";
				$store->query("LOAD <" . $uri . ">");
				
				return $store;
			break;
			
			case "dbpedia.org":
				//echo "LOAD: http://dbpedia.org/sparql<br />\n";
				$store = $this->_getRemoteStore('http://dbpedia.org/sparql');
				
				return $store;
			break;
			
			case "dbtune.org":
				//echo "LOAD: http://dbtune.org/musicbrainz/sparql<br />\n";
				$store = $this->_getRemoteStore('http://dbtune.org/musicbrainz/sparql');
				
				return $store;
			break;
			
			case "myspace.com":
			default:
				//no endpoint
				$this->_unhandledURIs[]=$domain;
				return null;
			break;
		}
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

	}
}

?>