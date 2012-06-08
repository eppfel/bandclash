<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>fb_TheBeatles.php</title>
</head>
<body>
<h1>The Beatles (FB)</h1>
<?php

include_once("arc2/ARC2.php");

$config = array(
				/* db */
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
$store->query('LOAD <http://rdf.freebase.com/ns/m.07c0j>');

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