<?php

require_once("arc2/ARC2.php");

$config = array(
	/* remote endpoint */
	'remote_store_endpoint' => 'http://dbtune.org/musicbrainz/sparql',
);

$store = ARC2::getRemoteStore($config);

/* list triples */
$q = '
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX tune: <http://dbtune.org/musicbrainz/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX dbta: <http://dbtune.org/musicbrainz/resource/artist/>
SELECT ?o 
WHERE { dbta:b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d owl:sameAs ?o }
';

/* list triples /
$q = '
SELECT ?s ?p ?o 
WHERE { ?s ?p "Strawberry Fields" }
';// */

$r = '';
if ($rows = $store->query($q, 'rows')) {
	$r = '<table border="1" rules="all">';
	foreach ($rows as $row) {
		$r .= '<tr>';
		//$r .= '<td>' . $row['s'] . '</td>';
		//$r .= '<td>' . $row['p'] . '</td>';
		$r .= '<td>' . $row['o'] . '</td>';
		$r .= '</tr>';
	}
	$r .= '</table>';
}

echo $r ? $r : 'no objects found';
?>