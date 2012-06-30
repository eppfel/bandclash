<?php
require_once('crawler.php');
require_once('dbhelper.php');

/**
* This class provides the basic data functionalities to application
*/
class BCAjaxServer extends DBHelper
{
	private $_startpoint;
	private $_store;
	
	function __construct()
	{
		$this->_startpoint = "http://www.bbc.co.uk/music/artists/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d#artist";
		//$this->_startpoint = "http://rdf.freebase.com/ns/m.07c0j";
		//$this->_startpoint = "http://dbpedia.org/resource/The_Beatles";

		//setup store
		$this->_store = $this->_getLocalStore('arc_bc');
	}

	/**
	* !Starting point of every action
	* Check, which action to run and therefore which data to respond
	*/
	public function handleRequest()
	{
		if(isset($_REQUEST['action']))
		{
			switch ($_REQUEST['action']) {

				case 'crawl':
					if (isset($_REQUEST['uri'])) {
						$uri = $_REQUEST['uri'];
					}
					else {
						$uri = $this->_startpoint;
					}
					$crawler = new Crawler();
					$triples = $crawler->crawl($uri);

					//insert everthing into db
					$n = count($triples);
					if ($n) {
						//$this->_store->reset(); //just if every crawl should start by 0
						$this->_store->insert($triples, 'bandclash.net');
						if ($errs = $this->_store->getErrors()) {
							echo "Problems in Insert of aggregated triples: " . var_export($errs, true) . PHP_EOL;
							var_dump($triples);
						}
						else {
							echo "Succesfully crawled " . $n . " data triples from &lt;" . $uri . "&gt;." . PHP_EOL;
							echo "These Sources were not crawled: " . $crawler->getUnhandledURIs . PHP_EOL;
						}
					}
					else {
						echo "Sadly nothing got crawled from &lt;" . $uri . "&gt;." . PHP_EOL;
					}
					break;
				
				case 'export':
					//query store
					$doc = $this->_store->toRDFXML($this->_fetchAll());
					if ($errs = $this->_store->getErrors()) {
						var_dump($errs);
					}
					else {
						header('Content-type: rdf/xml');
						header('Content-Disposition: attachment; filename="BandClashTriples-'.date("ymd-His").'.rdf"');
						echo $doc;	
					}
					break;
				
				//reset and show result
				case 'reset':
					//empty store
					$this->_store->reset();

				// print all data in a table view
				default:
					$triples = $this->_fetchAll();
					$n = count($triples);
					if ($n) {
						$r = '<p>The db contains ' . $n . ' Triples.</p>' . PHP_EOL;
						$r .= '<table class="table table-striped table-condensed">' . PHP_EOL;
						$r .= '<thead><tr><th>s</th><<th>p</th><th>o</th></tr></thead>' . PHP_EOL;
						$r .= '<tbody>' . PHP_EOL;
						foreach ($triples as $row) {
							$r .= '<tr>';
							$r .= '<td>' . $row['s'] . '</td>';
							$r .= '<td>' . $row['p'] . '</td>';
							$r .= '<td>' . $row['o'] . '</td>';
							$r .= '</tr>' . PHP_EOL;
						}
						$r .= '</tbody>' . PHP_EOL;
						$r .= '</table>';
						echo $r;
					}
					else
					{
						echo "Local store is empty!" . PHP_EOL;
					}
					break;
			}
		}
		else
		{
			header('Warning: "This location is not for direct access. Please go back to <a href=./index.html>Index</a>"');
		}

		if ($errs = $this->_store->getErrors()) {
			//var_dump($errs);
		}
	}

	/*
	* Basic DB request to fetch all Triples
	*/
	private function _fetchAll()
	{
		$triples = $this->_store->query('SELECT ?s ?p ?o WHERE {?s ?p ?o}', 'rows');
		if ($errs = $this->_store->getErrors()) {
			var_dump($errs);
		}
		return $triples;
	}
}

//init server and run handler
$server = new BCAjaxServer();
$server->handleRequest();
?>