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
	
	public function __construct($baseURI, $subPath)
	{
		$this->_baseURI = $baseURI;
		$this->_subPath = $subPath;
		$this->_rowCounter = 0;
		$this->_datacounter = 0;
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
							$this->_triples[$this->_rowCounter][] = "release";
							$this->_triples[$this->_rowCounter][] = "hasCover";
							$this->_triples[$this->_rowCounter][] = "<img src=\"".$this->_baseURI.str_replace("-100", "-raw", $src)."\" />";
						}
						
					break;
					case 1:
						$this->_triples[$this->_rowCounter][] = "release";
						$this->_triples[$this->_rowCounter][] = "http://www.w3.org/2000/01/rdf-schema#label";
						$this->_triples[$this->_rowCounter][] = $element->plaintext;
					break;	
					case 2:
						$this->_triples[$this->_rowCounter][] = "release";
						$this->_triples[$this->_rowCounter][] = "firstCharted";
						$this->_triples[$this->_rowCounter][] = $element->plaintext;
					break;	
					case 3:
						$this->_triples[$this->_rowCounter][] = "release";
						$this->_triples[$this->_rowCounter][] = "lastCharted";
						$this->_triples[$this->_rowCounter][] = $element->plaintext;
					break;	
					case 4:
						$this->_triples[$this->_rowCounter][] = "release";
						$this->_triples[$this->_rowCounter][] = "appearance";
						$this->_triples[$this->_rowCounter][] = $element->plaintext;
					break;	
					case 5:
						$this->_triples[$this->_rowCounter][] = "release";
						$this->_triples[$this->_rowCounter][] = "peak";
						$this->_triples[$this->_rowCounter][] = $element->plaintext;
					break;	
				}
					//echo $this->_datacounter;
					$this->_datacounter++;	
				
		   }
		   $this->_datacounter = 0;
		   $this->_rowCounter++;
		}
		
		
		//return results as _triples
		
		return $this->_triples;
	}
}
?>