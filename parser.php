<?php
require_once('dbhelper.php');
require_once('shdp/simple_html_dom.php');

/**
* Parser to extract Data from a plain HTML site
*/
class BCParser extends DBHelper
{
	private $_baseURI;
	private $_subPath;
	public $unresolvedReleases;
	
	public function __construct($baseURI, $subPath)
	{
		$this->_baseURI = $baseURI;
		$this->_subPath = $subPath;
		$this->unresolvedRelaeses = array();
	}

	/**
	 * Function to parse all hits by an artist from chartarchive.org
	 * Fix: parameter artsit uri
	 * Fix: correct releaseType URI from dbpedia
	 */
	public function getChartsByArtist($artistURI)
	{	
		//get name of artist in order to parse it
		$store = $this->_getStore($artistURI);

		if (!is_null($store))
		{
			$rows = $store->query("SELECT ?name WHERE { <$artistURI> <http://xmlns.com/foaf/0.1/name> ?name }", 'rows');
			if ($rows) {
				$artistName = $rows[0]['name'];
			}
			else
			{
				echo "result is is empty";
				return;
			}
		}
		else
		{
			echo "store is is null" .  $artistURI;
			return;
		}

		// parse a Site
		// Create DOM from URL or file
		$uri = $this->_baseURI . $this->_subPath . str_replace(" ", "+", $artistName);
		$html = file_get_html($uri);
		if ($html == FALSE) return;

		//init vars
		$triples = array();
		$releaseType = '';

		foreach($html->find('table') as $table) 
		{
			$rowC = 0;

			// Find all trs 
			foreach($table->find('tr') as $tablerow) 
			{

				//for each table or 
				if ($rowC == 0) {
					
					$tablehead = $tablerow->find('th' , 0);
					if($tablehead->plaintext == 'Singles')
					{
						$releaseType= 'http://dbpedia.org/ontology/Single'; //Replace by correct URI
					}
					else if($tablehead->plaintext == 'Albums')
					{
						$releaseType = 'http://dbpedia.org/ontology/Album'; //Replace by correct URI
					}
					else {
						//throw new Exception("Error Parsing wether albums or Singles", 1);
					}
					
				}
				else if ($rowC > 1) {

					# Temporary triple array
					$triples_temp = array();
				  	$tdC = 0;
				  	$releaseName = '';
					
					//scan cells for infos	!!!change id to push
				   	foreach($tablerow->find('td') as $element)
				   	{	
				   		$triple_temp = array();

						switch($tdC)
						{
							case 0:
								//Add img URI
								
								if (!$img = $element->find('img', 0)) break;
								$src = $img->src;

								//COVER LARGE
								$triple_temp["p"] = "http://xmlns.com/foaf/0.1/depiction";
								$triple_temp["p type"] = "uri";
								$triple_temp["o type"] = "uri";
								$triple_temp["o"] = $this->_baseURI . str_replace("-100", "-raw", $src);
								$triples_temp[] = $triple_temp;
								
								//COVER 300px
								$triple_temp["p"] = "http://xmlns.com/foaf/0.1/depiction";
								$triple_temp["p type"] = "uri";
								$triple_temp["o type"] = "uri";
								$triple_temp["o"] = $this->_baseURI.str_replace("-100", "-300", $src);
								$triples_temp[] = $triple_temp;
								
								//COVER THUMBNAIL
								$triple_temp["p"] = "http://xmlns.com/foaf/0.1/thumbnail";
								$triple_temp["p type"] = "uri";
								$triple_temp["o type"] = "uri";
								$triple_temp["o"] = $this->_baseURI . $src; //FIX: URI is not complete!
								break;
							case 1:
								$releaseName = $element->plaintext;
								break;	

							case 2:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/ontology#firstCharted";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 3:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/ontology#lastCharted";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 4:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/ontology#chartAppearances";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 5:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/ontology#chartPeak";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	
						}
						if(($triple_temp)) $triples_temp[] = $triple_temp;
						$tdC++;
				   	}

				   	/**
				   	 * set subject uri from title/name of release
				   	 * FIX: some releases are not identified and therefore are ignored
				   	 */
				   	$releaseURI = $this->fetchReleaseURI($releaseName, $artistURI, $releaseType);
				   	if ($releaseURI) {
				   		foreach ($triples_temp as $triple)
					   	{
							$triple['s'] 		= $releaseURI;
							$triple['s type'] 	= "uri";
							$triples[] = $triple;
					   	}
				   	}
				   	
				}

			   	$rowC++;
			}
		}
		
		//return results as triples !FIX: empty, because results are not transfered to var $triples
		return $triples;
	}
	
	/**
	 * Function to fetch a dbpedia URI to infer the parsed data with RDF data
	 * @return URI or FALSE
	 */
	private function fetchReleaseURI($releaseName, $artistURI, $releaseType)
	{
		/* Query */
		$q = "
		PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
		PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		SELECT ?rURI
		WHERE 	{
			  	?rURI <http://dbpedia.org/property/artist> <$artistURI> .
				?rURI rdf:type <$releaseType> .
				?rURI foaf:name '" . addslashes($releaseName) . "'@en
		}";
		//echo $q;

		$store = $this->_getStore($artistURI);

		if (!is_null($store))
		{
			$rows = $store->query($q, 'rows');
			if ($errs = $store->getErrors()) {
				var_dump($errs);
			}
			else if (count($rows) == 1)
			{
				return $rows[0]['rURI'];
			}
			else if (count($rows) > 1) {
				echo "Multiple uri results for this release: " . $artistURI . " : " . $releaseName ;
			}
			else
			{
				$this->unresolvedReleases[] = $releaseName;
			}
		}
		return FALSE;
	}
}
?>