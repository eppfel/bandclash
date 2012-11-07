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
				case 'updateBand':
					if (isset($_REQUEST['uri'])) {
						$uri = $_REQUEST['uri'];
					}
					else {
						$uri = $this->_startpoint;
					}
					$triples = $this->_fetchBandDetails($uri);
					echo json_encode($triples);
					break;
				case 'clash':
					if(isset($_REQUEST['uri1'])&&isset($_REQUEST['uri2']))
					{
						$uri1 = $_REQUEST['uri1'];
						$uri2 = $_REQUEST['uri2'];
						$triples = $this->_clash($uri1, $uri2);	
						echo json_encode($triples);
					}
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

				case 'crawl':
					if (isset($_REQUEST['uri'])) {
						$uri = $_REQUEST['uri'];
					}
					else {
						$uri = $this->_startpoint;
					}
					$this->_crawlByArtist($uri);

					echo "<p>Show all data, result status unknown so far</p>";

				// print all data in a table view
				//FIX: Move markup to client side
				default:
					$triples = $this->_fetchAll();
					if ($n = count($triples))
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
		else {
			return $triples;
		}
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


	private function _fetchBandDetails($uri)
	{
		$result = $this->_store->query("SELECT ?comment ?name ?depiction ?formed WHERE {<$uri> <http://www.w3.org/2000/01/rdf-schema#comment> ?comment . <$uri> <http://xmlns.com/foaf/0.1/name> ?name. <$uri> <http://dbpedia.org/ontology/thumbnail> ?depiction . FILTER (langMATCHES (LANG(?comment),'en')) OPTIONAL { <$uri>  <http://purl.org/vocab/bio/0.1/date>  ?formed }}" ,'rows');
		if ($result) $details = $result[0];
		else return 'query failed : ' . $this->_store->getErrors();
		$members = $this->_store->query('SELECT ?member WHERE {<'.$uri.'> <http://dbpedia.org/ontology/bandMember> ?member }' ,'rows');
		//$members = $this->_store->query('SELECT ?member WHERE {<'.$uri.'> <http://dbpedia.org/ontology/bandMember> ?m . ?m  <http://xmlns.com/foaf/0.1/name> ?member}' ,'rows');
		$details['members'] = array();
		foreach ($members as $member) {
			$details['members'][] = $member['member'];
		};
		if ($errs = $this->_store->getErrors()) {
			return $errs;
		}
		return $details;
	}

	private function _crawlByArtist($uri)
	{
		//use crawler to aggregate data
		require_once('crawler.php');
		$crawler = new Crawler();
		$triples = $crawler->crawl($uri);

		$this->_parseChartsByArtist($uri);

	}

	/**
	 * parse data from chartarchive.org
	 * FIX: get inferences from local store
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
	
	private function _clash($uri1, $uri2)
	{
		$query = 'SELECT ?comment ?name ?depiction ?yearsActive ?bandMembers WHERE {<'.$uri1.'> <http://www.w3.org/2000/01/rdf-schema#comment> ?comment. <'.$uri1.'> <http://xmlns.com/foaf/0.1/name> ?name. <'.$uri1.'> <http://dbpedia.org/ontology/thumbnail> ?depiction. <'.$uri1.'> <http://dbpedia.org/property/yearsActive> ?yearsActive. <'.$uri1.'> <http://dbpedia.org/ontology/bandMembers> ?bandMembers FILTER (langMATCHES (LANG(?comment),"en"))}';
		echo $query;
		$triples = $this->_store->query( $query,'rows');

	}
}

//init server and run handler
$server = new BCAjaxServer();
$server->handleRequest();
?>