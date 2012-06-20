<?php


require_once("arc2/ARC2.php");

//setup store
$bc_config = array(
					/* db */
					'db_host' => 'localhost',
					'db_name' => 'mi8',
					'db_user' => 'root',
					'db_pwd' => '',
					/* store */
					'store_name' => 'arc_bc',
					/* stop after 100 errors */
					'max_errors' => 30
					);
$bc_store = ARC2::getStore($bc_config);
if (!$bc_store->isSetUp()) $bc_store->setUp();

//query store
//$triples = array();
$triples = $bc_store->query('SELECT ?s ?p ?o WHERE {?s ?p ?o}', 'rows');
$doc = $bc_store->toNTriples($triples);

if ($errs = $bc_store->getErrors()) {
  var_dump($errs);
}
else {
	header('Content-type: rdf/xml');
	header('Content-Disposition: attachment; filename="triples.rdf"');
	echo $doc;
}

//var_dump($triples);

?>