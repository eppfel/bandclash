<?php
//Force UTF-8 for international triples
header( 'Content-Type: text/html; charset=UTF-8' );
error_reporting(E_ALL);

require_once('utf8helper.php');
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
		//$this->_startpoint = "http://www.bbc.co.uk/music/artists/b10bbbfc-cf9e-42e0-be17-e2c3e1d2600d#artist";
		//$this->_startpoint = "http://rdf.freebase.com/ns/m.07c0j";
		$this->_startpoint = "http://dbpedia.org/resource/The_Beatles";

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

				case 'onload':
					$triples = $this->_fetchArtists();
					echo json_encode($triples);
				break;
				case 'crawl':
					if (isset($_REQUEST['uri'])) {
						$uri = $_REQUEST['uri'];
					}
					else {
						$uri = $this->_startpoint;
					}
					$this->_crawlByArtist($uri);
					break;

				//reset and show result
				case 'reset':
					//empty store
					$this->_store->reset();
					if ($errs = $this->_store->getErrors()) {
						var_dump($errs);
					}
					else {
						echo "<p>Store reseted with no errors!</p>";
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

				case 'import':
					//query store
					$this->_store->query("LOAD <http://bandclash/ontology/bandclash_inferred.owl>");
					if ($errs = $this->_store->getErrors()) {
						var_dump($errs);
					}
					else {
						echo "<p>No errors by importing!</p>";
					}

				// print all data in a table view
				//FIX: Move markup to client side
				default:
					$triples = $this->_fetchAll();
					break;
			}
		}
		else
		{
			header("Location: http://" . $_SERVER['HTTP_HOST'] );
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
		else if ($n = count($triples))
		{
			$r = '<p>The db contains ' . $n . ' Triples.</p>' . PHP_EOL;
			$r .= '<table class="table table-striped table-condensed tfixed">' . PHP_EOL;
			$r .= '<thead><tr><th>s</th><th>p</th><th>o</th></tr></thead>' . PHP_EOL;
			$r .= '<tbody>' . PHP_EOL;
			foreach ($triples as $row) {
				$r .= '<tr>';
				$r .= '<td>' . $row['s'] . '</td>';
				$r .= '<td>' . $row['p'] . '</td>';
				$r .= '<td>' . fixUtf8($row['o']) . '</td>';
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
		//return $triples;
	}

	/*
	* DB request for basic data, esp. all artists
	*/
	private function _fetchArtists()
	{
		$triples = $this->_store->query('SELECT ?uri ?name WHERE {?uri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://dbpedia.org/ontology/Band>. ?uri <http://xmlns.com/foaf/0.1/name> ?name }' ,'rows');
		if ($errs = $this->_store->getErrors()) {
			var_dump($errs);
		}
		return $triples;
	}

	private function _crawlByArtist($uri)
	{
		//use crawler to aggregate data
		require_once('crawler.php');
		$crawler = new Crawler();
		$triples = $crawler->crawl($uri);

		/*/insert everthing into db
		$n = count($triples);
		if ($n) {
			//$this->_store->reset(); //just if every crawl should start by 0

			$this->_store->insert($triples, 'http://bandclash.net/ontology');

			if ($errs = $this->_store->getErrors())
			{
				echo "Problems in Insert of aggregated triples: " . var_export($errs, true) . PHP_EOL;
				var_dump($triples);
			}
			else
			{
				echo "Succesfully crawled " . $n . " data triples from &lt;" . $uri . "&gt;." . PHP_EOL;
				echo "These Sources were not crawled: " . $crawler->getUnhandledURIs() . PHP_EOL;

				//$this->_parseChartsByArtist($uri);
			}
		}
		else {
			echo "Sadly nothing got crawled from &lt;" . $uri . "&gt;." . PHP_EOL;
		}
		*/
		echo "<p>Show all data, result status unknown so far</p>";

		$this->_fetchAll();
	}

	/**
	 * parse data from chartarchive.org
	 */
	private function _parseChartsByArtist($uri)
	{
		require_once('parser.php');
		$bcp = new BCParser("http://chartarchive.org", "/a/");
		$triples = $bcp->getChartsByArtist($uri);

		$this->_store->insert($triples, 'http://bandclash.net/ontology');
		if ($errs = $this->_store->getErrors())
		{
			echo "Problems in Insert of parsed triples: " . var_export($errs, true) . PHP_EOL;
		}
		else
		{
			echo "Succesfully parsed " . count($triples) . " data triples from &lt;" . $uri . "&gt;." . PHP_EOL;
		}
	}			
}

//init server and run handler
$server = new BCAjaxServer();
$server->handleRequest();
?>