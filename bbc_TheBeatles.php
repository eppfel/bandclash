<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>bbc_TheBeatles.php</title>
</head>
<body>
<h1>The Beatles (BBC)</h1>
<?php

include_once("arc2/ARC2.php");

$config = array(
				/* db */
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
$store->query('LOAD <http://www.bbc.co.uk/music/artists/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d.rdf>');

/* list triples */
$q = '
SELECT ?s ?p ?o
WHERE { ?s ?p ?o . }
';

$r = '';
if ($rows = $store->query($q, 'rows')) {
	$r = '<table border="1" rules="all">';
	foreach ($rows as $row) {
		$r .= '<tr>';
		$r .= '<td>' . $row['s'] . '</td>';
		$r .= '<td>' . $row['p'] . '</td>';
		$r .= '<td>' . $row['o'] . '</td>';
		$r .= '</tr>';
	}
	$r .= '</table>';
}

echo $r ? $r : 'no objects found';
?>
</body>
</html>