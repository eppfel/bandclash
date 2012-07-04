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
	private $_releasetype;
	
	public function __construct($baseURI, $subPath)
	{
		$this->_baseURI = $baseURI;
		$this->_subPath = $subPath;
		$this->_rowCounter = 0;
		$this->_datacounter = 0;
		$this->_artistname = "";
		$this->_releasetype = "";
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
		//TODO: Tabellen in Singles und Albums unterteilen
		//foreach($html->find('tr') as $tablerow) 
		
		foreach($html->find('table') as $table) 
		{
			foreach($html->find('th') as $tablehead)
			{
					
					if($tablehead->plaintext=='Singles')
					{
							$this->_releasetype = 'http://www.bandclash.net/onthology#Single'; //Replace by correct URI
					}
					if($tablehead->plaintext=='Albums')
					{
							$this->_releasetype = 'http://www.bandclash.net/onthology#Album'; //Replace by correct URI
					}
					
			}
			// Find all trs 
			foreach($html->find('tr') as $tablerow) 
			{
				
			   foreach($tablerow->find('td') as $element)
			   {
				
				   #funktioniert soweit
					//echo $element->plaintext." <br />";
					
					
					#Funktioniert noch nicht (Denkfehler!)
					#Triples müssten noch zusammengebaut werden
					
					switch($this->_datacounter)
					{
						case 0:
							foreach($element->find('img') as $img)
							{
								$src = $img->src;
								$this->_triples[$this->_rowCounter]["p type"] = "uri";
								$this->_triples[$this->_rowCounter]["p"] = $this->_releasetype;
								$this->_triples[$this->_rowCounter][] = "foaf:depitction";
								$this->_triples[$this->_rowCounter][] = $this->_baseURI.str_replace("-100", "-raw", $src);
							}
							
						break;
						case 1:
							$this->_triples[$this->_rowCounter][] = $this->_releasetype;
							$this->_triples[$this->_rowCounter][] = "http://www.w3.org/2000/01/rdf-schema#label";
							$this->_triples[$this->_rowCounter][] = $element->plaintext;
						break;	
						case 2:
							$this->_triples[$this->_rowCounter][] = $this->_releasetype;
							$this->_triples[$this->_rowCounter][] = "http://www.bandclash.net/onthology#firstCharted";
							$this->_triples[$this->_rowCounter][] = $element->plaintext;
						break;	
						case 3:
							$this->_triples[$this->_rowCounter][] = $this->_releasetype;
							$this->_triples[$this->_rowCounter][] = "http://www.bandclash.net/onthology#lastCharted";
							$this->_triples[$this->_rowCounter][] = $element->plaintext;
						break;	
						case 4:
							$this->_triples[$this->_rowCounter][] = $this->_releasetype;
							$this->_triples[$this->_rowCounter][] = "http://www.bandclash.net/onthology#chartAppearances";
							$this->_triples[$this->_rowCounter][] = $element->plaintext;
						break;	
						case 5:
							$this->_triples[$this->_rowCounter][] = $this->_releasetype;
							$this->_triples[$this->_rowCounter][] = "http://www.bandclash.net/onthology#chartPeak";
							$this->_triples[$this->_rowCounter][] = $element->plaintext;
						break;	
					}
						//echo $this->_datacounter;
						$this->_datacounter++;	
					
			   }
			   $this->_datacounter = 0;
			   $this->_rowCounter++;
			}
		}
		
		//return results as _triples
		
		
		return $this->_triples;
	}
}
?>