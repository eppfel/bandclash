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
	
	public function __construct($baseURI, $subPath)
	{
		$this->_baseURI = $baseURI;
		$this->_subPath = $subPath;
	}

	/**
	 * Function to parse all hits by an artist from chartarchive.org
	 * Fix: parameter artsit uri
	 * Fix: correct releaseType URI from dbpedia
	 */
	public function getChartsByArtist ($artistName, $artistURI)
	{	
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
								$src = $element->find('img', 0)->src;

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
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.w3.org/2000/01/rdf-schema#label";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $releaseName = $element->plaintext;
								break;	

							case 2:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/onthology#firstCharted";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 3:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/onthology#lastCharted";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 4:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/onthology#chartAppearances";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	

							case 5:
								$triple_temp["p type"] = "uri";
								$triple_temp["p"] = "http://www.bandclash.net/onthology#chartPeak";
								$triple_temp["o type"] = "literal";
								$triple_temp["o"] = $element->plaintext;
								break;	
						}
						$triples_temp[] = $triple_temp;
						$tdC++;
				   	}

				   	/**
				   	 * set subject uri from title/name of release
				   	 * FIX: method not implemented
				   	 *
				   	$releaseURI = $this->fetchReleaseURI($releaseName, $artistURI, $releaseType);
				   	foreach ($triples_temp as $triple)
				   	{
						$triple['s'] 		= $releaseURI;
						$triple['s type'] 	= "uri";
						$triples[] = $triple;
				   	}
				   	*/
				}

			   	$rowC++;
			}
		}
		
		//return results as triples !FIX: empty, because results are not transfered to var $triples
		return $triples;
	}
	
	/**
	 * Function to fetch a dbpedia URI to infer the parsed data with RDF data
	 * FIX: not implmented correctly, complete go through required
	 */
	public function fetchReleaseURI($releaseName, $artistURI, $releaseType)
	{
		/* Query */
		$q = "
		PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
		PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		SELECT ?s
		WHERE 	{
			  	?s <http://dbpedia.org/ontology/musicalArtist> <$artistUri> .
				?s rdf:type <".$this->_releasetype."> .
				?s foaf:name '".urlencode($releaseName)."'@en
				}
		";
		//echo $q;

		$store = $this->_getStore($artistUri);

		if (!is_null($store))
		{
			if (count($rows = $store->query($q, 'rows'))==1)
			{
				return $rows[0]['s'];
				/*$rowsn = array();
				foreach ($rows as $row) {
					$row['s'] = $uri;
					$row['s type'] = "uri";
					array_push($rowsn, $row);
				}
				return $rowsn;	*/
			}
			else if ($errs = $store->getErrors()) {
				var_dump($errs);
			}
		}
		return;
	}
}
?>