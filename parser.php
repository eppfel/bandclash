<?php
require_once('dbhelper.php');
require_once('shdp/simple_html_dom.php');

/**
* Parser to extract Data from a plain HTML site
*/
class BCParser extends DBHelper
{
	private $_baseURI;
	private $_rowCounter;
	private $_triples = array();
	private $_datacounter;
	
	public function __construct($baseURI)
	{
		$this->_baseURI = $baseURI;
		$this->_rowCounter = 0;
		$this->_datacounter = 0;
	}

	public function getChartsByArtist ($artistName)
	{
		$uri = $this->_baseURI.$artistName;
		// parse a Site
		// Create DOM from URL or file
		$html = file_get_html($uri);
		
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
						$this->_triples[$this->_rowCounter][0] = "release";
						$this->_triples[$this->_rowCounter][1] = "hasTitle";
						$this->_triples[$this->_rowCounter][2] = $element->plaintext;
					break;	
					case 1:
						$this->_triples[$this->_rowCounter][0] = "release";
						$this->_triples[$this->_rowCounter][1] = "firstCharted";
						$this->_triples[$this->_rowCounter][2] = $element->plaintext;
					break;	
					case 2:
						$this->_triples[$this->_rowCounter][0] = "release";
						$this->_triples[$this->_rowCounter][1] = "lastCharted";
						$this->_triples[$this->_rowCounter][2] = $element->plaintext;
					break;	
					case 3:
						$this->_triples[$this->_rowCounter][0] = "release";
						$this->_triples[$this->_rowCounter][1] = "appearance";
						$this->_triples[$this->_rowCounter][2] = $element->plaintext;
					break;	
					case 4:
						$this->_triples[$this->_rowCounter][0] = "release";
						$this->_triples[$this->_rowCounter][1] = "peak";
						$this->_triples[$this->_rowCounter][2] = $element->plaintext;
					break;	
				}
				if($this->_datacounter<=4)
				{
				$this->_datacounter++;
				}else{
				$this->_datacounter=0;	
				}
		   }
		   $this->_rowCounter++;
		}
		
		
		//return results as _triples
		
		return $this->_triples;
	}
}
?>