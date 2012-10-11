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
	private $_rowCounter;
	private $_triples = array();
	private $_datacounter;
	private $_artistname;
	private $_artistUri;
	private $_releasetype;
	private $_numberOfTableHeads;
	
	public function __construct($baseURI, $subPath, $artistUri)
	{
		$this->_baseURI = $baseURI;
		$this->_subPath = $subPath;
		$this->_rowCounter = 0;
		$this->_datacounter = 0;
		$this->_artistname = "";
		$this->_artistUri = $artistUri;
		$this->_releasetype = "";
		$this->_releaseName = "";
		$this->_numberOfTableHeads=0;
	}
	
	
	private function _getSubjectFromDbPedia($releasename)
	{
		/**To Do:
			Get URI for Release from dbPedia
		**/
		
		$uri = $releasename;
		/*array_push($fringe, $uri);

		if ($depth > 0)
		{
			//echo "URI: " . $uri . " , Depth: " . $depth . PHP_EOL;
			/* Query */
			/*
			$q = '
			PREFIX owl: <http://www.w3.org/2002/07/owl#>
			SELECT ?o
			WHERE { <'.$uri.'> owl:sameAs ?o . }';
		
			$store = $this->_getStore($uri);
		
			if (!is_null($store)) {
				if ($rows = $store->query($q, 'rows')) {
					foreach ($rows as $row) {
						if (!in_array($row['o'], $fringe)) {
							$fringe = $this->fetchSameAs($row['o'], $depth-1, $fringe);
						}
					}
				}
			}
		}
		*/
		
		return $uri;
	}

	public function getChartsByArtist ($artistName)
	{
		$fixedArtistName = str_replace(" ", "+", $artistName);
		$uri = $this->_baseURI.$this->_subPath.$fixedArtistName;
		// parse a Site
		// Create DOM from URL or file
		$html = file_get_html($uri);

		foreach($html->find('table') as $table) 
		{
			foreach($table->find('th') as $tablehead)
			{
				$this->_numberOfTableHeads++;
				if($tablehead->plaintext=='Singles')
				{
					$this->_releasetype = 'http://dbpedia.org/ontology/Single'; //Replace by correct URI
				}
				
				if($tablehead->plaintext=='Albums')
				{
					$this->_releasetype = 'http://dbpedia.org/ontology/Album'; //Replace by correct URI
				}
					
			}
			// Find all trs 
			foreach($html->find('tr') as $tablerow) 
			{
				# Temporary triple array 
				$triple_temp = array();
				
			   foreach($tablerow->find('td') as $element)
			   {	
					switch($this->_datacounter)
					{
						case 0:
							foreach($element->find('img') as $img)
							{
								//COVER LARGE
								$triple_temp[0]["p"] = "http://xmlns.com/foaf/0.1/depiction";
								$triple_temp[0]["p type"] = "uri";
								$triple_temp[0]["o type"] = "uri";
								//Add img URI
								$src = $img->src;
								$triple_temp[0]["o"] = $this->_baseURI.str_replace("-100", "-raw", $src);
								
								
								//COVER THUMBNAIL
								$triple_temp[7]["p"] = "http://xmlns.com/foaf/0.1/depiction";
								$triple_temp[7]["p type"] = "uri";
								$triple_temp[7]["o type"] = "uri";
								//Add thumbnail URI
								$src = $img->src;
								$triple_temp[7]["o"] = $this->_baseURI;
							}
							
						break;
						case 1:
							$triple_temp[1]["p type"] = "uri";
							$triple_temp[1]["p"] = "http://www.w3.org/2000/01/rdf-schema#label";
							$triple_temp[1]["o type"] = "literal";
							$triple_temp[1]["o"] = $element->plaintext;
						break;	
						case 2:
							$triple_temp[2]["p type"] = "uri";
							$triple_temp[2]["p"] = "http://www.bandclash.net/onthology#firstCharted";
							$triple_temp[2]["o type"] = "literal";
							$triple_temp[2]["o"] = $element->plaintext;
						break;	
						case 3:
							$triple_temp[3]["p type"] = "uri";
							$triple_temp[3]["p"] = "http://www.bandclash.net/onthology#lastCharted";
							$triple_temp[3]["o type"] = "literal";
							$triple_temp[3]["o"] = $element->plaintext;
						break;	
						case 4:
							$triple_temp[4]["p type"] = "uri";
							$triple_temp[4]["p"] = "http://www.bandclash.net/onthology#chartAppearances";
							$triple_temp[4]["o type"] = "literal";
							$triple_temp[4]["o"] = $element->plaintext;
						break;	
						case 5:
							$triple_temp[5]["p type"] = "uri";
							$triple_temp[5]["p"] = "http://www.bandclash.net/onthology#chartPeak";
							$triple_temp[5]["o type"] = "literal";
							$triple_temp[5]["o"] = $element->plaintext;
						break;	
					}
						$this->_datacounter++;
			   } 	   	
			   if(isset($triple_temp[1]["o"]))
			   {
			   		$this->_releaseName = $triple_temp[1]["o"];
			   		//echo $this->fetchAll($releaseName, $this->_artistUri);
			   };
			   $this->_datacounter = 0;
					   
			   for ($i=0; $i<=5; $i++)
			   {
					$triple_temp[$i]['s']=$this->_releaseName;
					$triple_temp[$i]['s type']= "literal";
					//echo $triple_temp['o type']." ,";			   
			   }
			  	$this->_triples = array_merge($this->_triples, $triple_temp);	
			  	//var_dump($this->_triples);			
			}
			   $this->_rowCounter++;
		}
		
		//return results as _triples

		return $this->_triples;
	}
	
	public function fetchSongURI($releaseName, $artistUri)
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
		echo $q;

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