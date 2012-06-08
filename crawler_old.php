<?php

require_once("arc2/ARC2.php");

$config = array(
	/* remote endpoint */
	'remote_store_endpoint' => 'http://dbtune.org/musicbrainz/sparql',
);

$store = ARC2::getRemoteStore($config);
$startpoint = "http://dbtune.org/musicbrainz/resource/artist/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d";
$fringe = array();

function requestURIs($uri, $store, $fringe, $i) {

	/* list triples */
	$q = "
	PREFIX owl: <http://www.w3.org/2002/07/owl#>
	SELECT ?o 
	WHERE { <$uri> owl:sameAs ?o }
	";

	if ($rows = $store->query($q, 'rows')) {
		foreach ($rows as $row) {
			if (!in_array($row['o'], $fringe)) {
				array_push($fringe, $row['o']);
				if ($i < 3)
				{
					$fringe = requestURIs($row['o'], $store, $fringe, $i+1);
				}
			}
		}
	}
	echo "dump: " . $uri . ", " . $i . "\n";
	return $fringe;
}



//loop
$fringe = requestURIs($startpoint, $store, $fringe, 0);

if ($fringe) {
	$r = '<table border="1" rules="all">';
	foreach ($fringe as $row) {
		$r .= '<tr>';
		//$r .= '<td>' . $row['s'] . '</td>';
		//$r .= '<td>' . $row['p'] . '</td>';
		$r .= '<td>' . $row . '</td>';
		$r .= '</tr>';
	}
	$r .= '</table>';
}

echo $r ? $r : 'no objects found';
?>