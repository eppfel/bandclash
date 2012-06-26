<?php
require_once('dbhelper.php');
require_once('shdp/simple_html_dom.php');

/**
* Parser to extract Data from a plain HTML site
*/
class BCParser extends DBHelper
{
	private $baseURI;
	
	public function __construct($baseURI)
	{
		$this->baseURI = $baseURI;
	}

	public function getChartsByArtist ($artistName)
	{
		$uri = $this->baseURI.$artistName;
		// parse a Site
		// Create DOM from URL or file
		$html = file_get_html($uri);
		
		// Find all trs 
		foreach($html->find('tr') as $tablerow) 
		{
			$datacounter = 0;
		   foreach($tablerow->find('td') as $element)
		   {
				echo $element->plaintext." <br />";
				switch($datacounter)
				{
					case 0:
					
					break;	
				}
		   }
		}
		
		
		//return results as triples
		//return $triples;
	}
}
?>