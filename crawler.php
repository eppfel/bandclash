<?php

require_once("arc2/ARC2.php");
//$startpoint = "http://www.bbc.co.uk/music/artists/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d#artist";
$startpoint = "http://rdf.freebase.com/ns/m.07c0j";
//$startpoint = "http://dbpedia.org/resource/The_Beatles";
$fringe = array( );

function fetchSameAs($uri)
{
	/* Pre- and Postfix */
	$q_pre = '
	PREFIX owl: <http://www.w3.org/2002/07/owl#>
	SELECT ?o 
	WHERE { ';
	
	$q_post = ' }
	';

	$q = $q_pre . "<" . $uri . "> owl:sameAs ?o" . $q_post;

	$store = getStore($uri);

	if (!is_null($store)) {
		$rows = $store->query($q, 'rows');
		return $rows;	
	}
	return;
}


function fetchAll($uri, $bc_store)
{
	/* Pre- and Postfix */
	$q_pre = '
	SELECT ?s ?p ?o  
	WHERE { ';
	
	$q_post = ' }
	';

	$q = $q_pre . " ?s ?p ?o . <" . $uri . "> ?p ?o" . $q_post;

	$store = getStore($uri);

	if (!is_null($store)) {
		$rows = $store->query($q, 'rows');
		//$rawdata = $store->query(, 'raw');
		$bc_store->insert($rows, 'bandclash.example.com');
		return $rows;	
	}
	return;
}

function getStore($uri) {


	//dump
	if (FALSE)
	{
		echo "dump: " . $uri . ": " . $domain . "\n";
	}
	
	$domain = parse_url($uri, PHP_URL_HOST)	;
	//echo "domain: " . $domain . "\n";
	
	$unhandledUris = array();

	switch($domain)
	{
		case 'rdf.freebase.com':
			$config = array(
					/* db */
					'db_host' => 'localhost',
					'db_name' => 'mi8',
					'db_user' => 'root',
					'db_pwd' => '',
					/* store */
					'store_name' => 'arc_fb',
					/* stop after 100 errors */
					'max_errors' => 100
					);
			$store = ARC2::getStore($config);
			if (!$store->isSetUp()) $store->setUp();
			
			/* Reset the store */
			$store->reset();
			
			/* LOAD will call the Web reader, which will call the
			 format detector, which in turn triggers the inclusion of an
			 appropriate parser, etc. until the triples end up in the store. */
			//$uri = substr_replace($uri,'.',strripos($uri,'/'),1);
			echo "LOAD: " . $uri . "<br />\n";
			$store->query("LOAD <" . $uri . ">");
			
			return $store;
		break;
		case 'www.bbc.co.uk':
			$config = array(
					/* db */
					'db_host' => '127.0.0.1',
					'db_name' => 'mi8',
					'db_user' => 'root',
					'db_pwd' => '',
					/* store */
					'store_name' => 'arc_bbc',
					/* stop after 100 errors */
					'max_errors' => 100
					);
			$store = ARC2::getStore($config);
			if (!$store->isSetUp()) $store->setUp();
			
			/* Reset the store */
			$store->reset();
			
			/* LOAD will call the Web reader, which will call the
			 format detector, which in turn triggers the inclusion of an
			 appropriate parser, etc. until the triples end up in the store. */

			echo "LOAD: " . substr($uri,0,strcspn($uri,'#')) . ".rdf<br />\n";
			$store->query("LOAD <" . substr($uri,0,strcspn($uri,'#')) . ".rdf>");
			
			return $store;
		break;
		
		case "dbpedia.org":
			echo "LOAD: http://dbpedia.org/sparql<br />\n";
			$config = array('remote_store_endpoint' => 'http://dbpedia.org/sparql');
			$store = ARC2::getRemoteStore($config);
			
			return $store;
		break;
		
		case "dbtune.org":
			echo "LOAD: http://dbtune.org/musicbrainz/sparql<br />\n";
			$config = array('remote_store_endpoint' => 'http://dbtune.org/musicbrainz/sparql');
			$store = ARC2::getRemoteStore($config);
			
			return $store;
		break;
		
		case "myspace.com":
		default:
			//no endpoint
			$unhandledUris[]=$domain;
			return null;
		break;
	}
}
	
function requestURIs($uri, $fringe, $i) {

	//echo "dump in Request: " . $uri . ", " . $i . "\n";
	array_push($fringe, $uri);
	if ($rows = fetchSameAs($uri)) {
		foreach ($rows as $row) {
			if (!in_array($row['o'], $fringe)) {
				if ($i < 4)
				{
					$fringe = requestURIs($row['o'], $fringe, $i+1);
				}
			}
		}
	}
	return $fringe;
}



//loop
$fringe = requestURIs($startpoint, $fringe, 0);
//var_dump($fringe);

$bc_config = array(
					/* db */
					'db_host' => 'localhost',
					'db_name' => 'mi8',
					'db_user' => 'root',
					'db_pwd' => '',
					/* store */
					'store_name' => 'arc_bc',
					/* stop after 100 errors */
					'max_errors' => 100
					);
$bc_store = ARC2::getStore($bc_config);
if (!$bc_store->isSetUp()) $bc_store->setUp();
			
/* Reset the store */
$bc_store->reset();

$triples = array();
foreach ($fringe as $uri) {
	//fetchAll($uri,$bc_store);

	$storeTriples = fetchAll($uri,$bc_store);
	if (is_array($storeTriples)) {
		$triples = array_merge($triples, $storeTriples);	
	}
}
//$triples = $bc_store->query('SELECT *', 'rows');

var_dump($triples);


if (FALSE) {//$fringe) {
	$r = '<table border="1" rules="all">';
	foreach ($triples as $row) {
		$r .= '<tr>';
		//$r .= '<td>' . $row['s'] . '</td>';
		$r .= '<td>' . $row['p'] . '</td>';
		$r .= '<td>' . $row['o'] . '</td>';
		$r .= '</tr>';
	}
	$r .= '</table>';
}

?>