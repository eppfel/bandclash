<?php
require_once('arc2/ARC2.php');

/**
* Abstract class to provide ARC features in local DB for all subclasses
* only one place to withhold db configurations
*/
abstract class DBHelper
{
	private $_unhandledURIs;
	
	function __construct()
	{
		$this->_unhandledURIs = array();
		//nothing
	}

	//Local Store for Content Negotiation
	protected function _getLocalStore($name)
	{
		$config = array(
			/* db */
			'db_host' => 'localhost',
			'db_name' => 'mi8',
			'db_user' => 'root',
			'db_pwd' => '',
			/* store */
			'store_name' => $name,
			/* stop after 100 errors */
			'max_errors' => 100
		);
		$store = ARC2::getStore($config);
		if (!$store->isSetUp()) $store->setUp();
		return $store;
	}

	/**
	* Setup remote SPARQL Endpoint
	* !Could be obsolete, because oneliner
	*/
	protected function _getRemoteStore($url)
	{
		$store = ARC2::getRemoteStore(array('remote_store_endpoint' => $url));
		return $store;
	}


	/**
	* Setup a Store to query on, from a the uri you query
	* @param: $uri 
	* @return: ARC2 Store Object
	*/
	protected function _getStore($uri)
	{
		//switch method by host
		$domain = parse_url($uri, PHP_URL_HOST);

		switch($domain)
		{
			case 'data.nytimes.com':
				$store = $this->_getLocalStore('arc_nyt');
				$store->reset();
				
				//$uri = substr_replace($uri,'.',strripos($uri,'/'),1);
				//echo "LOAD: " . $uri . "<br />\n";
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
}